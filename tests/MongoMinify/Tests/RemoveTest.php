<?php

class RemoveTest extends MongoMinifyTest {
	

	/**
	 * Test saving a document to the database
	 */
	public function testRemove()
	{

		// Create a collection
		$collection = $this->getTestCollection();

		// Fake Document
		$document = array(
			'user_id' => 1,
			'email' => 'test1@example.com'
		);
		$collection->save($document);

		// Check Data stored in database is compressed
		$document_native = $collection->native->findOne(array('_id' => $document['_id']));
		$this->assertEquals($document_native['u'], 1);

		// Remove and recheck
		$collection->remove(array('user_id' => 1));
		$document_native = $collection->findOne(array('user_id' => 1));
		$this->assertNull($document_native);

	}

}