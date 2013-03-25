<?php

namespace MongoMinify\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

class Db extends \MongoMinify\Db
{

    private $app;


    /**
     * Override default constructor to pass in app reference
     */
    public function __construct($db_name, $client, $app)
    {
        parent::__construct($db_name, $client);
        $this->app = $app;
    }


    /**
     * Change database helper
     */
    public function switchDb($db_name)
    {
        $self = $this;
        $app = $this->app;
        $this->app['mongo'] = $this->app->share(
            function () use ($db_name, $self, $app) {
                return new Db($db_name, $self->client, $app);
            }
        );
        return $this->app['mongo'];
    }

    /**
     * Select Temporary Database
     */
    public function selectDb($db_name)
    {
        return $this->client->selectDb($db_name);
    }

}
