<?php

namespace MongoMinify;

class Db
{

    private $name;
    public $client;
    public $native;

    public $collection_cache = array();

    public function __construct($name, Client $client)
    {
        $this->name = $name;
        $this->client = $client;
        $this->native = $client->native->selectDb($name);
    }

    /**
     * Get database name
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Select Collection
     */
    public function __get($name)
    {
        return $this->selectCollection($name);
    }

    /**
     * Select Collection
     */
    public function selectCollection($name)
    {
        if (!isset($this->collection_cache[$name])) {
            $collection = new Collection($name, $this);
            $collection_cache[$name] = $collection;
        }

        return $collection_cache[$name];
    }

    /**
     * Create a new Collection
     */
    public function createCollection($name, $capped = false, $size = 0, $max = 0)
    {
        $this->native->createCollection($name, $capped, $size, $max);

        return $this->selectCollection($name);
    }

    /**
     * Drop Database
     */
    public function drop()
    {
        $this->native->command(
            array(
                "dropDatabase" => 1
            )
        );
    }

    /**
     * List Collections
     */
    public function listCollections()
    {
        return $this->native->listCollections();
    }

    /**
     * Command
     * @codeCoverageIgnore
     */
    public function command(array $command, array $options = array())
    {
        return $this->native->command($command, $options);
    }

    /**
     * Last Error Helper
     * @codeCoverageIgnore
     */
    public function lastError()
    {
        $this->native->command(array('getLastError' => 1));
    }
}
