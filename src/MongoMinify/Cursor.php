<?php

namespace MongoMinify;

class Cursor implements \Iterator
{

    public static $timeout = 20000;

    public $collection;
    public $native;

    public $native_query = array();

    public function __construct(Collection $collection, array $query = array(), array $fields = array())
    {
        $this->collection = $collection;
        $this->native_query = $query;
        $this->native = $collection->native->find($query, $fields);
    }

    /**
     * Await data
     */
    public function awaitData($wait = true)
    {
        return $this->natuve->awaitData($wait);
    }

    /**
     * Batch size
     */
    public function batchSize($batchSize)
    {
        return $this->native->batchSize($batchSize);
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
    public function reset()
    {
        return $this->native->reset();
    }

    /**
     * Counting results
     */
    public function count($foundOnly = false)
    {
        return $this->native->count($foundOnly);
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
        $this->native->timeout($ms);
        return $this;
    }

    /**
     * Check if cursor is dead
     */
    public function dead()
    {
        return $this->native->dead();
    }

    /**
     * Tail a cursor
     */
    public function tailable($tail = true)
    {
        return $this->native->tailable($tail);
    }

    /**
     * Array helper for inline cursors
     */
    public function asArray()
    {
        return iterator_to_array($this, false);
    }
}
