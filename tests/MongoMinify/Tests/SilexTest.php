<?php

class SilexTest extends MongoMinifyTest {
	

	/**
	 * Text Silex Service Provider Integration
	 * @return [type] [description]
	 */
	public function testServiceProvider()
	{
		
		// Initialize Silex
		$app = new Silex\Application();
		$app->register(new MongoMinify\Silex\ServiceProvider(), array(
			'mongo.server' => $this->mongo_server,
			'mongo.options' => $this->mongo_options,
			'mongominify.schema_dir' => __DIR__ . '/../../Schema'
		));

		// Insert new document
		$drop = $app['mongo']->test->drop();
		$data = array(
			'user_id' => 1,
			'email' => 'test@example.com'
		);
		$app['mongo']->test->insert($data);
		$document = $app['mongo']->test->native->findOne(array('u' => 1));
		$this->assertEquals($document['e'], 'test@example.com');

	}

}