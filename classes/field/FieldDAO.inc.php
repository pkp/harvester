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

	/**
	 * Constructor.
	 */
	function FieldDAO() {
		parent::DAO();
	}
	
	/**
	 * Retrieve a field by ID.
	 * @param $fieldId int
	 * @return Field
	 */
	function &getFieldById($fieldId) {
		$result = &$this->retrieve(
			'SELECT * FROM fields WHERE field_id = ?', $fieldId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnFieldFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);
		return $returner;
	}
	
	/**
	 * Retrieve a field by field key.
	 * @param $key string
	 * @return Field
	 */
	function &getFieldByKey($fieldKey) {
		$result = &$this->retrieve(
			'SELECT * FROM fields WHERE field_key = ?', $fieldKey
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnFieldFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);
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
		$field->setKey($row['field_key']);
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
