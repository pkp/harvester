<?php

/**
 * @file FieldDAO.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @in_group field
 * @class FieldDAO
 *
 * @brief Class for Field DAO.
 * Operations for retrieving and modifying Field objects.
 *
 */

// $Id$


import ('classes.field.Field');

class FieldDAO extends DAO {
	/** @var $cachingEnabled boolean */
	var $cachingEnabled;

	/** @var $fieldsById array Cached fields by ID, when caching enabled */
	var $fieldsById;

	/** @var $fieldsBySchemaName array Cached fields by schema name, when caching enabled */
	var $fieldsBySchemaName;

	/**
	 * Constructor.
	 */
	function FieldDAO() {
		parent::DAO();
		$this->enableCaching();
	}

	/**
	 * Enable in-memory caching of fields by name and ID.
	 */
	function enableCaching() {
		$this->fieldsById = array();
		$this->fieldsBySchemaName = array();
		$this->cachingEnabled = true;
	}

	/**
	 * Disable in-memory caching of fields by name and ID.
	 */
	function disableCaching() {
		$this->cachingEnabled = false;
		unset($this->fieldsById);
		unset($this->fieldsBySchemaName);
	}

	/**
	 * Retrieve a field by ID.
	 * @param $fieldId int
	 * @return Field
	 */
	function &getFieldById($fieldId) {
		if ($this->cachingEnabled && isset($this->fieldsById[$fieldId])) return $this->fieldsById[$fieldId];

		$result =& $this->retrieve(
			'SELECT	f.*,
				s.schema_plugin AS schema_plugin_name
			FROM	raw_fields f,
				schema_plugins s
			WHERE	f.schema_plugin_id = s.schema_plugin_id
				AND raw_field_id = ?',
			array((int) $fieldId)
		);

		$returner = null;
		$pluginName = null;
		if ($result->RecordCount() != 0) {
			$row = $result->GetRowAssoc(false);
			$returner =& $this->_returnFieldFromRow($row);
			$pluginName = $row['schema_plugin_name'];
		}
		$result->Close();
		unset($result);

		if ($returner != null && $this->cachingEnabled) {
			$this->fieldsById[$returner->getFieldId()] =& $returner;
			$this->fieldsBySchemaName[$pluginName][$returner->getName()] =& $returner;
		}

		return $returner;
	}

	/**
	 * Retrieve a field by field name.
	 * @param $fieldName string
	 * @param $schemaPluginName string
	 * @return Field
	 */
	function &getFieldByName($fieldName, $schemaPluginName) {
		if ($this->cachingEnabled && isset($this->fieldsBySchemaName[$schemaPluginName]) && isset($this->fieldsBySchemaName[$schemaPluginName][$fieldName])) return $this->fieldsBySchemaName[$schemaPluginName][$fieldName];

		$result =& $this->retrieve(
			'SELECT	f.*,
				s.schema_plugin
			FROM	raw_fields f,
				schema_plugins s
			WHERE	f.name = ?
				AND f.schema_plugin_id = s.schema_plugin_id
				AND s.schema_plugin = ?',
			array($fieldName, $schemaPluginName)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnFieldFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);

		if ($returner != null && $this->cachingEnabled) {
			$this->fieldsById[$returner->getFieldId()] =& $returner;
			$this->fieldsBySchemaName[$schemaPluginName][$returner->getName()] =& $returner;
		}

		return $returner;
	}

	/**
	 * Internal function to return a Field object from a row.
	 * @param $row array
	 * @return Field
	 */
	function &_returnFieldFromRow(&$row) {
		$field = new Field();
		$field->setFieldId($row['raw_field_id']);
		$field->setSchemaId($row['schema_plugin_id']);
		$field->setName($row['name']);

		HookRegistry::call('FieldDAO::_returnFieldFromRow', array(&$field, &$row));

		return $field;
	}

	/**
	 * Insert a new field.
	 * @param $field Field
	 */
	function insertField(&$field) {
		$this->update(
			'INSERT INTO raw_fields
				(name, schema_plugin_id)
				VALUES
				(?, ?)',
			array(
				$field->getName(),
				(int) $field->getSchemaId(),
			)
		);

		$field->setFieldId($this->getInsertFieldId());
		return $field->getFieldId();
	}

	/**
	 * Update an existing field.
	 * @param $field Field
	 */
	function updateField(&$field) {
		return $this->update(
			'UPDATE raw_fields
				SET
					name = ?,
					schema_plugin_id = ?
				WHERE raw_field_id = ?',
			array(
				$field->getName(),
				(int) $field->getSchemaId(),
				(int) $field->getFieldId()
			)
		);
	}

	/**
	 * Delete a field, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $field Field
	 */
	function deleteField(&$field) {
		$this->deleteFieldById($field->getFieldId());
	}

	/**
	 * Delete a field by ID, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $fieldId int
	 */
	function deleteFieldById($fieldId) {
		if (isset($this->fieldsById[$fieldId])) {
			unset($this->fieldsById[$fieldId]);
		}
		foreach ($this->fieldsBySchemaName as $schemaPluginName => $fields) {
			foreach ($fields as $fieldName => $field) {
				if ($field->getFieldId() == $fieldId) {
					unset($this->fieldsBySchemaName[$schemaPluginName][$fieldName]);
				}
			}
		}
		return $this->update(
			'DELETE FROM raw_fields WHERE field_id = ?', $fieldId
		);
	}

	/**
	 * Retrieve all fields, optionally by schema.
	 * @return DAOResultFactory containing matching fields
	 */
	function &getFields($schemaId = null, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT * FROM raw_fields' . (isset($schemaId)?' WHERE schema_plugin_id = ?':''),
			isset($schemaId)?array((int) $schemaId):false,
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnFieldFromRow');
		return $returner;
	}

	/**
	 * Retrieve all fields by sort order ID.
	 * @return DAOResultFactory containing matching fields
	 */
	function &getFieldsBySortOrder($sortOrderId = null, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT	rf.*
			FROM	raw_fields rf,
				sort_order_fields sof
			WHERE	rf.raw_field_id = sof.raw_field_id
				AND sof.sort_order_id = ?',
			array((int) $sortOrderId),
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnFieldFromRow');
		return $returner;
	}

	/**
	 * Get a field, creating it if necessary.
	 * @param $fieldName string
	 * @param $schemaPlugin string
	 * @return object Field
	 */
	function &buildField($fieldName, $schemaPluginName) {
		$field =& $this->getFieldByName($fieldName, $schemaPluginName);
		if (!$field) {
			$schemaDao =& DAORegistry::getDAO('SchemaDAO');
			$schema =& $schemaDao->buildSchema($schemaPluginName);

			$field = new Field();
			$field->setName($fieldName);
			$field->setSchemaId($schema->getSchemaId());

			$plugin =& $field->getSchemaPlugin();
			if (!$plugin) {
				fatalError('Unknown schema plugin "' . $schemaPlugin . '"!');
			}
			if (!in_array($fieldName, $plugin->getFieldList())) {
				// This field doesn't actually exist -- return null.
				unset($field);
				$field = null;
				return $field;
			}

			$this->insertField($field);
		}
		return $field;
	}

	/**
	 * Get the ID of the last inserted field.
	 * @return int
	 */
	function getInsertFieldId() {
		return $this->getInsertId('raw_fields', 'raw_field_id');
	}
}

?>
