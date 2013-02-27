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

        // Build pipline
        $pipeline = array();
        foreach ($this->original as $index => $section) {

            $pipeline[$index] = array();

            foreach ($section as $pipeline_key => $data) {


                // Sort out Projections
                if ($pipeline_key === '$project') {
                    foreach ($data as $key => $value) {
                        if (strpos($value, '$') === 0) {
                            $value = array_search(substr($value, 1), $this->collection->schema_reverse_index);
                            if ($value !== false) {
                                $this->mappings[$value] = $key;
                                $data[$key] = '$' . $value;
                            }
                        }
                    }

                // Grouping
                } elseif ($pipeline_key === '$group') {
                    foreach ($data as $group_key => $group_value) {
                        if (is_array($group_value)) {
                            foreach ($group_value as $key => $value) {
                                if (strpos($value, '$') === 0) {
                                    $value = array_search(substr($value, 1), $this->collection->schema_reverse_index);
                                    if ($value !== false) {
                                        $data[$group_key][$key] = '$' . $this->mappings[$value];
                                    }
                                }
                            }
                        }
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
