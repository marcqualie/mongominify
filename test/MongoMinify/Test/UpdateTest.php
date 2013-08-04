<?php

namespace MongoMinify\Test;

class UpdateTest extends TestCase {

    /**
     * Test updating a document
     */
    public function testUpdateSet()
    {

        // Create a collection
        $collection = $this->getTestCollection();

        // Fake Document
        $document = array(
            'user_id' => 1,
            'email' => 'test@example.com',
            'tags' => array('tag1', 'tag2')
        );
        $new_tags = array('test1', 'test2');
        $collection->insert($document);
        $collection->update(array('user_id' => 1), array('$set' => array(
            'email' => 'test@example.com',
            'tags' => $new_tags
        )));

        // Check Data stored in database is compressed
        $document_native = $collection->native->findOne(array('e' => 'test@example.com'));
        $this->assertArrayHasKey('u', $document_native);
        $this->assertArrayHasKey('e', $document_native);
        $this->assertEquals($document_native['t'], $new_tags);

    }


    /**
     * Test updating a document
     */
    public function testUpdateInc()
    {

        // Create a collection
        $collection = $this->getTestCollection();

        // Fake Document
        $document = array(
            'user_id' => 1,
            'email' => 'test@example.com',
            'tags' => array(
                array(
                    'slug' => 'performance-horizon',
                    'name' => 'Performance Horizon',
                    'count' => 5
                )
            ),
            'notifications' => array(
                'messages' => 1,
                'requests' => 8
            )
        );
        $collection->insert($document);
        $collection->update(array('user_id' => 1), array('$inc' => array(
            'notifications.messages' => 10
        )));

        // Check Data stored in database is compressed
        $document_native = $collection->findOne(array('email' => 'test@example.com'));
        $this->assertEquals($document_native['notifications']['messages'], 11);

    }


    /**
     * Replace a full document
     */
    public function testUpdateFull()
    {

        // Create a collection
        $collection = $this->getTestCollection();

        // Fake Document
        $document = array(
            'user_id' => 1,
            'email' => 'test@example.com',
            'tags' => array(
                array(
                    'slug' => 'performance-horizon',
                    'name' => 'Performance Horizon',
                    'count' => 5
                )
            ),
            'notifications' => array(
                'messages' => 1,
                'requests' => 8
            )
        );
        $collection->insert($document);
        $collection->update(array('user_id' => 1), array(
            'user_id' => 1,
            'email' => 'test2@example.com',
            'tags' => array(
                array(
                    'slug' => 'performance-horizon-group',
                    'name' => 'Performance Horizon Group',
                    'count' => 5
                )
            ),
            'notifications' => array(
                'messages' => 10,
                'requests' => 3
            )
        ));

        // Check Data stored in database is compressed
        $document_after = $collection->findOne(array('email' => 'test2@example.com'));
        $this->assertEquals($document_after['tags'][0]['slug'], 'performance-horizon-group');
        $this->assertEquals($document_after['notifications']['messages'], 10);

        // Make sure an actual update was performed
        $document_after = $collection->findOne(array('email' => 'test@example.com'));
        $this->assertEquals($document_after, null);

    }


    /**
     * Test FindAndModify
     */
    public function testFindAndModify()
    {

         // Create a collection
        $collection = $this->getTestCollection();

        // Fake Document
        $document = array(
            'user_id' => 1,
            'email' => 'test@example.com',
            'tags' => array(
                array(
                    'slug' => 'performance-horizon',
                    'name' => 'Performance Horizon',
                    'count' => 5
                )
            ),
            'notifications' => array(
                'messages' => 1,
                'requests' => 8
            )
        );
        $collection->insert($document);

        // Test update and return original document with filered fields
        $updated = $collection->FindAndModify(array(
                'user_id' => 1
            ), array(
                'user_id' => 1,
                'email' => 'test2@example.com',
                'tags' => array(
                    array(
                        'slug' => 'performance-horizon-group',
                        'name' => 'Performance Horizon Group',
                        'count' => 5
                    )
                ),
                'notifications' => array(
                    'messages' => 10,
                    'requests' => 3
                )
            ), array(
                '_id' => 0,
                'user_id' => 1,
                'email' => 1
            ));
        $this->assertEquals($updated, array('user_id' => 1, 'email' => 'test@example.com'));

        // Test update and return new document
        $updated = $collection->FindAndModify(array(
                'user_id' => 1
            ), array(
                '$set' => array(
                    'email' => 'test3@example.com',
                    'notifications' => array(
                        'messages' => 6,
                        'requests' => 9
                    )
                )
            ), array(
                '_id' => 0,
                'email' => 1,
                'notifications' => 1
            ), array(
                'new' => true
            ));
        $this->assertEquals($updated, array('email' => 'test3@example.com', 'notifications' => array('messages' => 6, 'requests' => 9)));

    }

}
