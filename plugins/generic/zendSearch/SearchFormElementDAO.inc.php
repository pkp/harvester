<?php

/**
 * @file SearchFormElementDAO.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins_generic_zendSearch
 * @class SearchFormElementDAO
 *
 * @brief Class for search form DAO.
 * Operations for retrieving and modifying Zend Search (Lucene) search form.
 *
 */

// $Id$


class SearchFormElementDAO extends DAO {
	/**
	 * Constructor.
	 */
	function SearchFormElementDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve a search form element by ID.
	 * @param $searchFormElementId int
	 * @return SearchFormElement
	 */
	function &getSearchFormElement($searchFormElementId) {
		$result =& $this->retrieve(
			'SELECT * FROM search_form_elements WHERE search_form_element_id = ?', (int) $searchFormElementId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnSearchFormElementFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Check if a search form element exists with a specified symbolic name.
	 * @param $symbolic the symbolic name of the search form element
	 * @return boolean
	 */
	function searchFormElementExistsBySymbolic($symbolic) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM search_form_elements WHERE symbolic = ?', $symbolic
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Internal function to return a search form element object from a row.
	 * @param $row array
	 * @return Archive
	 */
	function &_returnSearchFormElementFromRow(&$row) {
		$searchFormElement = new SearchFormElement();
		$searchFormElement->setSearchFormElementId($row['search_form_element_id']);
		$searchFormElement->setType($row['element_type']);
		$searchFormElement->setRangeStart($row['range_start']);
		$searchFormElement->setRangeEnd($row['range_end']);
		$searchFormElement->setSymbolic($row['symbolic']);
		$searchFormElement->setIsClean($row['is_clean']);

		$this->getDataObjectSettings('search_form_element_settings', 'search_form_element_id', $row['search_form_element_id'], $searchFormElement);

		HookRegistry::call('SearchFormElementDAO::_returnSearchFormElementFromRow', array(&$searchFormElement, &$row));

		return $searchFormElement;
	}

	/**
	 * Get the list of localized field names for this table
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title');
	}

	/**
	 * Retrieve all fields by search form element ID.
	 * @return DAOResultFactory containing matching fields
	 */
	function &getFieldsBySearchFormElement($searchFormElementId = null, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT	rf.*
			FROM	raw_fields rf,
				search_form_element_fields sef
			WHERE	rf.raw_field_id = sef.raw_field_id
				AND sef.search_form_element_id = ?',
			array((int) $searchFormElementId),
			$rangeInfo
		);

		$fieldDao =& DAORegistry::getDAO('FieldDAO');
		$returner = new DAOResultFactory($result, $fieldDao, '_returnFieldFromRow');
		return $returner;
	}

	/**
	 * Update the settings for this object
	 * @param $searchFormElement object
	 */
	function updateLocaleFields(&$searchFormElement) {
		$this->updateDataObjectSettings('search_form_element_settings', $searchFormElement, array(
			'search_form_element_id' => $searchFormElement->getSearchFormElementId()
		));
	}

	/**
	 * Insert a new search form element.
	 * @param $searchFormElement SearchFormElement
	 */	
	function insertSearchFormElement(&$searchFormElement) {
		$this->update(
			'INSERT INTO search_form_elements
				(symbolic, is_clean, element_type, range_start, range_end)
			VALUES
				(?, ?, ?, ?, ?)',
			array(
				(string) $searchFormElement->getSymbolic(),
				$searchFormElement->getIsClean()?1:0,
				(int) $searchFormElement->getType(),
				String::substr($searchFormElement->getRangeStart(), 0, 255),
				String::substr($searchFormElement->getRangeEnd(), 0, 255)
			)
		);

		$searchFormElementId = $this->getInsertSearchFormElementId();
		$searchFormElement->setSearchFormElementId($searchFormElementId);

		$this->updateLocaleFields($searchFormElement);

		return $searchFormElement->getSearchFormElementId();
	}

	/**
	 * Update an existing search form element.
	 * @param $searchFormElement SearchFormElement
	 */
	function updateSearchFormElement(&$searchFormElement) {
		$returner = $this->update(
			'UPDATE	search_form_elements
			SET	symbolic = ?,
				is_clean = ?,
				element_type = ?,
				range_start = ?,
				range_end = ?
			WHERE	search_form_element_id = ?',
			array(
				(string) $searchFormElement->getSymbolic(),
				$searchFormElement->getIsClean()?1:0,
				(int) $searchFormElement->getType(),
				String::substr($searchFormElement->getRangeStart(), 0, 255),
				String::substr($searchFormElement->getRangeEnd(), 0, 255),
				(int) $searchFormElement->getSearchFormElementId()
			)
		);
		$this->updateLocaleFields($searchFormElement);
		return $returner;
	}

	/**
	 * Delete a search form element
	 * @param $searchFormElement SearchFormElement
	 */
	function deleteSearchFormElement(&$searchFormElement) {
		return $this->deleteSearchFormElementById($searchFormElement->getSearchFormElementId());
	}

	/**
	 * Delete a search form element by ID
	 * @param $searchFormElementId int
	 */
	function deleteSearchFormElementById($searchFormElementId) {
		$this->update(
			'DELETE FROM search_form_element_fields WHERE search_form_element_id = ?', $searchFormElementId
		);
		$this->update(
			'DELETE FROM search_form_element_settings WHERE search_form_element_id = ?', $searchFormElementId
		);
		$this->update(
			'DELETE FROM search_form_element_options WHERE search_form_element_id = ?', $searchFormElementId
		);
		return $this->update(
			'DELETE FROM search_form_elements WHERE search_form_element_id = ?', $searchFormElementId
		);
	}

	/**
	 * Retrieve all search form elements.
	 * @param $rangeInfo object
	 * @return DAOResultFactory containing matching search form elements
	 */
	function &getSearchFormElements($rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT * FROM search_form_elements',
			false, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnSearchFormElementFromRow');
		return $returner;
	}

	/**
	 * Set the fields associated with a search form element.
	 * @param $searchFormElementId int
	 * @param $fieldIds array Array of integers representing field IDs
	 */
	function setSearchFormElementFields($searchFormElementId, $fieldIds) {
		$this->deleteSearchFormElementFieldsBySearchFormElement($searchFormElementId);

		$fieldDao =& DAORegistry::getDAO('FieldDAO');
		foreach ($fieldIds as $fieldId) {
			$field =& $fieldDao->getFieldById($fieldId);
			$this->insertSearchFormElementField($searchFormElementId, $field->getSchemaId(), $fieldId);
			unset($field);
		}
	}

	/**
	 * Delete a search form element's field associations by search form element ID.
	 * @param $searchFormElementId int
	 * @return int
	 */
	function deleteSearchFormElementFieldsBySearchFormElement($searchFormElementId) {
		return $this->update(
			'DELETE FROM search_form_element_fields WHERE search_form_element_id = ?',
			array((int) $searchFormElementId)
		);
	}

	/**
	 * Insert a field/search form element association.
	 * @param $searchFormElementId int
	 * @param $schemaPluginId int
	 * @param $fieldId int
	 */
	function insertSearchFormElementField($searchFormElementId, $schemaPluginId, $fieldId) {
		return $this->update(
			'INSERT INTO search_form_element_fields
				(search_form_element_id, raw_field_id, schema_plugin_id)
			VALUES
				(?, ?, ?)',
			array(
				(int) $searchFormElementId,
				(int) $fieldId,
				(int) $schemaPluginId
			)
		);
	}

	/**
	 * Insert a search form element option.
	 * @param $searchFormElementId int
	 * @param $value string
	 * @return int Count
	 */
	function insertSearchFormElementOption($searchFormElementId, $value) {
		return $this->update(
			'INSERT INTO search_form_element_options
				(search_form_element_id, value)
			VALUES
				(?, ?)',
			array(
				(int) $searchFormElementId,
				String::substr($value, 0, 128)
			)
		);
	}

	/**
	 * Check if a search form element option exists.
	 * @param $searchFormElementId int
	 * @param $value string
	 * @return boolean
	 */
	function searchFormElementOptionExists($searchFormElementId, $value) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM search_form_element_options WHERE search_form_element_id = ? AND value = ?', array(
				(int) $searchFormElementId,
				String::substr($value, 0, 128)
			)
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve all search form element options for a search form element.
	 * @param $searchFormElementId int
	 * @param $rangeInfo object
	 * @return DAOResultFactory containing matching search form elements
	 */
	function &getSearchFormElementOptions($searchFormElementId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT value FROM search_form_element_options WHERE search_form_element_id = ? ORDER BY value',
			array((int) $searchFormElementId),
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnSearchFormElementOptionFromRow');
		return $returner;
	}

	/**
	 * Delete a search form element's options by search form element ID.
	 * @param $searchFormElementId int
	 * @return int
	 */
	function deleteSearchFormElementOptions($searchFormElementId) {
		return $this->update(
			'DELETE FROM search_form_element_options WHERE search_form_element_id = ?',
			array((int) $searchFormElementId)
		);
	}

	/**
	 * Get a search form element option value from a row.
	 */
	function _returnSearchFormElementOptionFromRow(&$row) {
		return $row['value'];
	}

	/**
	 * Get the ID of the last inserted search form element.
	 * @return int
	 */
	function getInsertSearchFormElementId() {
		return $this->getInsertId('search_form_elements', 'search_form_element_id');
	}
}

?>
