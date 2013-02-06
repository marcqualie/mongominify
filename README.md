## MongoMinify

A drop-in library for applying a simple schema to MongoDB documents and transparently compressing data on the fly.
Entirely namespaced, PSR-0 compliant and works with both PHP 5.3 and 5.4.


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


## Feedback / Contribution

Any feedback, pull requests or documentation would be greatly apreciated. Use the issue system if you find any problems or have an idea for a cool feature we don't support yet.
