<?php

class DocumentTest extends MongoMinifyTest {

    public function testDotSyntax()
    {

        $collection = $this->getTestCollection();

        $data = array(
            'user_id' => 1,
            'contact' => array(
                'email' => 'user1@example.com'
            )
        );
        $document = new MongoMinify\Document($data, $collection);
        $document->compress();
        $dotSyntax = $document->asDotSyntax();
        $this->assertEquals($dotSyntax, array(
            'u' => 1,
            'c.e' => 'user1@example.com'
        ));

    }


    public function testNumericIndexSubsets()
    {

        $collection = $this->getTestCollection();
        $data = array(
            'user_id' => 1,
            'tags' => array(
                array(
                    'slug' => 'awesome',
                    'name' => 'Awesome',
                    'count' => 1
                )
            )
        );
        $document = new MongoMinify\Document($data, $collection);
        $document->compress();
        $this->assertEquals($document->compressed, array(
            'u' => 1,
            't' => array(
                array(
                    's' => 'awesome',
                    'n' => 'Awesome',
                    'c' => 1
                )
            )
        ));

    }


    public function testFindAndModify()
    {

        $collection = $this->getTestCollection();
        $data = array(
            'user_id' => 1,
            'contact' => array(
                'email' => 'user1@marcqualie.com'
            ),
            'notifications' => array(
                'messages' => 1,
                'requests' => 8
            )
        );
        $collection->insert($data);
        $new_document = $collection->findAndModify(
            array(
                'user_id' => 1
            ),
            array(
                '$set' => array(
                    'notifications.messages' => 0
                )
            ),
            array(
                'notifications' => 1
            ),
            array(
                'sort' => array(
                    'user_id' => 1
                ),
                'new' => true
            )
        );
        unset($new_document['_id']);
        $this->assertEquals($new_document, array(
            'notifications' => array(
                'messages' => 0,
                'requests' => 8
            )
        ));

    }

}
