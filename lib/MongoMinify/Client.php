<?php

namespace MongoMinify;

class Client {

	public $native;
	public $db;

	public $debug = false;
	

	/**
	 * Initializer
	 * @param Array $options Connection Options
	 */
	public function __construct(Array $options = array())
	{
		$this->native = new \MongoClient('mongodb://' . $options['host'] . ':' . $options['port'] . '/' . $options['db']);
		$this->db = $this->selectDb($options['db']);
	}


	/**
	 * Select Collection
	 */
	public function selectDb($name)
	{
		$this->db = $this->native->selectDb($name);
		return $this->db;
	}


	/**
	 * Select Collection
	 */
	public function selectCollection($name)
	{
		$collection = new Collection($name, $this);
		return $collection;
	}

}