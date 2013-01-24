<?php

namespace MongoMinify;

class Db {

	public $name;
	public $client;
	public $native;

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
		$collection = new Collection($name, $this);
		return $collection;
	}


	/**
	 * Command
	 */
	public function command(Array $command, Array $options = array())
	{
		return $this->native->command($command, $options);
	}

}