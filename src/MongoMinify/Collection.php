<?php

namespace MongoMinify;

class Collection
{

    private $name = '';
    public $namespace = '';
    public $db;
    public $native;

    public $schema = array();
    public $schema_raw = array();
    public $schema_index = array();
    public $schema_reverse_index = array();

    /**
     * Initialize
     */
    public function __construct($name, Db $db)
    {
        $this->name = $name;
        $this->db = $db;
        $this->client = $db->client;
        $this->namespace = (String) $db . '.' . $this->name;
        $this->native = $db->native->selectCollection($this->name);

        // Apply schema to collection
        $this->setSchemaByName($this->namespace);
    }

    /**
     * Get name of collection
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get full name of collection
     */
    public function __toString()
    {
        return (String) $this->db . '.' . $this->getName();
    }

    /**
     * Get a collection (dot based syntax name)
     */
    public function __get($name)
    {
        return $this->db->selectCollection($this->getName() . '.' . $name);
    }

    /**
     * Save Document
     * @param  [type] $data    [description]
     * @param  array  $options [description]
     * @return [type] [description]
     */
    public function save(array &$data, array $options = array())
    {
        $document = new Document($data, $this);
        $save = $document->save($options);
        $data = $document->data;

        return $save;
    }

    /**
     * Insert new document
     * @param  [type] $document [description]
     * @param  array  $options  [description]
     * @return [type] [description]
     */
    public function insert(&$data, array $options = array())
    {
        $document = new Document($data, $this);
        $insert = $document->insert($options);
        $data = $document->data;

        return $insert;
    }

    /**
     * Update Document
     */
    public function update(array $query = array(), array $update = array(), array $options = array())
    {
        $document = new Document($query, $this);
        $update = $document->update($update, $options);

        return $update;
    }

    /**
     * Find and Modify Document
     */
    public function findAndModify(array $query, array $update = array(), array $fields = null, array $options = array())
    {
        $document = new Document($query, $this);
        $findAndModify = $document->findAndModify($update, $fields, $options);

        return $findAndModify;
    }

    /**
     * Remove Documents
     */
    public function remove($criteria = array(), array $options = array())
    {
        if ($criteria) {
            $query = new Query($criteria, $this);
            $query->compress();
            $criteria = $query->compressed;
        }

        return $this->native->remove($criteria, $options);
    }

    /**
     * Find document
     * @param  array  $document [description]
     * @return [type] [description]
     */
    public function findOne(array $query = array(), array $fields = array())
    {
        $cursor = $this->find($query, $fields)->limit(1);

        return $cursor->getNext();
    }
    public function find(array $query = array(), array $fields = array())
    {
        if ($query) {
            $query_object = new Query($query, $this);
            $query_object->compress();
            $query = $query_object->compressed;
        }
        if ($fields) {
            $fields_object = new Query($fields, $this);
            $fields_object->compress();
            $fields = $fields_object->compressed;
        }
        $cursor = new Cursor($this, $query, $fields);

        return $cursor;
    }

    /**
     * Count Helper
     */
    public function count(array $query = array(), $limit = null, $skip = null)
    {
        return $this->native->count($query, $limit, $skip);
    }

    /**
     * Apply internal schema
     */
    public function setSchemaByName($schema_name = null)
    {
        if (! $schema_name) {
            $schema_name = $this->namespace;
        }
        if (strpos($schema_name, '.') === false) {
            $schema_name = (String) $this->db . '.' . $schema_name;
        }

        // Check formats
        if ($this->client->schema_format === 'php') {
            $schema_file = $this->client->schema_dir . '/' . $schema_name . '.php';
            if (file_exists($schema_file)) {
                $schema = include $schema_file;
                if (! is_array($schema)) {
                    throw new \Exception('Possible PHP syntax error in ' . $schema_file);
                }
            }
        } elseif ($this->client->schema_format === 'json') {
            $schema_file = $this->client->schema_dir . '/' . $schema_name . '.json';
            if (file_exists($schema_file)) {
                $json_string = file_get_contents($schema_file);
                $schema = json_decode($json_string, true);
                if (empty($schema)) {
                    throw new \Exception('Possible JSON parse error in ' . $schema_file);
                }
            }
        } else {
            throw new \Exception('Unknown schema format: ' . $this->client->schema_format);
        }

        // Assign Schema
        if (! empty($schema)) {
            $this->setSchema($schema);
        }

    }
    public function setSchema(array $schema = array())
    {
        $this->schema = array();
        $this->schema_raw = $schema;
        $this->schema_index = array();
        $this->schema_reverse_index = array();
        $this->setSchemaArray($schema);
    }
    private function setSchemaArray(array $array, $namespace = null)
    {
        foreach ($array as $key => $value) {
            $subkey = ($namespace ? $namespace . '.' : '') . $key;
            $this->schema[$subkey] = $value;
            if (! isset($value['short'])) {
                if ($key === '*') {
                    $value['short'] = $this->schema_index[$namespace] . '.*';
                    $namespace = '';
                } else {
                    $value['short'] = $key;
                }
            }
            $short = $this->getShort($namespace);
            $parent_short = $short ? $short . '.' : '';
            $this->schema_index[$subkey] = $value['short'];
            $this->schema_reverse_index[$parent_short . $value['short']] = $subkey;
            if (isset($value['subset'])) {
                $this->setSchemaArray($value['subset'], $subkey);
                unset($value['subset']);
            }
        }
    }


    /**
     * Get short definitions based on full key
     */
    public function getShort($full)
    {
        if (strpos($full, '*') !== false) {
            return $this->schema_index[substr($full, 0, -2)] . '.*';
        }
        if (isset($this->schema[$full]['short'])) {
            return $this->schema[$full]['short'];
        }
        return $full;
    }


    /**
     * Batch Insert
     */
    public function batchInsert(array &$documents, array $options = array())
    {
        $documents_compressed = array();
        foreach ($documents as $data) {
            $document = new Document($data, $this);
            $document->compress();
            $documents_compressed[] = $document->compressed;
        }
        $this->native->batchInsert($documents_compressed, $options);
        foreach ($documents_compressed as $key => $document) {
            $documents[$key]['_id'] = $document['_id'];
        }
    }

    /**
     * Drop
     */
    public function drop()
    {
        $this->native->drop();
    }

    /**
     * Distinct
     */
    public function distinct($key, array $query = array())
    {
        $key_short = isset($this->schema[$key]['short']) ? $this->schema[$key]['short'] : $key;
        $query_object = new Query($query, $this);
        $query_object->compress();
        $values_short = $this->native->distinct($key_short, $query_object->compressed);
        if (isset($this->schema[$key]['type']) && $this->schema[$key]['type'] === 'enum') {
            $values = array();
            foreach ($values_short as $val) {
                $values[] = isset($this->schema[$key]['values'][$val]) ? $this->schema[$key]['values'][$val] : $val;
            }

            return $values;
        }

        return $values_short;
    }

    /**
     * Ensure Index
     */
    public function ensureIndex($keys, array $options = array())
    {
        if (is_string($keys)) {
            $keys = array($keys => 1);
        }

        $query = new Query($keys, $this);
        $query->compress();
        $query->asDotSyntax();

        return $this->native->ensureIndex($query->compressed, $options);
    }

    /**
     * Delete Index
     */
    public function deleteIndex($keys)
    {
        if (is_string($keys)) {
            $keys = array($keys => 1);
        }

        $query = new Query($keys, $this);
        $query->compress();
        $query->asDotSyntax();

        return $this->native->deleteIndex($query->compressed);
    }

    /**
     * Delete All Indexes
     */
    public function deleteIndexes()
    {
        return $this->native->deleteIndexes();
    }

    /**
     * Get all indexes
     */
    public function getIndexInfo()
    {
        return $this->native->getIndexInfo();
    }

    /**
     * Set Read Preference
     */
    public function setReadPreference($read_preference, array $tags = array())
    {
        return $this->native->setReadPreference($read_preference, $tags);
    }

    /**
     * Set Read Preference
     */
    public function getReadPreference()
    {
        return $this->native->getReadPreference();
    }

    /**
     * Aggregation Helper
     */
    public function aggregate($pipeline)
    {
        $pipeline_object = new Pipeline($pipeline, $this);
        $pipeline_object->compress();

        return $this->db->command(
            array(
                'aggregate' => $this->name,
                'pipeline' => $pipeline_object->compressed
            )
        );
    }
}
