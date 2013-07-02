<?php

namespace MongoMinify;

class Pipeline
{

    private $collection;
    private $original = array();
    public $compressed = array();
    public $mappings = array();

    public function __construct($original, $collection)
    {
        $this->collection = $collection;
        $this->original = $original;
        $this->compressed = $original;
    }

    public function expand($value)
    {
        if (is_array($value)) {
            foreach ($value as $key2 => $value2) {
                $value[$key2] = $this->expand($value2);
            }  
        } else {
            if (strpos($value, '$') === 0) {
                $short_value = array_search(substr($value, 1), $this->collection->schema_reverse_index);
                if ($short_value !== false) {
                    return '$' . $short_value;
                }
            }
        }

        return $value;
    }

    public function compress()
    {

        // Set default $project
        if ( ! isset($this->original[0]['$project']))
        {
            $project = array();
            foreach ($this->collection->schema_reverse_index as $short => $long)
            {
                $project[$long] = 1;
                $this->mappings[$long] = $short;
            }
        }

        // Build pipline
        $pipeline = array();
        foreach ($this->original as $index => $section) {

            $pipeline[$index] = array();

            foreach ($section as $pipeline_key => $data) {

                // Sort out Projections
                if ($pipeline_key === '$project') {
                    foreach ($data as $key => $value) {
                        $short_key = array_search($key, $this->collection->schema_reverse_index);
                        if ($short_key)
                        {
                            $this->mappings[$key] = $short_key;
                            unset($data[$key]);
                            $data[$short_key] = 1;
                        }
                    }


                // Match
                } elseif ($pipeline_key === '$match') {
                    $document = new Document($data, $this->collection);
                    $document->compress();
                    $data = $document->compressed;


                // Unwind
                } elseif ($pipeline_key === '$unwind') {
                    $key = substr($data, 1);
                    if (isset($this->mappings[$key]))
                    {
                        $data = '$' . $this->mappings[$key];
                    }


                // Grouping
                } elseif ($pipeline_key === '$group') {
                    foreach ($data as $group_key => $group_value) {
                        $data[$group_key] = $this->expand($group_value);
                    }
                }


                // Iterate over each section
                if (is_array($data)) {
                    $pipeline[$index][$pipeline_key] = array();
                    foreach ($data as $key => $value) {
                        $pipeline[$index][$pipeline_key][$key] = $value;
                    }

                // String values such as..
                } else {
                    $pipeline[$index][$pipeline_key] = $data;
                }
            }
        }

        // Assign to internal variable
        $this->compressed = $pipeline;
        return $pipeline;
    }
}
