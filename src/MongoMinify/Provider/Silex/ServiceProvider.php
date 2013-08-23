<?php

namespace MongoMinify\Provider\Silex;

use MongoMinify\Client;
use Silex\Application;
use Silex\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{

    /**
     * Register provider
     * @param  Application    $app Global Application instance
     * @return Db Silex Database Wrapper
     */
    public function register(Application $app)
    {
        $app['mongo'] = $app->share(
            function () use ($app) {

                // Assert MongoDB Server
                if (empty($app['mongo.server'])) {
                    $app['mongo.server'] = 'mongodb://127.0.0.1:27017';
                }
                if (! isset($app['mongo.options']) || ! is_array($app['mongo.options'])) {
                    $app['mongo.options'] = array();
                }

                // Connect to Database
                $client = new Client($app['mongo.server'], $app['mongo.options']);

                // Apply Schema Options
                if (! empty($app['mongominify.schema_dir'])) {
                    $client->schema_dir = $app['mongominify.schema_dir'];
                }
                if (! empty($app['mongominify.schema_format'])) {
                    $client->schema_format = $app['mongominify.schema_format'];
                }

                // Figure out DB Name
                if (! empty($app['mongo.options']['db'])) {
                    $db_name = $app['mongo.options']['db'];
                } else {
                    $uri = parse_url($app['mongo.server']);
                    $db_name = isset($uri['path']) ? substr($uri['path'], 1) : 'test';
                }

                // Return Database Instance
                return new Db($db_name, $client, $app);

            }
        );
    }

    /**
     * Service Boot
     * @codeCoverageIgnore
     */
    public function boot(Application $app)
    {

    }
}
