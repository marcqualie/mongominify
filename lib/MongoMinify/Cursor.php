<?php

namespace MongoMinify;

class Cursor implements \Iterator {

	public $colleciton;
	public $native;

	public function __construct($collection, array $query = array(), array $fields = array())
	{
		$this->collection = $collection;
		$this->native = $collection->native->find($query, $fields);
	}
	
	/**
	 * Move around cursor
	 */
	public function rewind()
	{
		$this->native->rewind();
	}
	public function getNext()
	{
		$this->next();
		return $this->current();
	}
	public function next()
	{
		$this->native->next();
	}
	public function current()
	{
		$current = $this->native->current();
		$document = new Document($current, $this->collection);
		$document->state = 'compressed';
		$document->decompress();
		return $document->data;
	}


	/**
	 * Data retreival
	 */
	public function sort(array $fields = array())
	{
		$this->native->sort($fields);
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

}