<?php

/**
 * FieldDAO.inc.php
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package field
 *
 * Class for Field DAO.
 * Operations for retrieving and modifying Field objects.
 *
 * $Id$
 */

import ('field.Field');

class FieldDAO extends DAO {
	/** @var $cachingEnabled boolean */
	var $cachingEnabled;

	/** @var $fieldsById array Cached fields by ID, when caching enabled */
	var $fieldsById;

	/** @var $fieldsByName array Cached fields by name, when caching enabled */
	var $fieldsByName;

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
		$this->fieldsByName = array();
		$this->cachingEnabled = true;
	}

	/**
	 * Disable in-memory caching of fields by name and ID.
	 */
	function disableCaching() {
		$this->cachingEnabled = false;
		unset($this->fieldsById);
		unset($this->fieldsByName);
	}

	/**
	 * Retrieve a field by ID.
	 * @param $fieldId int
	 * @return Field
	 */
	function &getFieldById($fieldId) {
		if ($this->cachingEnabled && isset($this->fieldsById[$fieldId])) return $this->fieldsById[$fieldId];

		$result = &$this->retrieve(
			'SELECT * FROM fields WHERE field_id = ?', $fieldId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnFieldFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);

		if ($returner != null && $this->cachingEnabled) {
			$this->fieldsById[$returner->getFieldId()] =& $returner;
			$this->fieldsByName[$returner->getSchemaPluginName()][$returner->getName()] =& $returner;
		}

		return $returner;
	}
	
	/**
	 * Retrieve a field by field name.
	 * @param $fieldName string
	 * @param $schemaPlugin string
	 * @return Field
	 */
	function &getFieldByName($fieldName, $schemaPlugin) {
		if ($this->cachingEnabled && isset($this->fieldsByName[$schemaPlugin]) && isset($this->fieldsByName[$schemaPlugin][$fieldName])) return $this->fieldsByName[$schemaPlugin][$fieldName];

		$result = &$this->retrieve(
			'SELECT * FROM fields WHERE name = ? AND schema_plugin = ?', array($fieldName, $schemaPlugin)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnFieldFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);

		if ($returner != null && $this->cachingEnabled) {
			$this->fieldsById[$returner->getFieldId()] =& $returner;
			$this->fieldsByName[$returner->getSchemaPluginName()][$returner->getName()] =& $returner;
		}

		return $returner;
	}
	
	/**
	 * Internal function to return a Field object from a row.
	 * @param $row array
	 * @return Field
	 */
	function &_returnFieldFromRow(&$row) {
		$field = &new Field();
		$field->setFieldId($row['field_id']);
		$field->setSchemaPluginName($row['schema_plugin']);
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
			'INSERT INTO fields
				(name, schema_plugin)
				VALUES
				(?, ?)',
			array(
				$field->getName(),
				$field->getSchemaPluginName(),
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
			'UPDATE fields
				SET
					name = ?,
					schema_plugin = ?
				WHERE field_id = ?',
			array(
				$field->getName(),
				$field->getSchemaPluginName(),
				$field->getFieldId()
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
		foreach ($this->fieldsByName as $schemaPlugin => $fields) {
			foreach ($fields as $fieldName => $field) {
				if ($field->getFieldId() == $fieldId) {
					unset($this->fieldsByName[$schemaPlugin][$fieldName]);
				}
			}
		}
		return $this->update(
			'DELETE FROM fields WHERE field_id = ?', $fieldId
		);
	}
	
	/**
	 * Retrieve all fields.
	 * @return DAOResultFactory containing matching fields
	 */
	function &getFields($rangeInfo = null) {
		$result = &$this->retrieveRange(
			'SELECT * FROM fields',
			false, $rangeInfo
		);

		$returner = &new DAOResultFactory($result, $this, '_returnFieldFromRow');
		return $returner;
	}

	/**
	 * Get a field, creating it if necessary.
	 * @param $fieldName string
	 * @param $schemaPlugin string
	 * @return object Field
	 */
	function &buildField($fieldName, $schemaPlugin) {
		$field =& $this->getFieldByName($fieldName, $schemaPlugin);
		if (!$field) {
			$field =& new Field();
			$field->setName($fieldName);
			$field->setSchemaPluginName($schemaPlugin);

			$plugin =& $field->getSchemaPlugin();
			if (!$plugin) {
				fatalError('Unknown schema plugin "' . $schemaPlugin . '"!');
			}
			if (!in_array($fieldName, $plugin->getFieldList())) {
				fatalError('Unknown field "' . $fieldName . '" for schema plugin "' . $schemaPlugin . '"!');
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
		return $this->getInsertId('fields', 'field_id');
	}

}

?>
