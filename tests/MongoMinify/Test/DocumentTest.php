<?php

namespace MongoMinify\Test;
use MongoMinify\Document;

class DocumentTest extends TestCase
{
    public function testDotSyntax()
    {

        $collection = $this->getTestCollection();

        $data = array(
            'user_id' => 1,
            'contact' => array(
                'email' => 'user1@example.com'
            )
        );
        $document = new Document($data, $collection);
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
        $document = new Document($data, $collection);
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


    /**
     * Wildcard keys should compress
     */
    public function testWildcardSchemaCompression()
    {
        // Compress document
        $collection = $this->getTestCollection();
        $document = array(
            '_id' => 1,
            'tags' => array(
                2 => array(
                    'slug' => 'test-tag',
                    'name' => 'Test Tag'
                )
            ),
            'organisations' => array(
                '13l22' => array(
                    'name' => 'example.org',
                    'role' => 'CEO'
                )
            )
        );
        $document_object = new Document($document, $collection);
        $document_object->compress();

        // Assert compressed object is as expected
        $this->assertEquals(
            array(
                '_id' => 1,
                't' => array(
                    2 => array(
                        's' => 'test-tag',
                        'n' => 'Test Tag'
                    )
                ),
                'o' => array(
                    '13l22' => array(
                        'n' => 'example.org',
                        'r' => 'CEO'
                    )
                )
            ),
            $document_object->compressed
        );

    }


    /**
     * Wildcard keys should decompress
     */
    public function testWildcardSchemaDecompression()
    {

        // Compress document
        $collection = $this->getTestCollection();
        $document = array(
            '_id' => 1,
            't' => array(
                2 => array(
                    's' => 'test-tag',
                    'n' => 'Test Tag'
                )
            ),
            'o' => array(
                '13l22' => array(
                    'n' => 'example.org',
                    'r' => 'CEO'
                )
            )
        );
        $document_object = new Document($document, $collection);
        $document_object->state = 'compressed';
        $document_object->decompress();

        // Assert compressed object is as expected
        $this->assertEquals(
            array(
                '_id' => 1,
                'tags' => array(
                    2 => array(
                        'slug' => 'test-tag',
                        'name' => 'Test Tag'
                    )
                ),
                'organisations' => array(
                    '13l22' => array(
                        'name' => 'example.org',
                        'role' => 'CEO'
                    )
                )
            ),
            $document_object->data
        );

    }


    /**
     * Multi-level Wildcard keys should compress
     */
    public function testMultiLevelWildcardSchemaCompression()
    {
        // Compress document
        $collection = $this->getTestCollection();
        $document = array(
            '_id' => 1,
            'tags' => array(
                2 => array(
                    'slug' => 'test-tag',
                    'name' => 'Test Tag'
                )
            ),
            'organisations' => array(
                '13l22' => array(
                    'name' => 'example.org',
                    'role' => 'CEO',
                    'partners' => array(
                        'example_com' => array(
                            'link' => 'parent'
                        )
                    )
                )
            )
        );
        $document_object = new Document($document, $collection);
        $document_object->compress();

        // Assert compressed object is as expected
        $this->assertEquals(
            array(
                '_id' => 1,
                't' => array(
                    2 => array(
                        's' => 'test-tag',
                        'n' => 'Test Tag'
                    )
                ),
                'o' => array(
                    '13l22' => array(
                        'n' => 'example.org',
                        'r' => 'CEO',
                        'p' => array(
                            'example_com' => array(
                                'l' => 'parent'
                            )
                        )
                    )
                )
            ),
            $document_object->compressed
        );

    }


    /**
     * Multi-level Wildcard keys should de-compress
     */
    public function testMultiLevelWildcardSchemaDeCompression()
    {
        // Compress document
        $collection = $this->getTestCollection();
        $document =  array(
            '_id' => 1,
            't' => array(
                2 => array(
                    's' => 'test-tag',
                    'n' => 'Test Tag'
                )
            ),
            'o' => array(
                '13l22' => array(
                    'n' => 'example.org',
                    'r' => 'CEO',
                    'p' => array(
                        'example_com' => array(
                            'l' => 'parent'
                        )
                    )
                )
            )
        );
        $document_object = new Document($document, $collection);
        $document_object->state = 'compressed';
        $document_object->decompress();

        // Assert compressed object is as expected
        $this->assertEquals(
            array(
                '_id' => 1,
                'tags' => array(
                    2 => array(
                        'slug' => 'test-tag',
                        'name' => 'Test Tag'
                    )
                ),
                'organisations' => array(
                    '13l22' => array(
                        'name' => 'example.org',
                        'role' => 'CEO',
                        'partners' => array(
                            'example_com' => array(
                                'link' => 'parent'
                            )
                        )
                    )
                )
            ),
            $document_object->data
        );

    }


    /**
     * Multi-level Wildcard keys should de-compress
     */
    public function testMultiLevelWildcardSchemaDeCompressionSequentialKeys()
    {
        // Compress document
        $collection = $this->getTestCollection();
        $document =  array(
            '_id' => 1,
            't' => array(
                2 => array(
                    's' => 'test-tag',
                    'n' => 'Test Tag'
                )
            ),
            'o' => array(
                0 => array(
                    'n' => 'example.org',
                    'r' => 'CEO',
                    'p' => array(
                        'example_com' => array(
                            'l' => 'parent'
                        )
                    )
                )
            )
        );
        $document_object = new Document($document, $collection);
        $document_object->state = 'compressed';
        $document_object->decompress();

        // Assert compressed object is as expected
        $this->assertEquals(
            array(
                '_id' => 1,
                'tags' => array(
                    2 => array(
                        'slug' => 'test-tag',
                        'name' => 'Test Tag'
                    )
                ),
                'organisations' => array(
                    0 => array(
                        'name' => 'example.org',
                        'role' => 'CEO',
                        'partners' => array(
                            'example_com' => array(
                                'link' => 'parent'
                            )
                        )
                    )
                )
            ),
            $document_object->data
        );

    }
}
