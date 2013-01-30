<?php

include_once __DIR__ . '/../vendor/autoload.php';

class MongoMinifyTest extends \PHPUnit_Framework_TestCase {

	public $client;

	public function setUp()
	{

		// Connecto to MongoDB
		try {
			$this->client = new MongoMinify\Client('mongodb://localhost:27017');
		}
		catch (MongoConnectionException $e)
		{
			throw new Exception('Could not connect to MongoDB. Tests are unable to run');
			exit;
		}
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