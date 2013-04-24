<?php

class AggregationTest extends MongoMinifyTest {

    /**
     * Unit Test The Pipeline
     */
    public function testPipelineBuilder()
    {

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
        $pipeline_object = new MongoMinify\Pipeline($pipeline_input, $collection);
        $pipeline_object->compress();
        $this->assertEquals($pipeline_object->compressed, $pipeline_expected);


    }


}
