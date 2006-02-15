<?php

/**
 * SearchableFieldDAO.inc.php
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package field
 *
 * Class for SearchableField DAO.
 * Operations for retrieving and modifying SearchableField objects.
 *
 * $Id$
 */

import ('field.SearchableField');

class SearchableFieldDAO extends DAO {
	/**
	 * Constructor.
	 */
	function SearchableFieldDAO() {
		parent::DAO();
		$this->cachingEnabled = false;
	}

	/**
	 * Retrieve a searchable field by ID.
	 * @param $fieldId int
	 * @return SearchableField
	 */
	function &getSearchableFieldById($searchableFieldId) {

		$result = &$this->retrieve(
			'SELECT * FROM searchable_fields WHERE searchable_field_id = ?', $searchableFieldId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnSearchableFieldFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);

		return $returner;
	}
	
	/**
	 * Internal function to return a SearchableField object from a row.
	 * @param $row array
	 * @return SearchableField
	 */
	function &_returnSearchableFieldFromRow(&$row) {
		$searchableField = &new SearchableField();
		$searchableField->setSearchableFieldId($row['searchable_field_id']);
		$searchableField->setName($row['name']);
		$searchableField->setDescription($row['description']);
		$searchableField->setSeq($row['seq']);
		
		HookRegistry::call('SearchableFieldDAO::_returnSearchableFieldFromRow', array(&$searchableField, &$row));

		return $searchableField;
	}

	/**
	 * Insert a new field.
	 * @param $field SearchableField
	 */	
	function insertSearchableField(&$field) {
		$this->update(
			'INSERT INTO searchable_fields
				(name, description, seq)
				VALUES
				(?, ?, ?, ?)',
			array(
				$field->getName(),
				$field->getDescription(),
				$field->getSeq()
			)
		);
		
		$field->setSearchableFieldId($this->getInsertSearchableFieldId());
		return $field->getSearchableFieldId();
	}
	
	/**
	 * Update an existing field.
	 * @param $field SearchableField
	 */
	function updateSearchableField(&$field) {
		return $this->update(
			'UPDATE searchable_fields
				SET
					name = ?,
					description = ?,
					seq = ?
				WHERE searchable_field_id = ?',
			array(
				$field->getName(),
				$field->getDescription(),
				$field->getSeq(),
				$field->getSearchableFieldId()
			)
		);
	}
	
	/**
	 * Delete a field, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $searchableField SearchableField
	 */
	function deleteSearchableField(&$searchableField) {
		return $this->deleteSearchableFieldById($searchableField->getSearchableFieldId());
	}
	
	/**
	 * Delete a searchable field by ID, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $searchableFieldId int
	 */
	function deleteSearchableFieldById($searchableFieldId) {
		return $this->update(
			'DELETE FROM searchable_fields WHERE searchable_field_id = ?', $searchableFieldId
		);
	}
	
	/**
	 * Retrieve all searchable fields.
	 * @return DAOResultFactory containing matching searchable fields
	 */
	function &getSearchableFields($rangeInfo = null) {
		$result = &$this->retrieveRange(
			'SELECT * FROM searchable_fields ORDER BY seq',
			false, $rangeInfo
		);

		$returner = &new DAOResultFactory($result, $this, '_returnSearchableFieldFromRow');
		return $returner;
	}
	
	/**
	 * Get the ID of the last inserted field.
	 * @return int
	 */
	function getInsertSearchableFieldId() {
		return $this->getInsertId('searchable_fields', 'searchable_field_id');
	}
	
}

?>
