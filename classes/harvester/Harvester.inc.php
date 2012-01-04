<?php

/**
 * @file Harvester.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package harvester
 * @class Harvester
 *
 * Generic harvester
 *
 */

// $Id$


define('DUBLIN_CORE_METADATA_PREFIX', 'oai_dc');

class Harvester {
	/** @var $errors array */
	var $errors;

	/** @var $recordDao object */
	var $recordDao;

	/** @var $sortOrderDao object */
	var $sortOrderDao;

	/** @var $fieldDao object */
	var $fieldDao;

	/** @var $archive object */
	var $archive;

	function Harvester($archive) {
		$this->errors = array();
		$this->archive =& $archive;

		$this->recordDao =& DAORegistry::getDAO('RecordDAO');
		$this->fieldDao =& DAORegistry::getDAO('FieldDAO');
		$this->sortOrderDao =& DAORegistry::getDAO('SortOrderDAO');
	}

	/**
	 * Return an array of error messages.
	 */
	function getErrors() {
		return $this->errors;
	}

	/**
	 * Determine whether or not the harvester is in an error condition.
	 * @return boolean true == OK; false == error (see getErrors)
	 */
	function getStatus() {
		return empty($this->errors);
	}

	/**
	 * Get the archive object.
	 */
	function &getArchive() {
		return $this->archive;
	}

	/**
	 * Add an error to the current list.
	 * @param $error string
	 */
	function addError($error) {
		array_push($this->errors, $error);
	}

	function &getSchema() {
		fatalError('ABSTRACT CLASS');
	}

	/**
	 * Index the record sort indexes for a specific sort order.
	 * @param $record object
	 * @param $sortOrder object
	 */
	function indexRecordSortingForSortOrder(&$record, &$sortOrder) {
		$schemaPlugin =& $this->getSchemaPlugin();
		$schema =& $this->getSchema();
		$schemaId = $schema->getSchemaId();
		$recordId = $record->getRecordId();

		$sortOrderId = $sortOrder->getSortOrderId();
		$sortOrderType = $sortOrder->getType();
		$fields =& $this->fieldDao->getFieldsBySortOrder($sortOrder->getSortOrderId());
		while ($field =& $fields->next()) {
			if ($field->getSchemaId() == $schemaId) {
				$fieldName = $field->getName();
				$fieldValue = $schemaPlugin->getFieldValue($record, $fieldName, $sortOrderType);
				if ($fieldValue !== null) switch ($sortOrderType) {
					case SORT_ORDER_TYPE_STRING:
						$this->sortOrderDao->insertString($recordId, $sortOrderId, $fieldValue);
						break;
					case SORT_ORDER_TYPE_NUMBER:
						$this->sortOrderDao->insertNumber($recordId, $sortOrderId, $fieldValue);
						break;
					case SORT_ORDER_TYPE_DATE:
						$this->sortOrderDao->insertDate($recordId, $sortOrderId, $fieldValue);
						break;
				}
			}
			unset($field);
		}
	}

	/**
	 * Index the record sort indexes for all sort orders.
	 * @param $record object
	 */
	function indexRecordSorting(&$record) {
		// Deal with sort order indexing
		$sortOrders =& $this->sortOrderDao->getSortOrders();
		while ($sortOrder =& $sortOrders->next()) {
			$this->indexRecordSortingForSortOrder($record, $sortOrder);
			unset($sortOrder);
		}
		
	}

	/**
	 * Delete record sorting for a record.
	 */
	function deleteRecordSorting(&$record) {
		$this->sortOrderDao->flushRecordIndex($record->getRecordId());
	}

	function &getRecordByIdentifier($identifier) {
		$returner =& $this->recordDao->getRecordByIdentifier($identifier);
		return $returner;
	}

	function _deleteRecordByIdentifier($identifier) {
		$record =& $this->getRecordByIdentifier($identifier);
		if (!$record) return false;

		$this->recordDao->deleteRecordByIdentifier($identifier);

		HookRegistry::call('Harvester::deleteRecord', array(&$record));

		return true;
	}

	function _updateRecord(&$record, &$contents) {
		$schemaPlugin =& $this->getSchemaPlugin();

		$record->setContents($contents);
		$record->setParsedContents($schemaPlugin->parseContents($contents));
		$this->recordDao->updateRecord($record);

		$this->deleteRecordSorting($record);
		$this->indexRecordSorting($record);

		HookRegistry::call('Harvester::updateRecord', array(&$record));

		return true;
	}

	function _insertRecord($identifier, &$contents) {
		$schemaPlugin =& $this->getSchemaPlugin();
		$schema =& $this->getSchema();
		$schemaId = $schema->getSchemaId();

		$record = new Record();
		$record->setSchemaId($schemaId);
		$record->setArchiveId($this->archive->getArchiveId());
		$record->setContents($contents);
		$record->setParsedContents($schemaPlugin->parseContents($contents));
		$record->setIdentifier($identifier);
		$this->recordDao->insertRecord($record);

		$this->indexRecordSorting($record);

		HookRegistry::call('Harvester::insertRecord', array(&$record));

		return true;
	}
}

?>
