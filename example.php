<?php

include __DIR__.'/vendor/autoload.php';

$mongo = new MongoMinify\Client(array(
	'host' => '127.0.0.1',
	'port' => 37017,
	'db' => 'stats'
));
$mongo->debug = true;
$collection = $mongo->selectCollection('example');
$collection->setSchema(array(
	'long_key_name' => array(
		'type' => 'string',
		'short' => 'a'
	),
	'long_key_name_2' => array(
		'type'  => 'int',
		'short' => 'b'
	)
));

// Save new document
$document = array(
	'long_key_name' => 'test data',
	'long_key_name_2' => 13,
	'keep_key' => rand(100000, 999999)
);
print_r($document);
$save = $collection->save($document);
print_r($document);