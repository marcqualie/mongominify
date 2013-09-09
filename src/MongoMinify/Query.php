<?php

namespace MongoMinify;

class Query
{

    public $state = 'normal';
    public $data = array();
    public $compressed = array();
    protected $collection = array();
    protected $nonNumericIdentifier = '[:]';

    public function __construct(array $data = array(), $collection = null)
    {
        $this->collection = $collection;
        $this->data = $data;
    }

    /**
     * Data Compression
     */
    public function compress()
    {
        if (!$this->collection->schema) {
            $this->compressed =& $this->data;

            return;
        }
        if ($this->state !== 'compressed' && $this->collection) {
            $this->compressed = $this->applyCompression($this->data);
            $this->asDotSyntax();
            $this->state = 'compressed';
        }
    }
    private function applyCompression($document, $parent = null)
    {

        // Normalize doc delimited keys
        foreach ($document as $key => $value) {
            if (strpos($key, '.') !== false) {
                list ($parent_key, $child_key) = explode('.', $key, 2);
                if (!isset($document[$parent_key])) {
                    $document[$parent_key] = array();
                }
                $document[$parent_key][$child_key] = $value;
                unset($document[$key]);
            }
        }

        // Documents are applied as key/value
        $doc = array();
        foreach ($document as $key => $value) {

            $namespace = ($parent ? $parent . '.' : '') . $key;

            // Fix $in queries
            if ($key === '$in' && isset($this->collection->schema[$parent]['values'])) {
                $values = $this->collection->schema[$parent]['values'];
                $values = array_flip($values);
                foreach ($value as $valkey => $val) {
                    if (isset($values[$val])) {
                        $value[$valkey] = $values[$val];
                    }
                }

            } elseif ($key === '$elemMatch') {
                // $elemMatch
                $sub_doc = $this->applyCompression($value, $parent);
                $value = $sub_doc;

            } elseif ($key === '$and' || $key === '$or') {
                // $and
                foreach ($value as $sub_index => $sub_value) {
                    $value[$sub_index] = $this->applyCompression($sub_value, $parent);
                }

            } elseif (is_array($value)) {
                // Loop over arrays recursively
                $value = $this->applyCompression($value, $namespace);

            } elseif (isset($this->collection->schema[$namespace]['values'])) {
                // Handle actual value conversion
                $values = $this->collection->schema[$namespace]['values'];
                $values = array_flip($values);
                if (isset($values[$value])) {
                    $value = $values[$value];
                }
            }

            // Apply values
            $short = isset($this->collection->schema_index[$namespace])
                ? $this->collection->schema_index[$namespace]
                : $key;
            if (is_int($short) && ! $this->isSequentialArray($document)) {
                $short = $this->nonNumericIdentifier . $short;
            }
            $doc[$short] = $value;
        }

        return $doc;
    }


    /**
     * As dot syntax for index ensuring
     */
    public function asDotSyntax()
    {
        $dotSyntax = $this->applyDotSyntax($this->compressed);
        $this->compressed = $dotSyntax;

        return $dotSyntax;
    }
    private function applyDotSyntax($data, $ns = '')
    {
        $out = array();
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (strpos($key, '$') === 0) {
                    $out[$key] = $this->applyDotSyntax($value);
                } elseif (is_array($value)) {
                    $sub_data = $this->applyDotSyntax($value, ($ns ? $ns . '.' : '') . $key);
                    foreach ($sub_data as $sub_key => $sub_value) {
                        if (strpos($key, $this->nonNumericIdentifier) === 0) {
                            $key = substr($key, strlen($this->nonNumericIdentifier));
                            $out[$key . '.' . $sub_key] = $sub_value;
                        } elseif (is_numeric($key)) {
                            $out[$key][$sub_key] = $sub_value;
                        } elseif (strpos($sub_key, '$') === 0) {
                            $out[$key][$sub_key] = $sub_value;
                        } else {
                            $out[$key . '.' . $sub_key] = $sub_value;
                        }
                    }
                } else {
                    $out[$key] = $value;
                }
            }

            return $out;
        } else {
            return $data;
        }

        return $out;
    }

    /**
     * Check if this array is sequential
     */
    public function isSequentialArray($array)
    {
        $counter = 0;
        foreach ($array as $key => $value) {
            if ($counter !== $key) {
                return false;
            }
            $counter++;
        }

        return true;
    }
}
