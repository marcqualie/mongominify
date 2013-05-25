<?php

namespace MongoMinify;

class Cursor implements \Iterator
{

    static $timeout = 20000;

    public $collection;
    public $native;

    public $native_query = array();

    public function __construct($collection, array $query = array(), array $fields = array())
    {
        $this->collection = $collection;
        $this->native_query = $query;
        $this->native = $collection->native->find($query, $fields);
        $native = $this->native;
    }


    /**
     * Get Cursor Info
     */
    public function info()
    {
        return $this->native->info();
    }


    /**
     * Move around cursor
     */
    public function rewind()
    {
        $this->native->rewind();
        return $this;
    }
    public function getNext()
    {
        $this->next();
        return $this->current();
    }
    public function next()
    {
        $this->native->next();
        return $this;
    }
    public function current()
    {
        $current = $this->native->current();
        if (!$current) {
            return null;
        }
        $document = new Document($current, $this->collection);
        $document->state = 'compressed';
        $document->decompress();
        return $document->data;
    }


    /**
     * Counting results
     */
    public function count()
    {
        return $this->native->count();
    }


    /**
     * Data retreival
     */
    public function sort(array $fields = array())
    {
        $fields_query = new Query($fields, $this->collection);
        $fields_query->compress();
        $this->native->sort($fields_query->compressed);
        return $this;
    }
    public function skip($num)
    {
        $this->native->skip($num);
        return $this;
    }
    public function limit($num)
    {
        $this->native->limit($num);
        return $this;
    }


    /**
     * Native abtracts
     */
    public function key()
    {
        return $this->native->key();
    }
    public function valid()
    {
        return $this->native->valid();
    }


    /**
     * Set Timeout
     */
    public function timeout($ms)
    {
        $native = $this->native;
        $native->timeout($ms);
        return $this;
    }

}

// Bind native timeouts
// TODO: Temporary fix, need a better place to put this code
Cursor::$timeout = \MongoCursor::$timeout;
\MongoCursor::$timeout =& Cursor::$timeout;
