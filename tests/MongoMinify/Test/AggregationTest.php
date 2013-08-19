<?php

namespace MongoMinify\Test;
use MongoMinify\Pipeline;

class AggregationTest extends TestCase
{
    /**
     * Unit Test The Pipeline
     */
    public function testPipelineBuilder()
    {

        $this->markTestIncomplete();

        $collection = $this->getTestCollection();

        $pipeline_input = array(
            array(
                '$match' => array(
                    'contact.email' => array(
                        '$ne' => null
                    )
                )
            ),
            array(
                '$unwind' => '$tags'
            ),
            array(
                '$group' => array(
                    '_id' => '$gender',
                    'cnt' => array(
                        '$sum' => '$notifications.messages'
                    )
                )
            )
        );
        $pipeline_expected = array(
            array(
                '$match' => array(
                    'c' => array(
                        'e' => array(
                            '$ne' => null
                        )
                    )
                )
            ),
            array(
                '$unwind' => '$t'
            ),
            array(
                '$group' => array(
                    '_id' => '$g',
                    'cnt' => array(
                        '$sum' => '$n.m'
                    )
                )
            )
        );

        // Compress Pipeline Object
        $pipeline_object = new Pipeline($pipeline_input, $collection);
        $pipeline_object->compress();
        $this->assertEquals($pipeline_object->compressed, $pipeline_expected);

    }

    /**
     * Unit Test The Pipeline With Projections
     */
    public function testPipelineProjectionBuilder()
    {

        $this->markTestIncomplete();
        $collection = $this->getTestCollection();

        $pipeline_input = array(
            array(
                '$project' => array(
                    'contact.email' => 1,
                    'gender' => 1,
                    'notifications.messages' => 1,
                    'tags' => 1
                )
            ),
            array(
                '$match' => array(
                    'contact.email' => array(
                        '$ne' => null
                    )
                )
            ),
            array(
                '$unwind' => '$tags'
            ),
            array(
                '$group' => array(
                    '_id' => '$gender',
                    'cnt' => array(
                        '$sum' => '$notifications.messages'
                    )
                )
            )
        );
        $pipeline_expected = array(
            array(
                '$project' => array(
                    'c.e' => 1,
                    'g' => 1,
                    'n.m' => 1,
                    't' => 1
                )
            ),
            array(
                '$match' => array(
                    'c' => array(
                        'e' => array(
                            '$ne' => null
                        )
                    )
                )
            ),
            array(
                '$unwind' => '$t'
            ),
            array(
                '$group' => array(
                    '_id' => '$g',
                    'cnt' => array(
                        '$sum' => '$n.m'
                    )
                )
            )
        );

        // Compress Pipeline Object
        $pipeline_object = new Pipeline($pipeline_input, $collection);
        $pipeline_object->compress();
        $this->assertEquals($pipeline_object->compressed, $pipeline_expected);

    }

    /**
     * Test Collection Helper
     */
    public function testCollectionHelper()
    {

        // Insert Data
        $documents = array(
            array(
                '_id' => 1,
                'gender' => 'male'
            ),
            array(
                '_id' => 2,
                'gender' => 'female'
            ),
            array(
                '_id' => 3,
                'gender' => 'female'
            )
        );
        $collection = $this->getTestCollection();
        $collection->batchInsert($documents);
        $pipeline = array(
            array(
                '$project' => array(
                    'gender' => 1
                )
            ),
            array(
                '$group' => array(
                    '_id' => '$gender',
                    'cnt' => array(
                        '$sum' => 1
                    )
                )
            )
        );

        // Check that the aggregation helper filters through correctly
        $response = $collection->aggregate($pipeline);
        $this->assertEquals($response['result'], array(
            array(
                '_id' => 'female',
                'cnt' => 2
            ),
            array(
                '_id' => 'male',
                'cnt' => 1
            )
        ));

    }

    /**
     * Test for array sets
     */
    public function testGroupArraySet()
    {

        // Insert Data
        $documents = array(
            array(
                '_id' => 1,
                'gender' => 'male'
            ),
            array(
                '_id' => 2,
                'gender' => 'female'
            ),
            array(
                '_id' => 3,
                'gender' => 'female'
            ),
            array(
                '_id' => 4,
                'gender' => 'male'
            )
        );
        $collection = $this->getTestCollection();
        $collection->batchInsert($documents);

        $pipeline = array(
            array(
                '$group' => array(
                    '_id' => '$gender',
                    'user_ids' => array(
                        '$addToSet' => '$_id'
                    )
                )
            )
        );
        $response = $collection->aggregate($pipeline);
        $this->assertEquals($response['result'], array(
            array(
                '_id' => 'female',
                'user_ids' => array(3, 2)
            ),
            array(
                '_id' => 'male',
                'user_ids' => array(4, 1)
            )
        ));

    }

}
