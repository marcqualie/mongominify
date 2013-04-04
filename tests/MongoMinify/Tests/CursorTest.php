<?php

class CursorTest extends MongoMinifyTest {
	

	/**
	 * Find data based on flat document structure
	 */
	public function testLimit()
	{

		// Create a collection
		$collection = $this->getTestCollection();

		// Fake Document
		$documents = array();
		for ($i = 0; $i < 100; $i++)
		{
			$documents[] = array(
				'user_id' => $i,
				'email' => 'test' . $i . '@example.com',
			);
		}
		$collection->batchInsert($documents);

		// Make sure document has the correct format after saving
		$found = $collection->find(array('user_id' => array('$gt' => 5)))->limit(1);
		foreach ($found as $document)
		{
			$this->assertEquals($document['user_id'], 6);
		}

	}


	/**
	 * Test Timeouts
	 */
	public function testTimeout()
	{

		// Create collection
		$collection = $this->getTestCollection();

		// Create a cursor object
		$cursor = $collection->find();
		$native_cursor = $cursor->native;

		// Assert cursor timeouts are updated
		$this->assertEquals($cursor::$timeout, $native_cursor::$timeout);
		$cursor->timeout(1000);

		// Check that static timeouts are binded
		$default_timeout = \MongoCursor::$timeout;
		$cursor::$timeout = $default_timeout / 2;
		$this->assertEquals(\MongoCursor::$timeout, $cursor::$timeout);
		$this->assertEquals(\MongoMinify\Cursor::$timeout, $cursor::$timeout);
		\MongoCursor::$timeout = 200;
		$this->assertEquals(\MongoCursor::$timeout, \MongoMinify\Cursor::$timeout);
		$this->assertEquals(\MongoMinify\Cursor::$timeout, 200);


	}


}
