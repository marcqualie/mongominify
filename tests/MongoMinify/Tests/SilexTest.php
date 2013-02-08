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
		$app->register(new MongoMinify\SilexServiceProvider(), array(
			'mongo.uri' => $this->mongo_uri,
			'mongo.options' => $this->mongo_options,
			'mongominify.schema_dir' => __DIR__ . '/../../Schema'
		));

		// Insert new document
		$drop = $app['mongo']->mongominify->test->drop();
		$data = array(
			'user_id' => 1,
			'email' => 'test@example.com'
		);
		$app['mongo']->mongominify->test->insert($data);
		$document = $app['mongo']->mongominify->test->native->findOne(array('u' => 1));
		$this->assertEquals($document['e'], 'test@example.com');

	}

}