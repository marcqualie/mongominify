<?php

class CollectionTest extends MongoMinifyTest {


    /**
     * Make sure collections can be accessed via dot syntax
     */
    public function testGet()
    {
        $collection1 = $this->client->currentDb()->selectCollection('dot.syntax.name');
        $collection2 = $this->client->currentDb()->dot->syntax->name;
        $this->assertEquals($collection1->getName(), $collection2->getName());
    }


    /**
     * Make sure collection is represented as a string
     */
    public function testToString()
    {
         $collection = $this->client->currentDb()->selectCollection('dot.syntax.name');
         $this->assertEquals((String) $collection, 'mongominify.dot.syntax.name');
    }


    /**
     * Ensure a simple index can be created
     */
    public function testEnsureSingle()
    {

        // Create a collection
        $collection = $this->getTestCollection();
        $collection->ensureIndex(array('user_id' => 1));

        // Assert that index is created
        $indexes = $collection->getIndexInfo();
        $this->assertCount(2, $indexes);
        $this->assertArrayHasKey(1, $indexes);
        $this->assertEquals($indexes[1]['name'], 'u_1');

        // Assert that is can be deleted again
        $collection->deleteIndex(array('user_id' => 1));
        $indexes = $collection->getIndexInfo();
        $this->assertCount(1, $indexes);

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
