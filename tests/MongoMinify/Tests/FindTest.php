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
			'email' => 'test1@example.com',
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

}