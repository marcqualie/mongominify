<?php

namespace MongoMinify;

class Document {
	
	public $state = 'standard';
	public $data = array();
	public $collection = array();

	public function __construct(Array $data = array(), $collection)
	{
		$this->data = $data;
		$this->collection = $collection;
	}

	/**
	 * Data Compression
	 */
	public function compress()
	{
		if ($this->state !== 'compressed')
		{
			$this->data = $this->applyCompression($this->data);
			$this->state = 'compressed';
		}
	}
	private function applyCompression($document, $parent = null)
	{

		// If is an array, loop through and apply rules
		if (isset($document[0]))
		{
			foreach ($document as $key => $value)
			{
				$document[$key] = $this->compress($value, $parent);
			}
			return $document;
		}

		// Documents are applied as key/value
		$doc = array();
		foreach ($document as $key => $value)
		{
			$namespace = ($parent ? $parent . '.' : ''). $key;
			if (is_array($value))
			{
				$value = $this->compress($value, $namespace);
			}
			$short = isset($this->collection->schema[$namespace]['short']) ? $this->collection->schema[$namespace]['short'] : $key;
			if ($this->collection->client->debug)
			{
				echo $namespace .'.[ ' . "\033[0;31m" . $key . "\033[0m" . ' > ' . "\033[0;32m" . $short . "\033[0m ]" . PHP_EOL;
			}
			$doc[$short] = $value;
		}
		return $doc;
	}

}