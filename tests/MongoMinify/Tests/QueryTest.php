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


    public function testElemMatch()
    {

        $collection = $this->getTestCollection();
        $data = array(
            array(
                '_id' => 1,
                'gender' => 'female',
                'tags' => array(
                    array(
                        'slug' => 'test',
                        'name' => 'Test',
                        'count' => 2
                    ),
                    array(
                        'slug' => 'test-2',
                        'name' => 'Test 2',
                        'count' => 6
                    )
                )
            ),
            array(
                '_id' => 2,
                'gender' => 'male',
                'tags' => array(
                    array(
                        'slug' => 'test-3',
                        'name' => 'Test',
                        'count' => 4
                    )
                )
            ),
            array(
                '_id' => 3,
                'gender' => 'female',
                'tags' => array(
                    array(
                        'slug' => 'test-4',
                        'name' => 'Test 4',
                        'count' => 7
                    ),
                    array(
                        'slug' => 'test',
                        'name' => 'Test',
                        'count' => 1
                    )
                )
            ),
            array(
                '_id' => 4,
                'gender' => 'male',
                'tags' => array(
                    array(
                        'slug' => 'test-4',
                        'name' => 'Test 4',
                        'count' => 6
                    ),
                    array(
                        'slug' => 'test',
                        'name' => 'Test',
                        'count' => 5
                    )
                )
            )
        );
        $collection->batchInsert($data);

        // Test normal matching
        $find = $collection->find(
            array(
                'gender' => 'female',
                'tags' => array(
                    '$elemMatch' => array(
                        'slug' => 'test'
                    )
                )
            ),
            array(
                '_id' => 1
            )
        )->asArray();
        $this->assertEquals($find, array(array('_id' => 1), array('_id' => 3)));

        // Test operator matching
        $find = $collection->find(
            array(
                'tags' => array(
                    '$elemMatch' => array(
                        'count' => array(
                            '$gte' => 5
                        )
                    )
                )
            ),
            array(
                '_id' => 1
            )
        )->asArray();
        $this->assertEquals($find, array(array('_id' => 1), array('_id' => 3), array('_id' => 4)));

    }


    public function testAndOr()
    {
        $collection = $this->getTestCollection();
        $data = array(
            array(
                '_id' => 1,
                'email' => 'user1@example.com',
                'gender' => 'female'
            ),
            array(
                '_id' => 2,
                'email' => 'user2@example.com',
                'gender' => 'female'
            ),
            array(
                '_id' => 3,
                'email' => 'user3@example.com',
                'gender' => 'male'
            )
        );
        $collection->batchInsert($data);

        // Test $and
        $find = $collection->find(
            array(
                '$and' => array(
                    array(
                        'gender' => 'female',
                    ),
                    array(
                        'email' => 'user2@example.com'
                    )
                )
            ),
            array(
                '_id' => 1
            )
        )->asArray();
        $this->assertEquals($find, array(array('_id' => 2)));
        $find = $collection->find(
            array(
                '$and' => array(
                    array(
                        'gender' => 'male',
                    ),
                    array(
                        'email' => 'user2@example.com'
                    )
                )
            ),
            array(
                '_id' => 1
            )
        )->asArray();
        $this->assertEquals($find, array());

        // Test $or
        $find = $collection->find(
            array(
                '$or' => array(
                    array(
                        'email' => 'user1@example.com'
                    ),
                    array(
                        'email' => 'user2@example.com'
                    )
                )
            ),
            array(
                '_id' => 1
            )
        )->asArray();
        $this->assertEquals($find, array(array('_id' => 1), array('_id' => 2)));
        $find = $collection->find(
            array(
                '$or' => array(
                    array(
                        'email' => 'user4@example.com',
                    ),
                    array(
                        'email' => 'user5@example.com'
                    )
                )
            ),
            array(
                '_id' => 1
            )
        )->asArray();
        $this->assertEquals($find, array());

    }


    public function testNumericKeys()
    {

        $collection = $this->getTestCollection();
        $data = array(
            'user_id' => 1,
            'meta' => array(
                'following' => array(
                    '1234' => array(
                        'mutal' => true
                    )
                )
            )
        );
        $query = new MongoMinify\Query($data, $collection);
        $query->compress();
        $this->assertEquals($query->compressed, array(
            'u' => 1,
            'm.following.1234.mutal' => true
        ));

    }

}
