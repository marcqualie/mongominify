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


	/**
	 * Sorting (Ascending)
	 */
	public function testSortAsc()
	{

		// Create a collection
		$collection = $this->getTestCollection();

		// Insert fake data
		$documents = array(
			array(
				'_id' => 3,
				'role' => 'moderator'
			),
			array(
				'_id' => 2,
				'role' => 'moderator'
			),
			array(
				'_id' => 6,
				'role' => 'user'
			),
			array(
				'_id' => 4,
				'role' => 'none'
			),
			array(
				'_id' => 1,
				'role' => 'admin'
			),
			array(
				'_id' => 5,
				'role' => 'moderator'
			)
		);
		$collection->batchInsert($documents);

		// Test distinct values
		$cursor = $collection->find();
		$cursor->sort(array('_id' => 1));
		$data = iterator_to_array($cursor, false);
		$this->assertEquals($data[0]['_id'], 1);
		$this->assertEquals($data[1]['_id'], 2);
		$this->assertEquals($data[2]['_id'], 3);


	}


	/**
	 * Sorting (Ascending)
	 */
	public function testSortDesc()
	{

		// Create a collection
		$collection = $this->getTestCollection();

		// Insert fake data
		$documents = array(
			array(
				'_id' => 3,
				'role' => 'moderator'
			),
			array(
				'_id' => 2,
				'role' => 'moderator'
			),
			array(
				'_id' => 6,
				'role' => 'user'
			),
			array(
				'_id' => 4,
				'role' => 'none'
			),
			array(
				'_id' => 1,
				'role' => 'admin'
			),
			array(
				'_id' => 5,
				'role' => 'moderator'
			)
		);
		$collection->batchInsert($documents);

		// Test distinct values
		$cursor = $collection->find();
		$cursor->sort(array('_id' => -1));
		$data = iterator_to_array($cursor, false);
		$this->assertEquals($data[0]['_id'], 6);
		$this->assertEquals($data[1]['_id'], 5);
		$this->assertEquals($data[2]['_id'], 4);


	}


}
