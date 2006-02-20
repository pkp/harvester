<?php

/**
 * Schema.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package schema
 *
 * Schema class.
 * Describes basic schema properties.
 *
 * $Id$
 */

class Schema extends DataObject {

	/**
	 * Constructor.
	 */
	function Schema() {
		parent::DataObject();
	}
	
	//
	// Get/set methods
	//
	
	/**
	 * Get name of schema
	 * @return string
	 */
	 function getName() {
	 	return $this->getData('name');
	}
	
	/**
	 * Set name of schema
	 * @param $name string
	 */
	function setName($name) {
		return $this->setData('name',$name);
	}

	/**
	 * Get schema plugin name for schema
	 * @return string
	 */
	 function getPluginName() {
	 	return $this->getData('schema_plugin');
	}
	
	/**
	 * Set schema plugin name for schema
	 * @param $schemaPlugin string
	 */
	function setPluginName($schemaPlugin) {
		return $this->setData('schema_plugin',$schemaPlugin);
	}

	/**
	 * Get ID of schema.
	 * @return int
	 */
	function getSchemaId() {
		return $this->getData('schemaId');
	}
	
	/**
	 * Set ID of schema.
	 * @param $schemaId int
	 */
	function setSchemaId($schemaId) {
		return $this->setData('schemaId', $schemaId);
	}

	function &getPlugin() {
		$plugins =& PluginRegistry::loadCategory('schemas');
		$returner = null;
		if (isset($plugins[$this->getPluginName()])) {
			$returner =& $plugins[$this->getPluginName()];
		}
		return $returner;
	}

	function getDisplayName($locale = null) {
		$plugin =& $this->getSchemaPlugin();
		if (!$plugin) return null;
		return $plugin->getSchemaName($this->getName(), $locale);
	}

	function getDisplayDescription($locale = null) {
		$plugin =& $this->getSchemaPlugin();
		if (!$plugin) return null;
		return $plugin->getSchemaName($this->getName(), $locale);
	}
}

?>
