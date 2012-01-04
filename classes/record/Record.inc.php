<?php

/**
 * @file Record.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package record
 * @class Record
 *
 * Record class.
 * Describes basic record properties.
 *
 * $Id$
 */

class Record extends DataObject {
	/**
	 * Constructor.
	 */
	function Record() {
		parent::DataObject();
	}

	//
	// Get/set methods
	//

	/**
	 * Get identifier for record
	 * @return string
	 */
	function getIdentifier() {
	 	return $this->getData('identifier');
	}

	/**
	 * Set identifier for record
	 * @param $identifier string
	 */
	function setIdentifier($identifier) {
		return $this->setData('identifier',$identifier);
	}

	/**
	 * Get contents for record
	 * @return string
	 */
	function getContents() {
	 	return $this->getData('contents');
	}

	/**
	 * Set contents for record
	 * @param $contents string
	 */
	function setContents($contents) {
		return $this->setData('contents',$contents);
	}

	/**
	 * Get parsed contents for record
	 * @return object
	 */
	function getParsedContents() {
	 	return $this->getData('parsedContents');
	}

	/**
	 * Set parsed for record
	 * @param $parsedContents object
	 */
	function setParsedContents($parsedContents) {
		return $this->setData('parsedContents',$parsedContents);
	}

	/**
	 * Get datestamp of record
	 * @return string
	 */
	function getDatestamp() {
	 	return $this->getData('datestamp');
	}

	/**
	 * Set datestamp of record
	 * @param $datestamp string
	 */
	function setDatestamp($datestamp) {
		return $this->setData('datestamp',$datestamp);
	}

	/**
	 * Get ID of record.
	 * @return int
	 */
	function getRecordId() {
		return $this->getData('recordId');
	}

	/**
	 * Set ID of record.
	 * @param $recordId int
	 */
	function setRecordId($recordId) {
		return $this->setData('recordId', $recordId);
	}

	/**
	 * Get ID of this record's schema.
	 * @return int
	 */
	function getSchemaId() {
		return $this->getData('schemaId');
	}

	/**
	 * Set ID of this record's schema.
	 * @param $recordId int
	 */
	function setSchemaId($schemaId) {
		return $this->setData('schemaId', $schemaId);
	}

	/**
	 * Get ID of archive.
	 * @return int
	 */
	function getArchiveId() {
		return $this->getData('archiveId');
	}

	/**
	 * Set ID of archive.
	 * @param $archiveId int
	 */
	function setArchiveId($archiveId) {
		return $this->setData('archiveId', $archiveId);
	}

	/**
	 * Get the archive for this record.
	 * @return object
	 */
	function &getArchive() {
		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		return $archiveDao->getArchive($this->getArchiveId(), false);
	}

	function displaySummary() {
		$plugin =& $this->getSchemaPlugin();
		$plugin->displayRecordSummary($this);
	}

	function display() {
		$plugin =& $this->getSchemaPlugin();
		$plugin->displayRecord($this);
	}

	function getUrl() {
		$plugin =& $this->getSchemaPlugin();
		return $plugin->getUrl($this);
	}

	function &getSchemaPlugin() {
		$schemaDao =& DAORegistry::getDAO('SchemaDAO');
		$schema =& $schemaDao->getSchema($this->getSchemaId());
		return $schema->getPlugin();
	}

	function getTitle() {
		$plugin =& $this->getSchemaPlugin();
		return $plugin->getTitle($this);
	}

	function getAuthors() {
		$plugin =& $this->getSchemaPlugin();
		return $plugin->getAuthors($this);
	}

	function getAuthorString() {
		$plugin =& $this->getSchemaPlugin();
		return $plugin->getAuthorString($this);
	}
}

?>
