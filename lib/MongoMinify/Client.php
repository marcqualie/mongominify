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

        // Link timeouts
        Cursor::$timeout = \MongoCursor::$timeout;
        \MongoCursor::$timeout =& Cursor::$timeout;

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
     * Connect to database
     * @return bool True if the connection was successful
     */
    public function connect()
    {
        return $this->native->connect();
    }


    /**
     * Close database connection
     * @return bool True if the connection was successful
     */
    public function close($connection = false)
    {
        return $this->native->close($connection);
    }


    /**
     * Get active connections
     */
    public function getConnections()
    {
        return $this->native->getConnections();
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
     * Select Collection
     */
    public function selectCollection($db_name, $collection_name)
    {
        $db = new Db((String) $db_name, $this);
        $collection = $db->selectCollection($collection_name);
        return $collection;
    }


    /**
     * Helper to get most recently selected database
     */
    public function currentDb()
    {
        return $this->current_db;
    }


    /**
     * List Databases
     */
    public function listDBs()
    {
        return $this->native->listDBs();
    }


    /**
     * Gets a list of all host statuses
     */
    public function getHosts()
    {
        return $this->native->getHosts();
    }


    /**
     * Set read preference
     */
    public function setReadPreference($read_preference, array $tags = array())
    {
        return $this->native->setReadPreference($read_preference, $tags);
    }


    /**
     * Get read preference
     */
    public function getReadPreference()
    {
        return $this->native->getReadPreference();
    }


    /**
     * String representation of this connection
     */
    public function __toString()
    {
        return (String) $this->native;
    }

}
