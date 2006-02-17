<?php

/**
 * Indexer.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package indexer
 *
 * Indexer class.
 * Describes basic indexer properties.
 *
 * $Id$
 */

class Indexer extends DataObject {
	/** Indexer DAO */
	var $indexerSettingsDao;

	/**
	 * Constructor.
	 */
	function Indexer() {
		parent::DataObject();
		$this->indexerSettingsDao =& DAORegistry::getDAO('IndexerSettingsDAO');
	}
	
	//
	// Get/set methods
	//
	
	/**
	 * Get name of indexer plugin
	 * @return string
	 */
	 function getIndexerPluginName() {
	 	return $this->getData('indexerPluginName');
	}
	
	/**
	 * Set name of indexer plugin
	 * @param $indexerPluginName string
	 */
	function setIndexerPluginName($indexerPluginName) {
		return $this->setData('indexerPluginName',$indexerPluginName);
	}

	/**
	 * Get sequence of indexer
	 * @return int
	 */
	 function getSeq() {
	 	return $this->getData('seq');
	}
	
	/**
	 * Set sequence of indexer
	 * @param $seq int
	 */
	function setSeq($seq) {
		return $this->setData('seq',$seq);
	}

	/**
	 * Get ID of indexer.
	 * @return int
	 */
	function getIndexerId() {
		return $this->getData('indexerId');
	}
	
	/**
	 * Set ID of indexer.
	 * @param $indexerId int
	 */
	function setIndexerId($indexerId) {
		return $this->setData('indexerId', $indexerId);
	}

	/**
	 * Get the indexer plugin associated with this indexer.
	 * @return object IndexerPlugin
	 */
	function &getIndexerPlugin() {
		$pluginName = $this->getIndexerPluginName();
		$plugins =& PluginRegistry::loadCategory('indexers');
		if (is_array($plugins) && isset($plugins[$pluginName])) {
			return $plugins[$pluginName];
		}
		fatalError("Unknown indexer plugin \"$pluginName\"!\n");
	}

	function displayAdminForm() {
		$plugin =& $this->getIndexerPlugin();
		$plugin->displayAdminForm($this);
	}

	function getPluginDisplayName() {
		$plugin =& $this->getIndexerPlugin();
		return $plugin->getDisplayName();
	}

	/**
	 * Install settings from an XML file.
	 * @param $filename
	 */
	function installSettings($filename, $paramArray = array()) {
		return $this->indexerSettingsDao->installSettings($filename, $paramArray);
	}

	/**
	 * Get a site setting.
	 * @param $name
	 */
	function getSetting($name) {
		return $this->indexerSettingsDao->getSetting($this->getIndexerId(), $name);
	}

	/**
	 * Update a site setting.
	 * @param $name
	 * @param $value
	 * @param $type
	 */
	function updateSetting($name, $value, $type = null) {
		$this->indexerSettingsDao->updateSetting($this->getIndexerId(), $name, $value, $type);
	}
}

?>
