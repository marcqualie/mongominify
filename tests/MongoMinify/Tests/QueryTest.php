<?php

class QueryTest extends MongoMinifyTest {

    public function testCompress()
    {

        $collection = $this->getTestCollection();
        $data = array(
            'user_id' => 1,
            'contact' => array(
                'email' => 'user1@example.com'
            )
        );
        $query = new MongoMinify\Query($data, $collection);
        $query->compress();
        $this->assertEquals($query->compressed, array(
            'u' => 1,
            'c.e' => 'user1@example.com'
        ));

    }


    public function testCompressNoSchema()
    {

        $collection = $this->client->currentDb()->collection_without_schema;
        $data = array(
            'user_id' => 1,
            'contact' => array(
                'email' => 'user1@example.com'
            )
        );
        $query = new MongoMinify\Query($data, $collection);
        $query->compress();
        $this->assertEquals($query->compressed, $data);

    }


    public function testNumericArrayCompression()
    {
        $collection = $this->getTestCollection();
        $data = array(
            'contact.email' => 'user1@example.com'
        );
        $query = new MongoMinify\Query($data, $collection);
        $query->compress();
        $this->assertEquals($query->compressed, array(
            'c.e' => 'user1@example.com'
        ));
    }


    public function testModifierDotSyntax()
    {

        $collection = $this->getTestCollection();
        $data = array(
            'notifications.requests' => array(
                '$gt' => 0
            )
        );
        $query = new MongoMinify\Query($data, $collection);
        $query->compress();
        $this->assertEquals($query->compressed, array(
            'n.r' => array(
                '$gt' => 0
            )
        ));

    }

}
