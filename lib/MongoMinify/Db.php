<?php

namespace MongoMinify;

class Db {

	public $name;
	public $client;
	public $native;

	public $collection_cache = array();

	public function __construct($name, $client)
	{
		$this->name = $name;
		$this->client = $client;
		$this->native = $client->native->selectDb($name);
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
		if ( ! isset($this->collection_cache[$name]))
		{
			$collection = new Collection($name, $this);
			$collection_cache[$name] = $collection;
		}
		return $collection_cache[$name];
	}


	/**
	 * Command
	 */
	public function command(Array $command, Array $options = array())
	{
		return $this->native->command($command, $options);
	}

}