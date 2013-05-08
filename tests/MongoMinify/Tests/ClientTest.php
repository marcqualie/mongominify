<?php

class ClientTest extends MongoMinifyTest {

    /**
     * This will test all miscelaneous methods for code coverage
     */
    public function testMisic()
    {

        // Connect
        $mongo = new MongoMinify\Client();

        // List connections
        $mongo->connect();
        $connections = $mongo->getConnections();
        $this->assertTrue(count($connections) >= 1);

        // Check string reference
        $this->assertEquals((String) $mongo, 'localhost:27017');

        // List Databases
        $dbs = $mongo->listDbs();
        $this->assertArrayHasKey('databases', $dbs);

        // List hosts
        $hosts = $mongo->getHosts();
        $this->assertTrue(is_array($hosts));

        // Read preference
        $mongo->setReadPreference(\MongoClient::RP_SECONDARY_PREFERRED);
        $this->assertEquals($mongo->getReadPreference(), array(
            "type" => "secondaryPreferred"
        ));

        // Select Collection
        $db = $mongo->selectDb('mongominify_test');
        $collection = $mongo->selectCollection($db, 'collection_test');
        $this->assertEquals((String) $collection, 'mongominify_test.collection_test');

        // Close connection
        $mongo->close();

    }

}
