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
	 * Create a new Collection
	 */
	public function createCollection($name, $capped = FALSE, $size = 0, $max = 0)
	{
		$this->native->createCollection($name, $capped, $size, $max);
		return $this->selectCollection($name);
	}


	/**
	 * Command
	 */
	public function command(array $command, array $options = array())
	{
		return $this->native->command($command, $options);
	}

	/**
	 * Last Error Helper
	 */
	public function lastError()
	{
		$this->native->command(array('getLastError' => 1));
	}

}