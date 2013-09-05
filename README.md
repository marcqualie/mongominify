## MongoMinify (1.1-dev)

[![Build Status](https://travis-ci.org/marcqualie/mongominify.png?branch=master)](https://travis-ci.org/marcqualie/mongominify)
[![Total Downloads](https://poser.pugx.org/marcqualie/mongominify/d/total.png)](https://packagist.org/packages/marcqualie/mongominify)
[![Latest Stable Version](https://poser.pugx.org/marcqualie/mongominify/version.png)](https://packagist.org/packages/marcqualie/mongominify)
[![Dependency Status](https://www.versioneye.com/user/projects/520f85d5632bac1d74000287/badge.png)](https://www.versioneye.com/user/projects/520f85d5632bac1d74000287)
[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/marcqualie/mongominify/trend.png)](https://bitdeli.com/free "Bitdeli Badge")
[![Say Tahanks](https://s3.amazonaws.com/github-thank-you-button/thank-you-button.png)](http://twitter.com/home/?status=Thanks @marcqualie for making Mongo+Minify: https%3A%2F%2Fgithub.com%2Fmarcqualie%2Fmongominify)

MongoMinify is a drop-in library which acts as a transparent filter to MongoDB documents and compresses/decompresses data on the fly.
PSR-2 compliant and works with PHP 5.3+.


### Getting started

You should check out the [Getting Started](https://github.com/marcqualie/mongominify/wiki/Getting-Started) page on the Wiki to get up and running


### Quick Instalation

The best way to install this library is via composer.

    {
        "require": {
            "marcqualie/mongominify": "dev-master"
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


## Aggregation

The aggregation framework is very complex with a lot of use cases. I've tried to tackle it as best I can and it works for every use case I've come across. If you find a bug with minification while using the aggregtation framework, please create report it using [Github Issues](http://github.com/marcqualie/mongominify/issues).


## Feedback / Contributing

Feedback and pull requests on Github are always welcome and encouraged.
