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


}