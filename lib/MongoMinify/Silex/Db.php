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
	public function selectDb($db_name)
	{
		$self = $this;
		$this->app['mongo'] = $this->app->share(function () use ($db_name, $self) {
			return $self->client->selectDb($db_name);
		});
	}

}