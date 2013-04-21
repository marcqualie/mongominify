<?php

namespace MongoMinify;

class Client
{

    public $native;
    public $debug = false;
    public $schema_dir = './';
    public $schema_format = 'json';

    private $current_db;

    /**
     * Initializer
     * @param Array $options Connection Options
     */
    public function __construct($server = 'mongodb://localhost:27017', array $options = array())
    {

        // Parse MongoDB Path Info
        if (! empty($options['db'])) {
            $db_name = $options['db'];
        } else {
            $uri = parse_url($server);
            $db_name = isset($uri['path']) ? substr($uri['path'], 1) : 'test';
        }

        // Apply defaults
        if (! isset($options['connect'])) {
            $options['connect'] = true;
        }

        // Native connection
        $this->native = new \MongoClient($server, $options);

        // Select Database for default reference
        if ($db_name) {
            $this->selectDb($db_name);
        }
        
    }


    /**
     * Select Database
     */
    public function __get($name)
    {
        return $this->selectDb($name);
    }


    /**
     * Select Collection
     */
    public function selectDb($name)
    {
        $this->current_db = new Db($name, $this);
        return $this->current_db;
    }


    /**
     * Helper to get most recently selected database
     */
    public function currentDb()
    {
        return $this->current_db;
    }
}
