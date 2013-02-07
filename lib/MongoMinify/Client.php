<?php

namespace MongoMinify;

class Client {

	public $native;
	public $db = null;
	public $db_name = 'test';
	public $debug = false;
	public $schema_dir = './';
	

	/**
	 * Initializer
	 * @param Array $options Connection Options
	 */
	public function __construct($server = 'mongodb://localhost:27017', array $options = array())
	{

		// Parse MongoDB Path Info
		$uri = parse_url($server);
		$this->db_name = isset($uri['path']) ? substr($uri['path'], 1) : $this->db_name;

		// Native connection
		$this->native = new \MongoClient($server, $options);

		// Select Database for default reference
		$this->db = $this->selectDb($this->db_name);
		
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