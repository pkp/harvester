<?php

/**
 * @file Harvester.inc.php
 *
 * Copyright (c) 2005-2006 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package harvester
 * @class Harvester
 *
 * Generic harvester
 *
 * $Id$
 */

class Harvester {
	/** @var $record object The current record being harvested */
	var $record;

	/** @var $errors array */
	var $errors;

	/** @var $fieldDao object */
	var $fieldDao;

	/** @var $recordDao object */
	var $recordDao;

	/** @var $archive object */
	var $archive;

	function Harvester($archive) {
		$this->errors = array();

		$this->fieldDao =& DAORegistry::getDAO('FieldDAO');
		$this->recordDao =& DAORegistry::getDAO('RecordDAO');

		$this->archive =& $archive;

		// Make sure preprocessors are loaded.
		PluginRegistry::loadCategory('preprocessors');
	}

	function &getFieldByKey($fieldKey, $schemaPlugin) {
		$returner =& $this->fieldDao->buildField($fieldKey, $schemaPlugin);
		return $returner;
	}

	function &getRecordByIdentifier($identifier) {
		$returner =& $this->recordDao->getRecordByIdentifier($identifier);
		return $returner;
	}

	function insertEntry(&$field, $value, $attributes = array()) {
		$record =& $this->getRecord();
		$archive =& $this->getArchive();
		if (!$record) return null;

		if (HookRegistry::call('Harvester::insertEntry', array(&$archive, &$record, &$field, &$value, &$attributes))) return true;

		return $this->recordDao->insertEntry(
			$record->getRecordId(),
			$field->getFieldId(),
			$value,
			$attributes
		);
	}

	/**
	 * Return an array of error messages.
	 */
	function getErrors() {
		return $this->errors;
	}

	/**
	 * Get the archive object.
	 */
	function &getArchive() {
		return $this->archive;
	}

	/**
	 * Get the status of the harvester. False iff errors occurred.
	 */
	function getStatus() {
		return (empty($this->errors));
	}

	/**
	 * Add an error to the current list.
	 * @param $error string
	 */
	function addError($error) {
		array_push($this->errors, $error);
	}

	/**
	 * Get an iterator of records since the given UNIX timestamp.
	 * @param $recordHandler object
	 * @param $lastUpdateTimestamp int
	 * @return mixed false iff error, record count otherwise
	 */
	function updateRecords(&$recordHandler, $lastUpdateTimestamp) {
		fatalError ('ABSTRACT CLASS');
	}

	function setRecord(&$record) {
		unset($this->record);
		$this->record =& $record;
	}

	function &getRecord() {
		return $this->record;
	}
}

?>
