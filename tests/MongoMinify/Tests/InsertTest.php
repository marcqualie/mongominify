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
	 * Test updating a document
	 */
	public function testUpdate()
	{

		// Create a collection
		$collection = $this->getTestCollection();

		// Fake Document
		$document = array(
			'user_id' => 1,
			'email' => 'test1@example.com',
			'tags' => array('tag1', 'tag2')
		);
		$new_tags = array('test1', 'test2');
		$collection->insert($document);
		$collection->update(array('user_id' => 1), array('$set' => array(
			'email' => 'test2@example.com',
			'tags' => $new_tags
		)));

		// Check Data stored in database is compressed
		$document_native = $collection->native->findOne(array('e' => 'test2@example.com'));
		$this->assertArrayHasKey('u', $document_native);
		$this->assertArrayHasKey('e', $document_native);
		$this->assertEquals($document_native['t'], $new_tags);

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