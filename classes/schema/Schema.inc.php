<?php

/**
 * @file Schema.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package schema
 * @class Schema
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
