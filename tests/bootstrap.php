<?php

include_once __DIR__ . '/../vendor/autoload.php';

class MongoMinifyTest extends \PHPUnit_Framework_TestCase {

	private static $client_instance;
	public $client;

	public $mongo_server = 'mongodb://127.0.0.1:27017/mongominify';
	public $mongo_options = array();

	public function setUp()
	{

		// Connecto to MongoDB
		try {
			if ( ! isset(self::$client_instance))
			{
				self::$client_instance = new MongoMinify\Client($this->mongo_server, $this->mongo_options);
			}
			$this->client = self::$client_instance;
		}
		catch (MongoConnectionException $e)
		{
			throw new Exception('Could not connect to MongoDB. Tests are unable to run');
			exit;
		}

		// Override schema options
		$this->client->schema_format = 'json';
		$this->client->schema_dir = __DIR__ . '/Schema';

	}


	/**
	 * Get access to test collection
	 * @return MongoMinify\Collection A collection instance object
	 */
	public function getTestCollection($drop = true)
	{
		$collection = $this->client->mongominify->test;
		if ($drop)
		{
			$collection->drop();
		}
		return $collection;
	}

}
