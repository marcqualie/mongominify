<?php

namespace MongoMinify\Test;

use MongoCursorTimeoutException;

class CursorTest extends TestCase
{


    /**
     * Make sure cursors are limited
     */
    public function testLimit()
    {

        // Create a collection
        $collection = $this->getTestCollection();

        // Fake Document
        $documents = array();
        for ($i = 0; $i < 10; $i++) {
            $documents[] = array(
                'user_id' => $i,
                'email' => 'test' . $i . '@example.com',
            );
        }
        $collection->batchInsert($documents);

        // Make sure document has the correct format after saving
        $cursor = $collection->find(array('user_id' => array('$gt' => 5)))->limit(1);
        foreach ($cursor as $document) {
            $this->assertEquals($cursor->key(), (String) $document['_id']);
            $this->assertEquals($document['user_id'], 6);
        }

    }


    /**
     * Check if cursors can be skipped
     */
    public function testSkip()
    {

        // Create a collection
        $collection = $this->getTestCollection();

        // Fake Document
        $documents = array();
        for ($i = 0; $i < 20; $i++) {
            $documents[] = array(
                'user_id' => $i,
                'email' => 'test' . $i . '@example.com',
            );
        }
        $collection->batchInsert($documents);

        // Make sure document has the correct format after saving
        $found = $collection->find(array('user_id' => array('$gt' => 5)))->skip(10)->limit(1);
        foreach ($found as $document) {
            $this->assertEquals($document['user_id'], 16);
        }

    }


    /**
     * Make sure counts are applied even after skipping
     */
    public function testCount()
    {

        // Create a collection
        $collection = $this->getTestCollection();

        // Fake Document
        $documents = array();
        for ($i = 0; $i < 10; $i++) {
            $documents[] = array(
                'user_id' => $i,
                'email' => 'test' . $i . '@example.com',
            );
        }
        $collection->batchInsert($documents);

        // Make sure document has the correct format after saving
        $count = $collection->find(array('user_id' => array('$gt' => 5)))->skip(1)->count();
        $this->assertEquals($count, 4);

    }


    /**
     * Test global timeouts and native mappings
     */
    public function testGlobalTimeout()
    {

        // Create collection
        $default_timeout = \MongoCursor::$timeout;
        $collection = $this->getTestCollection();

        // Create a cursor object
        $cursor = $collection->find();
        $native_cursor = $cursor->native;

        // Assert cursor timeouts are updated
        $this->assertEquals($cursor::$timeout, $native_cursor::$timeout);

        // Check that static timeouts are binded
        $default_timeout = \MongoCursor::$timeout;
        $cursor::$timeout = $default_timeout / 2;
        $this->assertEquals(\MongoCursor::$timeout, $cursor::$timeout);
        $this->assertEquals(\MongoMinify\Cursor::$timeout, $cursor::$timeout);
        \MongoCursor::$timeout = 200;
        $this->assertEquals(\MongoCursor::$timeout, \MongoMinify\Cursor::$timeout);
        $this->assertEquals(\MongoMinify\Cursor::$timeout, 200);
        \MongoCursor::$timeout = $default_timeout;

    }


    /**
     * Test timeout on single cursor
     * @expectedException MongoCursorTimeoutException
     */
    public function testInstanceTimeout()
    {
        $collection = $this->getTestCollection();
        $documents = array();
        for ($i = 0; $i < 10000; $i++) {
            $documents[] = array(
                '_id' => $i,
                'random' => rand(0, 999999)
            );
            if ($i % 1000 === 0) {
                $collection->batchInsert($documents);
                $documents = array();
            }
        }
        $collection->batchInsert($documents);
        $cursor = $collection->find(array('random' => array('$gte' => 0)))->sort(array('random' => -1));
        $cursor->timeout(1);
        $cursor->getNext();
    }


    /**
     * Sorting (Ascending)
     */
    public function testSortAsc()
    {

        // Create a collection
        $collection = $this->getTestCollection();

        // Insert fake data
        $documents = array(
            array(
                '_id' => 3,
                'role' => 'moderator'
            ),
            array(
                '_id' => 2,
                'role' => 'moderator'
            ),
            array(
                '_id' => 6,
                'role' => 'user'
            ),
            array(
                '_id' => 4,
                'role' => 'none'
            ),
            array(
                '_id' => 1,
                'role' => 'admin'
            ),
            array(
                '_id' => 5,
                'role' => 'moderator'
            )
        );
        $collection->batchInsert($documents);

        // Test distinct values
        $cursor = $collection->find();
        $cursor->sort(array('_id' => 1));
        $data = iterator_to_array($cursor, false);
        $this->assertEquals($data[0]['_id'], 1);
        $this->assertEquals($data[1]['_id'], 2);
        $this->assertEquals($data[2]['_id'], 3);

    }


    /**
     * Sorting (Ascending)
     */
    public function testSortDesc()
    {

        // Create a collection
        $collection = $this->getTestCollection();

        // Insert fake data
        $documents = array(
            array(
                '_id' => 3,
                'role' => 'moderator'
            ),
            array(
                '_id' => 2,
                'role' => 'moderator'
            ),
            array(
                '_id' => 6,
                'role' => 'user'
            ),
            array(
                '_id' => 4,
                'role' => 'none'
            ),
            array(
                '_id' => 1,
                'role' => 'admin'
            ),
            array(
                '_id' => 5,
                'role' => 'moderator'
            )
        );
        $collection->batchInsert($documents);

        // Test distinct values
        $cursor = $collection->find();
        $cursor->sort(array('_id' => -1));
        $data = iterator_to_array($cursor, false);
        $this->assertEquals($data[0]['_id'], 6);
        $this->assertEquals($data[1]['_id'], 5);
        $this->assertEquals($data[2]['_id'], 4);

    }


    /**
     * Test Info
     */
    public function testInfo()
    {
        $collection = $this->getTestCollection();
        $cursor = $collection
            ->find(array('test' => 5), array('field1' => 1))
            ->skip(2)
            ->limit(3)
            ->timeout(0);
        $info = $cursor->info();
        $this->assertArrayHasKey('ns', $info);
        $this->assertEquals('mongominify.test', $info['ns']);
        $this->assertEquals(array('test' => 5), $info['query']);
        $this->assertEquals(array('field1' => 1), $info['fields']);
        $this->assertEquals(2, $info['skip']);
        $this->assertEquals(3, $info['limit']);
    }


    /**
     * Inline Helpers
     */
    public function testAsArrayHelper()
    {
        $collection = $this->getTestCollection();
        $documents = array(
            array(
                '_id' => 1,
                'role' => 'admin'
            ),
            array(
                '_id' => 3,
                'role' => 'moderator'
            )
        );
        $collection->batchInsert($documents);
        $array = $collection->find()->sort(array('_id' => -1))->asArray();
        $this->assertTrue(is_array($array));
        $this->assertEquals($array, array(
            array('_id' => 3, 'role' => 'moderator'),
            array('_id' => 1, 'role' => 'admin')
        ));
    }


    /**
     * Test Reset
     */
    public function testReset()
    {

        // Create a collection
        $collection = $this->getTestCollection();

        // Fake Document
        $documents = array();
        for ($i = 1; $i <= 5; $i++) {
            $documents[] = array(
                'user_id' => $i,
                'email' => 'test' . $i . '@example.com',
            );
        }
        $collection->batchInsert($documents);

        // Make sure cursor is expended, then reset to iterate again
        $cursor = $collection->find(array(), array('_id' => 0, 'user_id' => 1));
        $this->assertEquals(array('user_id' => 1), $cursor->getNext());
        $this->assertEquals(array('user_id' => 2), $cursor->getNext());
        $this->assertEquals(array('user_id' => 3), $cursor->getNext());
        $cursor->reset();
        $this->assertEquals(array('user_id' => 1), $cursor->getNext());

    }
}
