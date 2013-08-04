<?php

namespace MongoMinify\Test;

class SchemaTest extends TestCase
{
    /**
     * Test JSON schema
     */
    public function testJSON()
    {

        // Create a collection
        $this->client->schema_format = 'json';
        $collection = $this->getTestCollection();

        // Fake Document
        $document = array(
            'user_id' => 1,
            'email' => 'test@example.com'
        );
        $collection->save($document);

        // Check Data stored in database is compressed
        $document_native = $collection->native->findOne(array('_id' => $document['_id']));
        $this->assertEquals($document_native['u'], 1);

    }

    /**
     * Test PHP schema
     */
    public function testPHP()
    {

        // Create a collection
        $this->client->schema_format = 'php';
        $collection = $this->getTestCollection();

        // Fake Document
        $document = array(
            'user_id' => 1,
            'email' => 'test@example.com'
        );
        $collection->save($document);

        // Check Data stored in database is compressed
        $document_native = $collection->native->findOne(array('_id' => $document['_id']));
        $this->assertEquals($document_native['u'], 1);

    }

    /**
     * test missing schema files
     */
    public function testMissingFile()
    {
        $collection = $this->client->mongominify->raw_test;
        $collection->drop();

        // Fake Document
        $document = array(
            'user_id' => 1,
            'email' => 'test@example.com'
        );
        $collection->save($document);

        // Assert no schema was applied
        $document_native = $collection->native->findOne(array('_id' => $document['_id']));
        $this->assertEquals($document_native['user_id'], 1);

    }

}
