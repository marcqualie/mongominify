<?php

namespace MongoMinify;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{

	/**
	 * Register provider
	 * @param  Application $app Global Application instance
	 * @return MongoMinify\Client MongoMinify client instance
	 */
	public function register(Application $app)
	{
		$app['mongo'] = $app->share(function ($name) use ($app) {

			// Connect to Database
			$client = new Client($app['mongo.uri'], $app['mongo.options']);
			if ( ! empty($app['mongo.schema_dir']))
			{
				$client->schema_dir = $app['mongo.schema_dir'];
			}

			return $client;

		});
	}

	/**
	 * Service Boot
	 */
	public function boot(Application $app) {}

}