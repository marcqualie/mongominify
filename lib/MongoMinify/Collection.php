<?php

namespace MongoMinify;

class Collection {
	
	private $name = '';
	public $namespace = '';
	public $db;
	public $native;

	public $schema = array();
	public $schema_raw = array();
	public $schema_index = array();
	public $schema_reverse_index = array();


	/**
	 * Initialize
	 */
	public function __construct($name, $db)
	{
		$this->name = $name;
		$this->db = $db;
		$this->client = $db->client;
		$this->namespace = (String) $db . '.' . $this->name;
		$this->native = $db->native->selectCollection($this->name);

		// Apply schema to collection
		$this->setSchemaByName($this->namespace);
	}


	/**
	 * Get name of collection
	 */
	public function getName()
	{
		return $this->name;
	}

	
	/**
	 * Save Document
	 * @param  [type] $data    [description]
	 * @param  array  $options [description]
	 * @return [type]          [description]
	 */
	public function save(array &$data, array $options = array())
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
	public function insert(&$data, array $options = array())
	{
		$document = new Document($data, $this);
		$insert = $document->insert($options);
		$data = $document->data;
		return $insert;
	}


	/**
	 * Update Document
	 */
	public function update(array $data = array(), array $new_object, array $options = array())
	{
		$document = new Document($data, $this);
		$update = $document->update($new_object, $options);
		return $update;
	}


	/**
	 * Remove Documents
	 */
	public function remove($criteria, array $options = array())
	{
		$query = new Query($criteria, $this);
		$query->compress();
		return $this->native->remove($query->compressed, $options);
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
		$query_object = new Query($query, $this);
		$query_object->compress();
		$cursor = new Cursor($this, $query_object->compressed, $fields);
		return $cursor;
	}


	/**
	 * Count Helper
	 */
	public function count(array $query = array(), $limit = null, $skip = null)
	{
		$cursor = $this->find($query);
		if ($skip !== null)
		{
			$cursor->skip($skip);
		}
		if ($limit !== null)
		{
			$cursor->limit($limit);
		}
		return $cursor->count();
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

		// Decode Schema File
		if ($this->client->schema_format === 'php')
		{
			$schema_file = $this->client->schema_dir . '/' . $schema_name . '.php';
			if (file_exists($schema_file))
			{
				$schema = include $schema_file;
				if (empty($schema))
				{
					throw new \Exception('Possible JSON parse in ' . $schema_file);
				}
			}
		}

		elseif ($this->client->schema_format === 'json')
		{
			$schema_file = $this->client->schema_dir . '/' . $schema_name . '.json';
			if (file_exists($schema_file))
			{
				$json_string = file_get_contents($schema_file);
				$schema = json_decode($json_string, true);
				if (empty($schema))
				{
					throw new \Exception('Possible PHP syntax error in ' . $schema_file);
				}
			}
		}

		else
		{
			throw new \Exception('Unknown schema format: ' . $this->client->schema_format);
		}

		// Assign Schema
		if ( ! empty($schema))
		{
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
				$short = $this->getShort($namespace);
				$parent_short = $short ? $short . '.' : '';
				$this->schema_index[$subkey] = $value['short'];
				$this->schema_reverse_index[$parent_short . $value['short']] = $subkey;
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
		$this->native->batchInsert($documents_compressed, $options);
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
	 * Distinct
	 */
	public function distinct($key, array $query = array())
	{
		$key_short = isset($this->schema[$key]['short']) ? $this->schema[$key]['short'] : $key;
		$query_object = new Query($query, $this);
		$query_object->compress();
		$values_short = $this->native->distinct($key_short, $query_object->compressed);
		if (isset($this->schema[$key]['type']) && $this->schema[$key]['type'] === 'enum')
		{
			$values = array();
			foreach ($values_short as $val)
			{
				$values[] = isset($this->schema[$key]['values'][$val]) ? $this->schema[$key]['values'][$val] : $val;
			}
			return $values;
		}
		return $values_short;
	}


	/**
	 * Ensure Index
	 */
	public function ensureIndex(array $keys, array $options = array())
	{
		$query = new Query($keys, $this);
		$query->compress();
		$query->asDotSyntax();
		$this->native->ensureIndex($query->compressed, $options);
	}


	/**
	 * Get all indexes
	 */
	public function getIndexInfo()
	{
		return $this->native->getIndexInfo();
	}

}