## MongoMinify

A small library for applying a schema to MongoDB documents and transparently compressing the contents on the fly

### Install

Via composer

	{
		"require": {
			"marcqualie/mongominify": "0.1.*"
		}
	}

### Usage

Most of the interaction with MongoMinify will not differ to how you use the native Mongo driver. Nearly all actions are transparent to the application and based around the schema you specify.


#### Apply Schema

	$mongo = new MongoMinify\MongoMinify();
	$collection = $mongo->selectCollection('collection_name');
	$collection->setSchema([
		'long_key_name' => [
			'type' => 'string',
			'short' => 'a'
		],
		'long_key_name_2' => [
			'type'  => 'int',
			'short' => 'b'
		]
	]);

#### Inserting documents

When inserting/saving documents they are validated against the schema and translated internally
	
	$document = [
		'long_key_name' => 'test data',
		'long_key_name_2' => 13
	];
	$save = $collection->save($document);
	print_r($save->compressed); // ['_id' => MongoId('xxx'), 'a' => 'test data', 'b' => 13]
	print_r($document); // ['_id' => MongoId('xxx'), 'long_key_name' => 'test data', 'long_key_name_2' => 13];

#### Querying Schema

You can query data just as you normaly would, the conversion is internal and transparent

	$document = $collection->findOne(['long_key_name' => 'test data'], ['_id' => 0, 'long_key_name_2' => 1]);
	print_r($document); // ['long_key_name_2' => 13]

