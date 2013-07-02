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

                    $schema_keys = array();
                    foreach ($this->collection->schema_reverse_index as $schema_key => $schema_value)
                    {
                        $schema_keys['$' . $schema_key] = '$' . $schema_value;
                    }

                    $json_data = json_encode($data);
                    $json_data = str_replace(array_values($schema_keys), array_keys($schema_keys), $json_data);

                    $data = json_decode($json_data, true);
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
