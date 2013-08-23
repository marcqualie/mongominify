<?php

namespace MongoMinify\Provider\Silex;

use MongoMinify\Db as Base;

class Db extends Base
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

        return $this->app['mongo'] = $this->app->share(
            function () use ($db_name, $self) {
                return new Db($db_name, $self->client, $self->app);
            }
        );
    }

    /**
     * Select Temporary Database
     */
    public function selectDb($db_name)
    {
        return $this->client->selectDb($db_name);
    }
}
