<?php

namespace MongoMinify;

class Document {
	
	public $state = 'normal';
	public $data = array();
	public $compressed = array();
	protected $collection = array();

	public function __construct(array $data = array(), $collection = null)
	{
		$this->collection = $collection;
		$this->data = $data;
	}

	/**
	 * Save
	 */
	public function save(array $options = array())
	{
		$this->compress();
		$this->collection->native->save($this->compressed, $options);
		$this->data['_id'] = $this->compressed['_id'];
	}
	public function insert(array $options = array())
	{
		$this->compress();
		$this->collection->native->insert($this->compressed, $options);
		$this->data['_id'] = $this->compressed['_id'];
	}

	/**
	 * Data Compression
	 */
	public function compress()
	{
		if ( ! $this->collection->schema)
		{
			$this->compressed =& $this->data;
			return;
		}
		if ($this->state !== 'compressed' && $this->collection)
		{
			$this->compressed = $this->applyCompression($this->data);
			$this->state = 'compressed';
		}
	}
	private function applyCompression($document, $parent = null)
	{

		// If is an array, loop through and apply rules
		if (isset($document[0]) && is_array($document[0]))
		{
			foreach ($document as $key => $value)
			{
				$document[$key] = $this->applyCompression($value, $parent);
			}
			return $document;
		}

		// Normalize doc delimited keys
		foreach ($document as $key => $value)
		{
			if (strpos($key, '.') !== false)
			{
				list ($parent_key, $child_key) = explode('.', $key, 2);
				if ( ! isset($document[$parent_key]))
				{
					$document[$parent_key] = array();
				}
				$document[$parent_key][$child_key] = $value;
				unset($document[$key]);
			}
		}

		// Documents are applied as key/value
		$doc = array();
		foreach ($document as $key => $value)
		{
			$namespace = ($parent ? $parent . '.' : '') . $key;
			if (is_array($value))
			{
				$value = $this->applyCompression($value, $namespace);
			}
			elseif (isset($this->collection->schema[$namespace]['values']))
			{
				$values =  $this->collection->schema[$namespace]['values'];
				$values = array_flip($values);
				if (isset($values[$value]))
				{
					$value = $values[$value];
				}
			}
			$short = isset($this->collection->schema_index[$namespace]) ? $this->collection->schema_index[$namespace] : $key;
			$doc[$short] = $value;
		}
		return $doc;
	}


	/**
	 * Data Decompression
	 */
	public function decompress()
	{
		if ($this->state !== 'normal' && $this->collection)
		{
			$this->data = $this->applyDecompression($this->data);
			$this->state = 'normal';
		}
	}
	private function applyDecompression($document, $parent = null)
	{

		// If is an array, loop through and apply rules
		if (isset($document[0]))
		{
			foreach ($document as $key => $value)
			{
				$document[$key] = $this->applyDecompression($value, $parent);
			}
			return $document;
		}

		// Standard document traversal
		foreach ($document as $key => $value)
		{
			$namespace = ($parent ? $parent . '.' : '') . $key;
			if (isset($this->collection->schema_reverse_index[$namespace]))
			{
				if (is_array($value))
				{
					$value =$this->applyDecompression($value, $key);
				}
				$full_namespace = $this->collection->schema_reverse_index[$namespace];
				$explode = explode('.', $full_namespace);
				$full_key = end($explode);
				if (isset($this->collection->schema[$full_namespace]['values'][$value]))
				{
					$value = $this->collection->schema[$full_namespace]['values'][$value];
				}
				$document[$full_key] = $value;
				unset($document[$key]);
			}
		}
		return $document;
	}


	/**
	 * As dot syntax for index ensuring
	 * TODO: This is a quick hack to get indexes working with embedded document syntax
	 */
	public function asDotSyntax()
	{
		$dotSyntax = array();
		foreach ($this->compressed as $key => $value)
		{
			if (is_array($value))
			{
				foreach ($value as $subkey => $subval)
				{
					$dotSyntax[$key . '.' . $subkey] = $subval;
				}
			}
			else
			{
				$dotSyntax[$key] = $value;
			}
		}
		return $dotSyntax;
	}

}