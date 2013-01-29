<?php

class IndexTest extends MongoMinifyTest {
	

	/**
	 * Ensure a simple index can be created
	 */
	public function testEnsureSingle()
	{

		// Create a collection
		$collection = $this->getTestCollection();

		$collection->ensureIndex(array('user_id' => 1));

		$indexes = $collection->getIndexInfo();
		$this->assertArrayHasKey(1, $indexes);
		$this->assertEquals($indexes[1]['name'], 'u_1');

	}

	/**
	 * Assert that indexes with dot creation can be created
	 */
	public function testEnsureEmbedded()
	{

		// Create a collection
		$collection = $this->getTestCollection();

		$collection->ensureIndex(array('user_id' => 1, 'tags.slug' => 1));

		$indexes = $collection->getIndexInfo();
		$this->assertArrayHasKey(1, $indexes);
		$this->assertEquals($indexes[1]['name'], 'u_1_t_s_1');


	}


}