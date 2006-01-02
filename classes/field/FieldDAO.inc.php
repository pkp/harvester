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

	/** @var $fieldsByKey array Cached fields by key, when caching enabled */
	var $fieldsByKey;

	/**
	 * Constructor.
	 */
	function FieldDAO() {
		parent::DAO();
		$this->cachingEnabled = false;
	}

	/**
	 * Enable in-memory caching of fields by key and ID. NOTE: This should
	 * only be enabled for situations where many fields are used repeatedly
	 * in a read-only situation. Update and insert calls WILL BE BUGGY
	 * when enableCaching is enabled. It should be disabled immediately
	 * after use is complete.
	 */
	function enableCaching() {
		$this->fieldsById = array();
		$this->fieldsByKey = array();
		$this->cachingEnabled = true;
	}

	/**
	 * Disable in-memory caching of fields by key and ID.
	 */
	function disableCaching() {
		$this->cachingEnabled = false;
		unset($this->fieldsById);
		unset($this->fieldsByKey);
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
			$this->fieldsByKey[$returner->getFieldKey()] =& $returner;
		}

		return $returner;
	}
	
	/**
	 * Retrieve a field by field key.
	 * @param $key string
	 * @return Field
	 */
	function &getFieldByKey($fieldKey) {
		if ($this->cachingEnabled && isset($this->fieldsByKey[$fieldKey])) return $this->fieldsByKey[$fieldKey];

		$result = &$this->retrieve(
			'SELECT * FROM fields WHERE field_key = ?', $fieldKey
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnFieldFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);

		if ($returner != null && $this->cachingEnabled) {
			$this->fieldsById[$returner->getFieldId()] =& $returner;
			$this->fieldsByKey[$returner->getFieldKey()] =& $returner;
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
		$field->setType($row['type']);
		$field->setFieldKey($row['field_key']);
		$field->setName($row['name']);
		$field->setDescription($row['description']);
		$field->setSeq($row['seq']);
		
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
				(type, field_key, name, description, seq)
				VALUES
				(?, ?, ?, ?, ?)',
			array(
				$field->getType(),
				$field->getFieldKey(),
				$field->getName(),
				$field->getDescription(),
				$field->getSeq()
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
					type = ?,
					field_key = ?,
					name = ?,
					description = ?,
					seq = ?
				WHERE field_id = ?',
			array(
				$field->getType(),
				$field->getFieldKey(),
				$field->getName(),
				$field->getDescription(),
				$field->getSeq(),
				$field->getFieldId()
			)
		);
	}
	
	/**
	 * Delete a field, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $field Field
	 */
	function deleteField(&$field) {
		return $this->deleteFieldById($field->getFieldId());
	}
	
	/**
	 * Delete a field by ID, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $fieldId int
	 */
	function deleteFieldById($fieldId) {
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
	 * Get the ID of the last inserted field.
	 * @return int
	 */
	function getInsertFieldId() {
		return $this->getInsertId('fields', 'field_id');
	}
	
}

?>
