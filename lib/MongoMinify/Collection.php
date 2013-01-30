<?php

namespace MongoMinify;

class Collection {
	
	public $name = '';
	public $namespace = '';
	public $db;
	public $native;

	public $schema = array();
	public $schema_raw = array();
	public $schema_index = array();
	public $schema_reverse_index = array();


	public function __construct($name, $db)
	{
		$this->name = $name;
		$this->db = $db;
		$this->client = $db->client;
		$this->namespace = $db->name . '.' . $this->name;
		$this->native = $db->native->selectCollection($this->name);

		// Apply schema to collection
		$this->setSchemaByName($this->namespace);
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
		$save = $document->save($options);
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
		$insert = $document->insert($options);
		$data = $document->data;
		return $insert;
	}


	/**
	 * Find document
	 * @param  array  $document [description]
	 * @return [type]           [description]
	 */
	public function findOne(array $query = array(), array $fields = array())
	{
		$cursor = $this->find($query, $fields)->limit(1);
		return $cursor->getNext();
	}
	public function find(array $query = array(), array $fields = array())
	{
		$document = new Document($query, $this);
		$document->compress();
		$cursor = new Cursor($this, $document->compressed, $fields);
		return $cursor;
	}


	/**
	 * Apply internal schema
	 * @param [type] $schema [description]
	 */
	public function setSchemaByName($schema_name = null)
	{
		if ( ! $schema_name)
		{
			$schema_name = $this->namespace;
		}
		if (strpos($schema_name, '.') === false)
		{
			$schema_name = $this->db->name . '.' . $schema_name;
		}
		$schema_file = $this->client->schema_dir . '/' . $schema_name . '.php';
		if (file_exists($schema_file))
		{
			$schema = include $schema_file;
			$this->setSchema($schema);
		}
	}
	public function setSchema(array $schema = array())
	{
		$this->schema = array();
		$this->schema_raw = $schema;
		$this->schema_index = array();
		$this->schema_reverse_index = array();
		$this->setSchemaArray($schema);
	}
	private function setSchemaArray(array $array, $namespace = null)
	{
		foreach ($array as $key => $value)
		{
			$subkey = ($namespace ? $namespace . '.' : '') . $key;
			$this->schema[$subkey] = $value;
			if (isset($value['subset']))
			{
				$this->setSchemaArray($value['subset'], $subkey);
				unset($value['subset']);
			}
			if (isset($value['short']))
			{
				$parent_short = $this->getShort($namespace) ? $this->getShort($namespace) . '.' : '';
				$this->schema_index[$subkey] = $value['short'];
				$this->schema_reverse_index[$parent_short . $value['short']] = $key;
			}
		}
	}


	/**
	 * Get short definitions based on full key
	 * @param  [type] $full [description]
	 * @return [type]       [description]
	 */
	public function getShort($full)
	{
		if (isset($this->schema[$full]))
		{
			return $this->schema[$full]['short'];
		}
		return $full;
	}


	/**
	 * Batch Insert
	 */
	public function batchInsert(array &$documents, array $options = array())
	{
		$documents_compressed = array();
		foreach ($documents as $data)
		{
			$document = new Document($data, $this);
			$document->compress();
			$documents_compressed[] = $document->compressed;
		}
		$this->native->batchInsert($documents_compressed);
		foreach ($documents_compressed as $key => $document)
		{
			$documents[$key]['_id'] = $document['_id'];
		}
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
	public function ensureIndex(array $keys, array $options = array())
	{
		$document = new Document($keys, $this);
		$document->compress();
		$flat = $document->asDotSyntax();
		$this->native->ensureIndex($flat, $options);
	}


	/**
	 * Get all indexes
	 */
	public function getIndexInfo()
	{
		return $this->native->getIndexInfo();
	}

}