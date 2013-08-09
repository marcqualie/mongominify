<?php

namespace MongoMinify\Test;
use MongoMinify\Client;
use Exception;

class CollectionTest extends TestCase
{
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
     * Ensure an index can be created with an string
     */
    public function testEnsureIndexString()
    {
        // Create a collection
        $collection = $this->getTestCollection();

        // Assert that index can be created with string
        $collection->ensureIndex('user_id');
        $indexes = $collection->getIndexInfo();
        $this->assertCount(2, $indexes);
        $this->assertArrayHasKey(1, $indexes);
        $this->assertEquals($indexes[1]['name'], 'u_1');
        $collection->deleteIndex('user_id');
    }

    /**
     * Ensure an index can be created with an array
     */
    public function testEnsureIndexArray()
    {
        // Create a collection
        $collection = $this->getTestCollection();

        // Assert that index can be created with array
        $collection->ensureIndex(array('user_id' => 1));
        $indexes = $collection->getIndexInfo();
        $this->assertCount(2, $indexes);
        $this->assertArrayHasKey(1, $indexes);
        $this->assertEquals($indexes[1]['name'], 'u_1');
        $collection->deleteIndex('user_id');
    }

    /**
     * Ensure an index can be deleted with a string
     */
    public function testDeleteIndexString()
    {
        // Create a collection
        $collection = $this->getTestCollection();

        // Assert that index can be deleted with string
        $collection->ensureIndex('user_id');
        $indexes = $collection->getIndexInfo();
        $this->assertCount(2, $indexes);
        $this->assertArrayHasKey(1, $indexes);
        $this->assertEquals($indexes[1]['name'], 'u_1');
        $collection->deleteIndex('user_id');
        $indexes = $collection->getIndexInfo();
        $this->assertCount(1, $indexes);
    }

    /**
     * Ensure an index can be deleted with an array
     */
    public function testDeleteIndexArray()
    {
        // Create a collection
        $collection = $this->getTestCollection();

        // Assert that index can be deleted with array
        $collection->ensureIndex('user_id');
        $indexes = $collection->getIndexInfo();
        $this->assertCount(2, $indexes);
        $this->assertArrayHasKey(1, $indexes);
        $this->assertEquals($indexes[1]['name'], 'u_1');
        $collection->deleteIndex(array('user_id' => 1));
        $indexes = $collection->getIndexInfo();
        $this->assertCount(1, $indexes);
    }

    /**
     * Ensure all indexes can be deleted
     */
    public function testDeleteAllIndexes()
    {
        // Create a collection
        $collection = $this->getTestCollection();

        // Assert that all indexes can be deleted
        $collection->ensureIndex(array('user_id' => 1));
        $this->assertCount(2, $collection->getIndexInfo());
        $collection->deleteIndexes();
        $this->assertCount(1, $collection->getIndexInfo());
    }

    /**
     * Assert that indexes with dot creation can be created
     */
    public function testEnsureIndexEmbedded()
    {

        // Create a collection
        $collection = $this->getTestCollection();
        $collection->ensureIndex(array('user_id' => 1, 'tags.slug' => 1));

        $indexes = $collection->getIndexInfo();
        $this->assertArrayHasKey(1, $indexes);
        $this->assertEquals($indexes[1]['name'], 'u_1_t_s_1');

    }

    /**
     * Test Counting
     */
    public function testCount()
    {
        $mongo = new \MongoClient();
        $collection = $mongo->selectCollection('mongominify', 'test');
        $collection = $this->getTestCollection();
        for ($i = 0; $i < 69; $i++) {
            $document = array(
                '_id' => $i,
                'random' => rand(0, 9999)
            );
            $collection->insert($document);
        }
        $this->assertEquals($collection->count(), 69);
        $collection->remove(array(
            '_id' => array('$gte' => 60)
        ));
        $this->assertEquals($collection->count(), 60);
        $this->assertEquals($collection->count(array('_id' => array('$gte' => 50))), 10);
        $this->assertEquals($collection->count(array('_id' => array('$gte' => 50)), 5), 5);
        $this->assertEquals($collection->count(array('_id' => array('$gte' => 50)), null, 3), 7);
        $this->assertEquals($collection->count(array('_id' => array('$gte' => 50)), 10, 6), 4);
    }

    /**
     * Test read preferences
     */
    public function testReadPreferences()
    {
        $collection = $this->getTestCollection();
        $collection->setReadPreference(\MongoClient::RP_SECONDARY_PREFERRED);
        $this->assertEquals($collection->getReadPreference(), array('type' => 'secondaryPreferred'));
    }

    /**
     * Test setting schema
     */
    public function testSetSchemaByName()
    {

        $collection = $this->getTestCollection();
        $collection->setSchemaByName();
        $collection->setSchemaByName('test');

    }

    /**
     * Test exception throwing
     */
    public function testSchemaExceptions()
    {
        $client = $this->client;
        $collection = $this->getTestCollection();

        // Make sure php returns config data
        $client->schema_format = 'php';
        $client->schema_dir = '/tmp';
        file_put_contents('/tmp/mongominify.test.php', '<?php /* No Schema */');
        $dead = false;
        try {
            $client->currentDb()->test->setSchemaByName();
        } catch (Exception $e) {
            $dead = true;
        }
        $this->assertTrue($dead);

        // Make sure php returns config data
        $client->schema_format = 'json';
        $client->schema_dir = '/tmp';
        file_put_contents('/tmp/mongominify.test.json', ':: /* Invalid JSON */');
        $dead = false;
        try {
            $client->currentDb()->test->setSchemaByName();
        } catch (Exception $e) {
            $dead = true;
        }
        $this->assertTrue($dead);

        // Catch invalid formats
        $client->schema_format = 'rb';
        $client->schema_dir = '/tmp';
        $dead = false;
        try {
            $client->currentDb()->test->setSchemaByName();
        } catch (Exception $e) {
            $dead = true;
        }
        $this->assertTrue($dead);

    }

}
