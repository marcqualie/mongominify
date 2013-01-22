<?php

class MongoMinify {
	
	public $debug = false;
	private $schema = array();
	private $schema_raw = array();
	private $client;


	/**
	 * Initializer
	 * @param Array $options Connection options
	 */
	public function __construct(Array $options = array())
	{
		$this->client = new MongoMinify\Client($options);
		return $this->client;
	}

}