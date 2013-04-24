<?php

class InsertTest extends MongoMinifyTest {
	

	/**
	 * Test saving a document to the database
	 */
	public function testSave()
	{

		// Create a collection
		$collection = $this->getTestCollection();

		// Fake Document
		$document = array(
			'user_id' => 1,
			'email' => 'test1@example.com'
		);
		$collection->save($document);

		// Make sure document has the correct format after saving
		$this->assertArrayHasKey('_id', $document);
		$this->assertArrayHasKey('user_id', $document);
		$this->assertArrayHasKey('email', $document);

		// Check Data stored in database is compressed
		$document_object = new MongoMinify\Document($document, $collection);
		$document_native = $collection->native->findOne(array('_id' => $document['_id']));
		$this->assertArrayHasKey('u', $document_native);
		$this->assertArrayHasKey('e', $document_native);

	}


	/**
	 * Test saving a document to the database
	 */
	public function testInsert()
	{

		// Create a collection
		$collection = $this->getTestCollection();

		// Fake Document
		$document = array(
			'user_id' => 2,
			'email' => 'test2@example.com'
		);
		$collection->insert($document);

		// Make sure document has the correct format after saving
		$this->assertArrayHasKey('_id', $document);
		$this->assertArrayHasKey('user_id', $document);
		$this->assertArrayHasKey('email', $document);

		// Check Data stored in database is compressed
		$document_object = new MongoMinify\Document($document, $collection);
		$document_native = $collection->native->findOne(array('_id' => $document['_id']));
		$this->assertArrayHasKey('u', $document_native);
		$this->assertArrayHasKey('e', $document_native);

	}


	/**
	 * Make sure we can batch insert
	 */
	public function testBatchInsert()
	{

		// Create a collection
		$collection = $this->getTestCollection();

		// Fake Documents
		$documents = array();
		for ($i = 1; $i <= 10; $i++)
		{
			$documents[] = array(
				'user_id' => $i,
				'email' => 'test' . $i . '@example.com'
			);
		}
		$collection->batchInsert($documents);
		$document = $documents[0];

		// Make sure document has the correct format after saving
		$this->assertArrayHasKey('_id', $document);
		$this->assertArrayHasKey('user_id', $document);
		$this->assertArrayHasKey('email', $document);

		// Check Data stored in database is compressed
		$document_object = new MongoMinify\Document($document, $collection);
		$document_native = $collection->native->findOne(array('_id' => $document['_id']));
		$this->assertArrayHasKey('u', $document_native);
		$this->assertArrayHasKey('e', $document_native);

	}

}
