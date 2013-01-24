<?php

namespace MongoMinify;

class Client {

	public $native;
	public $db = null;
	public $debug = false;
	

	/**
	 * Initializer
	 * @param Array $options Connection Options
	 */
	public function __construct(Array $options = array())
	{
		if ( ! isset($options['host']))
		{
			$options['host'] = 'localhost';
		}
		if ( ! isset($options['port']))
		{
			$options['port'] = 27017;
		}
		if ( ! isset($options['db']))
		{
			$options['db'] = '';
		}
		$this->native = new \MongoClient('mongodb://' . $options['host'] . ':' . $options['port'] . '/' . $options['db']);
		if ($options['db'])
		{
			$this->selectDb($options['db']);
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
		$this->db = new Db($name, $this);
		return $this->db;
	}

}