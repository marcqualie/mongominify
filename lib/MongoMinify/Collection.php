<?php

namespace MongoMinify;

class Collection {
	
	public $name = '';
	public $mongo;
	public $instance;

	public function __construct($name, $client)
	{
		$this->name = $name;
		$this->client = $client;
		$this->native = $this->client->db->selectCollection($this->name);
	}

	public function save(&$data, Array $options = array())
	{
		$document = new Document($data, $this);
		$document->compress();
		$save = $this->native->save($document->data, $options);
		$data = $document->data;
		return $save;
	}

	public function find($document)
	{

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

}