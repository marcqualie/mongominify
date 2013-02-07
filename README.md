## MongoMinify

[![Build Status](https://travis-ci.org/marcqualie/mongominify.png?branch=master)](https://travis-ci.org/marcqualie/mongominify)

A drop-in library for applying a simple schema to MongoDB documents and transparently compressing data on the fly.
Entirely namespaced, PSR-0 compliant and works with both PHP 5.3 and 5.4.

### Why MongoMinify?

As great as MongoDB is at so many things, it has a downside compared to other data stores that it stores it's keys wih every document.
These keys quickly add up and sometimes double or evem triple the amount of storage required.
Myself and many other developers got around this by adding single letter key names, but this is hard to manage with large projects and documents.
MongoMinify gets around this problem by transparently converting documents as they are transfered between the client and the database leaving readable code with compressed storage.

### Install

Via composer

```json
{
	"require": {
		"marcqualie/mongominify": "dev-master"
	}
}
```


### Usage

I designed the usage pattern to match the native Mongo PHP driver as close as possible so it's a drop in replacement. Nearly all actions are transparent to the application and based around the schema you specify.

```php
// Connect in the same way you would with the native driver
$mongo = new MongoMinify\Client('mongodb://localhost:27017');
$collection = $mongo->db_name->collection_name;
```


#### Apply Schema

The schema is now applied internally based on the collection name. Every action you take on a collection has the schema applied correctly so you see no differences between this library and native library.

```php
$mongo->schema_dir = 'app/schema/mongominify';
```


#### Inserting documents

When inserting/saving documents they are validated against the schema and translated internally

```php
$document = [
	'long_key_name' => 'test data',
	'long_key_name_2' => 13
];
$save = $collection->save($document);
```


#### Querying Schema

You can query data just as you normaly would, the conversion is internal and transparent

```php
$document = $collection->findOne(['long_key_name' => 'test data'], ['_id' => 0, 'long_key_name_2' => 1]);
print_r($document); // ['long_key_name_2' => 13]
```


## Feedback / Contributing

Any feedback, pull requests or documentation would be greatly apreciated.
