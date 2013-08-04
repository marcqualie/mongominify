<?php

namespace MongoMinify\Test;

class FindTest extends TestCase {


	/**
	 * Creates a fake document
	 * @return [type] [description]
	 */
	public function insertTestDocument($collection)
	{
		$document = array(
			'user_id' => 1,
			'tags' => array(
				array(
					'slug' => 'test',
					'name' => 'test'
				)
			),
			'role' => 'moderator',
			'contact' => array(
				'preferred' => 'email',
				'email' => array(
					'work' => array(
						'office' => 'test1_work_office@example.com',
						'mobile' => 'test1_work_mobile@example.com'
					)
				)
			)
		);
		$collection->insert($document);
		return $document;
	}


	/**
	 * Find data based on flat document structure
	 */
	public function testFindSimple()
	{

		// Create a collection
		$collection = $this->getTestCollection();
		$document = $this->insertTestDocument($collection);

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
		$document = $this->insertTestDocument($collection);

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
		$document = $this->insertTestDocument($collection);

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
		$document = $this->insertTestDocument($collection);

		// Retreive compressed doc
		$document_native = $collection->native->findOne(array('_id' => $document['_id']));
		$this->assertEquals($document_native['c']['a'], 0);

		// Standard find to make sure is looked up correctly
		$document_find = $collection->findOne(array('contact.preferred' => 'email'));
		$this->assertEquals($document, $document_find);

	}


	/**
	 * Make sure $in queries perform correctly
	 */
	public function testFindIn()
	{

		// Create a collection
		$collection = $this->getTestCollection();
		$document = $this->insertTestDocument($collection);

		// Retreive compressed document
		$document_native = $collection->native->findOne(array('contact.preferred' => array('$in' => array('email', 'phone'))));
		$this->assertEquals($document_native['c']['a'], 0);

		// Retreive standard document
		$document_native = $collection->findOne(array('contact.preferred' => array('$in' => array('email', 'phone'))));
		$this->assertEquals($document_native['contact']['preferred'], 'email');

	}


	/**
	 * Distinct Test
	 */
	public function testDistinctSimple()
	{

		// Create a collection
		$collection = $this->getTestCollection();

		// Insert fake data
		$documents = array(
			array(
				'_id' => 1,
				'role' => 'admin',
				'gender' => 'male'
			),
			array(
				'_id' => 2,
				'role' => 'moderator',
				'gender' => 'female'
			),
			array(
				'_id' => 3,
				'role' => 'moderator',
				'gender' => 'male'
			),
			array(
				'_id' => 4,
				'role' => 'none',
				'gender' => 'female'
			),
			array(
				'_id' => 5,
				'role' => 'moderator',
				'gender' => 'male'
			),
			array(
				'_id' => 6,
				'role' => 'user'
			)
		);
		$collection->batchInsert($documents);

		// Test distinct values
		$roles = $collection->distinct('gender');
		$this->assertEquals($roles, array('male', 'female'));

		// Test distinct values (enum)
		$roles = $collection->distinct('role');
		$this->assertEquals($roles, array('admin', 'moderator', 'none', 'user'));

	}



	/**
	 * Distinct Test
	 */
	public function testDistinctQuery()
	{

		// Create a collection
		$collection = $this->getTestCollection();

		// Insert fake data
		$documents = array(
			array(
				'_id' => 1,
				'role' => 'admin'
			),
			array(
				'_id' => 2,
				'role' => 'moderator'
			),
			array(
				'_id' => 3,
				'role' => 'moderator'
			),
			array(
				'_id' => 4,
				'role' => 'none'
			),
			array(
				'_id' => 5,
				'role' => 'moderator'
			),
			array(
				'_id' => 6,
				'role' => 'user'
			)
		);
		$collection->batchInsert($documents);

		// Test distinct values
		$roles = $collection->distinct('role', array(
			'role' => 'moderator'
		));
		$this->assertEquals($roles, array('moderator'));

	}


	/**
	 * Find data based on flat document structure
	 */
	public function testFindFields()
	{

		// Create a collection
		$collection = $this->getTestCollection();
		$document = $this->insertTestDocument($collection);

		// Make sure document has the correct format after saving
		$found = $collection->findOne(array(), array('_id' => 0, 'user_id' => 1, 'role' => 1));
		$this->assertEquals($found, array('user_id' => 1, 'role' => 'moderator'));

	}

}
