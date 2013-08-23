<?php

namespace MongoMinify\Test;
use Silex\Application;
use MongoMinify\Provider\Silex\ServiceProvider;

class SilexTest extends TestCase
{
    /**
     * Text Silex Service Provider Integration
     * @return [type] [description]
     */
    public function testServiceProvider()
    {

        // Initialize Silex
        $app = new Application();
        $app->register(new ServiceProvider(), array(
            'mongo.server' => $this->mongo_server,
            'mongo.options' => $this->mongo_options,
            'mongominify.schema_dir' => dirname(__DIR__) . '/Schema',
            'mongominify.schema_format' => 'json'
        ));

        // Insert new document
        $drop = $app['mongo']->test->drop();
        $data = array(
            'user_id' => 1,
            'email' => 'test@example.com'
        );
        $app['mongo']->test->insert($data);
        $this->assertEquals((String) $app['mongo'], 'mongominify');
        $document = $app['mongo']->test->native->findOne(array('u' => 1));
        $this->assertEquals($document['e'], 'test@example.com');

        // Switch DB
        $app['mongo']->switchDb('test2');
        $this->assertEquals((String) $app['mongo'], 'test2');

        // Select DB
        $this->assertEquals((String) $app['mongo']->selectDb('test3'), 'test3');

    }

    /**
     * Test Default Settings
     */
    public function testServiceProviderDefaults()
    {
        // Initialize Silex
        $app = new Application();
        $app->register(new ServiceProvider());
        $app['mongo']->test->drop();
    }

    /**
     * Test Default Settings
     */
    public function testServiceProviderCustomDb()
    {
        // Initialize Silex
        $app = new Application();
        $app->register(new ServiceProvider(), array(
            'mongo.options' => array(
                'db' => 'mongominify'
            )
        ));
        $app['mongo']->test->drop();
    }

}
