<?php

namespace MongoMinify;

class Collection {
	
	public $name = '';
	public $namespace = '';
	public $db;
	public $native;


	public function __construct($name, $db)
	{
		$this->name = $name;
		$this->db = $db;
		$this->client = $db->client;
		$this->namespace = $db->name . '.' . $this->name;
		$this->native = $db->native->selectCollection($this->name);

		// Apply schema to collection
		if (isset($this->client->schema_dir))
		{
			$schema_file = $this->client->schema_dir . '/' . $this->namespace . '.php';
			if (file_exists($schema_file))
			{
				$schema = include $schema_file;
				$this->setSchema($schema);
			}
		}
	}


	/**
	 * Compression
	 * This is useful for testing what a document will look like during development or debugging
	 */
	public function compress($data)
	{
		$document = new Document($data, $this);
		$document->compress();
		return $document->data;
	}


	/**
	 * Save Document
	 * @param  [type] $data    [description]
	 * @param  array  $options [description]
	 * @return [type]          [description]
	 */
	public function save(&$data, Array $options = array())
	{
		$document = new Document($data, $this);
		$document->compress();
		$save = $this->native->save($document->data, $options);
		$data = $document->data;
		return $save;
	}


	/**
	 * Insert new document
	 * @param  [type] $document [description]
	 * @param  array  $options  [description]
	 * @return [type]           [description]
	 */
	public function insert(&$data, Array $options = array())
	{
		$document = new Document($data, $this);
		$document->compress();
		$insert = $this->native->insert($document->data, $options);
		$data = $document->data;
		return $insert;
	}


	/**
	 * Find document
	 * @param  array  $document [description]
	 * @return [type]           [description]
	 */
	public function find(Array $document = array())
	{
		return array();
	}


	/**
	 * Apply internal schema
	 * @param [type] $schema [description]
	 */
	public function setSchema(Array $schema)
	{
		$this->schema = array();
		$this->schema_raw = $schema;
		$this->setSchemaArray($schema);
//		$this->client->schema[$this->client->db->name . '.' . $this->name] = $schema;
	}
	private function setSchemaArray(Array $array, $namespace = null)
	{
		foreach ($array as $key => $value)
		{
			$subkey = ($namespace ? $namespace . '.' : '') . $key;
			if (isset($value['subset']))
			{
				$this->setSchemaArray($value['subset'], $subkey);
				unset($value['subset']);
			}
			$this->schema[$subkey] = $value;
		}
	}


	/**
	 * Batch Insert
	 */
	public function batchInsert(Array &$documents, Array $options = array())
	{
		$documents_compressed = array();
		foreach ($documents as $data)
		{
			$document = new Document($data, $this);
			$document->compress();
			$documents_compressed[] = $document->data;
		}
		$this->native->batchInsert($documents_compressed);
	}


	/**
	 * Drop
	 */
	public function drop()
	{
		$this->native->drop();
	}


	/**
	 * Ensure Index
	 */
	public function ensureIndex(Array $keys, Array $options = array())
	{
		$this->native->ensureIndex($keys, $options);
	}

}