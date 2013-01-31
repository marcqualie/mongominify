<?php

class FindTest extends MongoMinifyTest {
	

	/**
	 * Find data based on flat document structure
	 */
	public function testFindSimple()
	{

		// Create a collection
		$collection = $this->getTestCollection();

		// Fake Document
		$document = array(
			'user_id' => 1,
			'email' => 'test1@example.com',
		);
		$collection->insert($document);

		// Make sure document has the correct format after saving
		$found = $collection->findOne(array('_id' => $document['_id']));
		foreach ($document as $key => $value)
		{
			$this->assertTrue(isset($found[$key]));
			$this->assertEquals($found[$key], $value);
		}

	}


	/**
	 * Find data based on flat document structure
	 */
	public function testFindEmbedded()
	{

		// Create a collection
		$collection = $this->getTestCollection();

		// Fake Document
		$document = array(
			'user_id' => 1,
			'tags' => array(
				array(
					'slug' => 'test',
					'name' => 'test'
				)
			),
			'contact' => array(
				'email' => array(
					'work' => array(
						'office' => 'test1_work_office@example.com',
						'mobile' => 'test1_work_mobile@example.com'
					)
				)
			)
		);
		$collection->insert($document);

		// Make sure document has the correct format after saving
		$found = $collection->findOne(array('_id' => $document['_id']));
		foreach ($document as $key => $value)
		{
			$this->assertTrue(isset($found[$key]));
			$this->assertEquals($found[$key], $value);
		}

	}


	/**
	 * Find data based on Enum values
	 */
	public function testFindEnum()
	{

		// Create a collection
		$collection = $this->getTestCollection();

		// Fake Document
		$document = array(
			'user_id' => 1,
			'role' => 'moderator'
		);
		$collection->insert($document);

		// Retreive compressed doc
		$document_native = $collection->native->findOne(array('_id' => $document['_id']));
		$this->assertEquals($document_native['r'], 1);
		
		// Standard find to make sure is looked up correctly
		$document_find = $collection->findOne(array('_id' => $document['_id']));
		$this->assertEquals($document, $document_find);
		

	}


	/**
	 * Find data based on Enum Embedded Values
	 */
	public function testFindEnumEmbedded()
	{


		// Create a collection
		$collection = $this->getTestCollection();

		// Fake Document
		$document = array(
			'user_id' => 1,
			'contact' => array(
				'preferred' => 'email'
			)
		);
		$collection->insert($document);

		// Retreive compressed doc
		$document_native = $collection->native->findOne(array('_id' => $document['_id']));
		$this->assertEquals($document_native['c']['a'], 0);
		
		// Standard find to make sure is looked up correctly
		$document_find = $collection->findOne(array('contact.preferred' => 'email'));
		$this->assertEquals($document, $document_find);

	}

}