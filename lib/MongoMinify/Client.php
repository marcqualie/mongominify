<?php

namespace MongoMinify;

class Client {

	public $native;
	public $db = null;
	public $debug = false;
	public $schema_dir = './';
	

	/**
	 * Initializer
	 * @param Array $options Connection Options
	 */
	public function __construct($server = 'mongodb://localhost:27017', Array $options = array())
	{
		$this->native = new \MongoClient($server, $options);
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
		$this->db = new Db($name, $this);
		return $this->db;
	}

}