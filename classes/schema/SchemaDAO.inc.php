<?php

/**
 * @file SchemaDAO.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package schema
 * @class SchemaDAO
 *
 * Class for Schema DAO.
 * Operations for retrieving and modifying Schema objects.
 *
 * $Id$
 */

import ('classes.schema.Schema');

class SchemaDAO extends DAO {
	/** @var $cachingEnabled boolean */
	var $cachingEnabled;

	/** @var $schemasById array Cached schemas by ID, when caching enabled */
	var $schemasById;

	/** @var $schemasByPluginName array Cached schemas by name, when caching enabled */
	var $schemasByPluginName;

	/**
	 * Constructor.
	 */
	function SchemaDAO() {
		parent::DAO();
		$this->enableCaching();
	}

	/**
	 * Enable in-memory caching of schemas by name and ID.
	 */
	function enableCaching() {
		$this->schemasById = array();
		$this->schemasByPluginName = array();
		$this->cachingEnabled = true;
	}

	/**
	 * Disable in-memory caching of schemas by name and ID.
	 */
	function disableCaching() {
		$this->cachingEnabled = false;
		unset($this->schemasById);
		unset($this->schemasByPluginName);
	}

	/**
	 * Retrieve a schema by ID.
	 * @param $schemaId int
	 * @return Schema
	 */
	function &getSchema($schemaId) {
		if ($this->cachingEnabled && isset($this->schemasById[$schemaId])) return $this->schemasById[$schemaId];

		$result =& $this->retrieve(
			'SELECT * FROM schema_plugins WHERE schema_plugin_id = ?', $schemaId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnSchemaFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);

		if ($returner != null && $this->cachingEnabled) {
			$this->schemasById[$returner->getSchemaId()] =& $returner;
			$this->schemasByPluginName[$returner->getPluginName()] =& $returner;
		}

		return $returner;
	}

	/**
	 * Retrieve a schema by plugin name.
	 * @param $schemaPluginName string
	 * @return Schema
	 */
	function &getSchemaByPluginName($schemaPluginName) {
		if ($this->cachingEnabled && isset($this->schemasByPluginName[$schemaPluginName])) return $this->schemasByPluginName[$schemaPluginName];

		$result =& $this->retrieve('SELECT * FROM schema_plugins WHERE schema_plugin = ?', $schemaPluginName);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnSchemaFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);

		if ($returner != null && $this->cachingEnabled) {
			$this->schemasById[$returner->getSchemaId()] =& $returner;
			$this->schemasByPluginName[$returner->getPluginName()] =& $returner;
		}

		return $returner;
	}

	/**
	 * Internal function to return a Schema object from a row.
	 * @param $row array
	 * @return Schema
	 */
	function &_returnSchemaFromRow(&$row) {
		$schema = new Schema();
		$schema->setSchemaId($row['schema_plugin_id']);
		$schema->setPluginName($row['schema_plugin']);

		HookRegistry::call('SchemaDAO::_returnSchemaFromRow', array(&$schema, &$row));

		return $schema;
	}

	/**
	 * Insert a new schema.
	 * @param $schema Schema
	 */	
	function insertSchema(&$schema) {
		$this->update(
			'INSERT INTO schema_plugins
				(schema_plugin)
				VALUES
				(?)',
			array(
				$schema->getPluginName()
			)
		);

		$schema->setSchemaId($this->getInsertSchemaId());
		return $schema->getSchemaId();
	}

	/**
	 * Insert a new schema alias.
	 * @param $schemaId int Schema ID
	 * @param $alias string Alias
	 */	
	function insertSchemaAlias($schemaId, $alias) {
		$this->update(
			'INSERT INTO schema_aliases
				(schema_plugin_id, alias)
				VALUES
				(?, ?)',
			array(
				(int) $schemaId,
				$alias
			)
		);
	}

	/**
	 * Update an existing schema.
	 * @param $schema Schema
	 */
	function updateSchema(&$schema) {
		return $this->update(
			'UPDATE schemas
				SET
					schema_plugin = ?
				WHERE schema_plugin_id = ?',
			array(
				$schema->getPluginName(),
				$schema->getSchemaId()
			)
		);
	}

	/**
	 * Delete a schema, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $schema Schema
	 */
	function deleteSchema(&$schema) {
		$this->deleteSchemaById($schema->getSchemaId());
	}

	/**
	 * Delete a schema by ID, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $schemaId int
	 */
	function deleteSchemaById($schemaId) {
		if (isset($this->schemasById[$schemaId])) {
			unset($this->schemasById[$schemaId]);
		}
		foreach ($this->schemasByPluginName as $schemaPlugin => $schema) {
			if ($schema->getSchemaId() == $schemaId) {
				unset($this->schemasByPluginName[$schemaPluginName]);
			}
		}
		return $this->update(
			'DELETE FROM schema_plugins WHERE schema_plugin_id = ?', $schemaId
		);
	}

	/**
	 * Retrieve all schemas.
	 * @return DAOResultFactory containing matching schemas
	 */
	function &getSchemas($rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT * FROM schema_plugins',
			false, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnSchemaFromRow');
		return $returner;
	}

	/**
	 * Retrieve all schema aliases.
	 * @param $schemaPluginName string optional
	 * @return array alias => schema plugin name
	 */
	function &getSchemaAliases($schemaPluginName = null) {
		$params = array();
		if ($schemaPluginName !== null) $params[] = $schemaPluginName;

		$result =& $this->retrieve(
			'SELECT	sa.alias, s.schema_plugin
			FROM	schema_aliases sa,
				schema_plugins s
			WHERE	s.schema_plugin_id = sa.schema_plugin_id' .
			($schemaPluginName !== null ? ' AND s.schema_plugin = ?' : ''),
			$params
		);

		$returner = array();
		while (!$result->EOF) {
			$row =& $result->getRowAssoc(false);
			$returner[$row['alias']] = $row['schema_plugin'];
			$result->MoveNext();
		}
		$result->Close();

		return $returner;
	}

	/**
	 * Get a schema, creating it if necessary.
	 * @param $schemaPluginName string
	 * @return object Schema
	 */
	function &buildSchema($schemaPluginName) {
		$schema =& $this->getSchemaByPluginName($schemaPluginName);
		if (!$schema) {
			$schema = new Schema();
			$schema->setPluginName($schemaPluginName);
			$this->insertSchema($schema);
		}
		return $schema;
	}

	/**
	 * Get the ID of the last inserted schema.
	 * @return int
	 */
	function getInsertSchemaId() {
		return $this->getInsertId('schema_plugins', 'schema_plugin_id');
	}

}

?>
