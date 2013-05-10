<?php

class DbTest extends MongoMinifyTest {

    public function testCreateCollection()
    {
        $collection = $this->getTestCollection();
        $db = $collection->db;
        $collection_name = (String) $collection;
        $collection->drop();

        // Create new collection
        $collection = $db->createCollection($collection_name);
        $this->assertEquals((String) $collection, (String) $db . '.' . $collection_name);
    }

    public function testListCollections()
    {
        $db = $this->client->currentDb();
        $collections = $db->listCollections();
        $this->assertTrue(is_array($collections));
    }

    public function testDropDatabase()
    {
        $document = array('_id' => 1);
        $this->client->mongominify_fake_db_test->test->save($document);
        
        // Get db list
        $dbs = $this->client->listDbs();
        $db_list = array();
        foreach ($dbs['databases'] as $db)
        {
            $db_list[$db['name']] = true;
        }
        $this->assertArrayHasKey('mongominify_fake_db_test', $db_list);

        // Drop then get db list again
        $this->client->mongominify_fake_db_test->drop();
        $dbs = $this->client->listDbs();
        $db_list = array();
        foreach ($dbs['databases'] as $db)
        {
            $db_list[$db['name']] = true;
        }
        $this->assertFalse(isset($db_list['mongominify_fake_db_test']));

    }

}
