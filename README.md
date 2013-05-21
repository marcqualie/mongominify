## MongoMinify

[![Build Status](https://travis-ci.org/marcqualie/mongominify.png?branch=master)](https://travis-ci.org/marcqualie/mongominify)

A drop-in library which acts as a transparent filter to MongoDB documents and compresses/decompresses data on the fly.
Entirely namespaced, PSR-0 compliant and works with both PHP 5.3 and 5.4.


### Getting started

You should check out the [Getting Started](https://github.com/marcqualie/mongominify/wiki/Getting-Started) page on the Wiki to get up and running


### Why MongoMinify?

As great as MongoDB is at so many things, it has a downside compared to other data stores that it stores it's keys wih every document.
These keys quickly add up and sometimes double or even triple the amount of storage required.
Myself and many other developers got around this by adding single letter key names, but this is hard to manage with large projects and documents.
MongoMinify gets around this problem by transparently converting documents as they are transfered between the client and the database leaving readable code with compressed storage.


## Feedback / Contributing

Any feedback, pull requests or documentation would be greatly apreciated.
