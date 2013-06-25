## MongoMinify

[![Build Status](https://travis-ci.org/marcqualie/mongominify.png?branch=master)](https://travis-ci.org/marcqualie/mongominify)
[![Total Downloads](https://poser.pugx.org/marcqualie/mongominify/d/total.png)](https://packagist.org/packages/marcqualie/mongominify)
[![Latest Stable Version](https://poser.pugx.org/marcqualie/mongominify/version.png)](https://packagist.org/packages/marcqualie/mongominify)

MongoMinify is a drop-in library which acts as a transparent filter to MongoDB documents and compresses/decompresses data on the fly.
PSR-1 compliant and works with PHP 5.3+.


### Getting started

You should check out the [Getting Started](https://github.com/marcqualie/mongominify/wiki/Getting-Started) page on the Wiki to get up and running


### Quick Instalation

The best way to install this library is via composer.

    {
        "require": {
            "marcqualie/mongominify": "~1.0"
        }
    }


### Why MongoMinify?

As great as MongoDB is at so many things, it has a downside compared to other data stores that it stores it's keys wih every document.
These keys quickly add up and sometimes double or even triple the amount of storage required.
Myself and many other developers got around this by adding single letter key names, but this is hard to manage with large projects and documents.
MongoMinify gets around this problem by transparently converting documents as they are transfered between the client and the database leaving readable code with compressed storage.


## Requirements

- PHP 5.3+
- MongoDB PHP Driver 1.3+


## Feedback / Contributing

Feedback and pull requests on Github are always welcome.
