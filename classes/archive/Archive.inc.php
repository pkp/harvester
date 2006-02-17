<?php

/**
 * Archive.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package archive
 *
 * Archive class.
 * Describes basic archive properties.
 *
 * $Id$
 */

class Archive extends DataObject {
	/** Archive settings DAO */
	var $archiveSettingsDao;

	/**
	 * Constructor.
	 */
	function Archive() {
		parent::DataObject();
		$this->archiveSettingsDao =& DAORegistry::getDAO('ArchiveSettingsDAO');
	}
	
	//
	// Get/set methods
	//
	
	/**
	 * Get title of archive
	 * @return string
	 */
	 function getTitle() {
	 	return $this->getData('title');
	}
	
	/**
	 * Set title of archive
	 * @param $title string
	 */
	function setTitle($title) {
		return $this->setData('title',$title);
	}

	/**
	 * Get name of harvester plugin
	 * @return string
	 */
	 function getHarvesterPlugin() {
	 	return $this->getData('harvesterPlugin');
	}
	
	/**
	 * Set name of harvester plugin
	 * @param $harvesterPlugin string
	 */
	function setHarvesterPlugin($harvesterPlugin) {
		return $this->setData('harvesterPlugin',$harvesterPlugin);
	}

	/**
	 * Get url of archive
	 * @return string
	 */
	 function getUrl() {
	 	return $this->getData('url');
	}
	
	/**
	 * Set url of archive
	 * @param $url string
	 */
	function setUrl($url) {
		return $this->setData('url',$url);
	}

	/**
	 * Get description of archive
	 * @return string
	 */
	 function getDescription() {
	 	return $this->getData('description');
	}
	
	/**
	 * Set description of archive
	 * @param $description string
	 */
	function setDescription($description) {
		return $this->setData('description', $description);
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
	 * Install settings from an XML file.
	 * @param $filename
	 */
	function installSettings($filename, $paramArray = array()) {
		return $this->archiveSettingsDao->installSettings($filename, $paramArray);
	}

	/**
	 * Get a site setting.
	 * @param $name
	 */
	function getSetting($name) {
		return $this->archiveSettingsDao->getSetting($this->getArchiveId(), $name);
	}

	/**
	 * Update a site setting.
	 * @param $name
	 * @param $value
	 * @param $type
	 */
	function updateSetting($name, $value, $type = null) {
		$this->archiveSettingsDao->updateSetting($this->getArchiveId(), $name, $value, $type);
	}

	/**
	 * Set the last indexed date for the archive.
	 */
	function setLastIndexedDate($lastIndexedDate = null) {
		$this->updateSetting('lastIndexedDate', $lastIndexedDate, 'string');
	}

	/**
	 * Get the last indexed date.
	 * @return string
	 */
	function getLastIndexedDate() {
		return $this->getSetting('lastIndexedDate');
	}

	/**
	 * Get the current record count for the archive.
	 * @return int
	 */
	function getRecordCount() {
		$count = $this->getSetting('recordCount');
		if (!is_int($count)) return 0;
		return $count;
	}

	/**
	 * Update the record count for the archive.
	 * @return int
	 */
	function updateRecordCount() {
		$recordDao =& DAORegistry::getDAO('RecordDAO');
		$count = $recordDao->getRecordCount($this->getArchiveId());
		$this->updateSetting('recordCount', $count, 'int');
	}
}

?>
