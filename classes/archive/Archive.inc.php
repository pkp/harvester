<?php

/**
 * @file Archive.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package archive
 * @class Archive
 *
 * @brief Archive class.
 * Describes basic archive properties.
 *
 */

// $Id$


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
	 * Get enabled/disabled state of archive
	 * @return boolean
	 */
	function getEnabled() {
	 	return $this->getData('enabled');
	}

	/**
	 * Set enabled/disabled state of archive
	 * @param $enabled boolean
	 */
	function setEnabled($enabled) {
		return $this->setData('enabled',$enabled);
	}

	/**
	 * Get public ID of archive
	 * @return string
	 */
	function getPublicArchiveId() {
	 	return $this->getData('publicArchiveId');
	}

	/**
	 * Set public ID of archive
	 * @param $publicArchiveId string
	 */
	function setPublicArchiveId($publicArchiveId) {
		return $this->setData('publicArchiveId',$publicArchiveId);
	}

	/**
	 * Get name of harvester plugin
	 * @return string
	 */
	function getHarvesterPluginName() {
	 	return $this->getData('harvesterPlugin');
	}

	/**
	 * Set name of harvester plugin
	 * @param $harvesterPluginName string
	 */
	function setHarvesterPluginName($harvesterPluginName) {
		return $this->setData('harvesterPlugin',$harvesterPluginName);
	}

	/**
	 * Get the harvester plugin
	 * @return object
	 */
	function &getHarvesterPlugin() {
		$harvesterPluginName = $this->getHarvesterPluginName();
		$plugins =& PluginRegistry::loadCategory('harvesters');
		if (isset($plugins[$harvesterPluginName])) {
			return $plugins[$harvesterPluginName];
		}
		fatalError("Unknown plugin \"$harvesterPluginName\"!");
	}

	/**
	 * Get the harvester object
	 * @return object
	 */
	function &getHarvester() {
		$harvesterPlugin =& $this->getHarvesterPlugin();
		$harvester =& $harvesterPlugin->getHarvester($this);
		return $harvester;
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
	 * Get the user who created the archive
	 * @return object
	 */
	function &getUser() {
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& $userDao->getUser($this->getUserId());
		return $user;
	}

	/**
	 * Get ID of the user who created the archive.
	 * @return int
	 */
	function getUserId() {
		return $this->getData('userId');
	}

	/**
	 * Set ID of the user who created the archive.
	 * @param $userId int
	 */
	function setUserId($userId) {
		return $this->setData('userId', $userId);
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
		return $count;
	}

	/**
	 * Get the schema plugin name for this archive.
	 */
	function getSchemaPluginName() {
		return $this->getData('schemaPluginName');
	}

	/**
	 * Get the schema plugin name for this archive.
	 */
	function &getSchemaPlugin() {
		$plugins =& PluginRegistry::loadCategory('schemas');
		$schemaPluginName = $this->getSchemaPluginName();
		$returner = null;
		if (isset($plugins[$schemaPluginName])) {
			$returner =& $plugins[$schemaPluginName];
		}
		return $returner;
	}

	/**
	 * Set the schema plugin name for this archive.
	 */
	function setSchemaPluginName($schemaPluginName) {
		$this->setData('schemaPluginName', $schemaPluginName);
	}
}

?>
