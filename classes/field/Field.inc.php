<?php

/**
 * Field.inc.php
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package field
 *
 * Field class.
 * Describes basic field properties.
 *
 * $Id$
 */

class Field extends DataObject {

	/**
	 * Constructor.
	 */
	function Field() {
		parent::DataObject();
	}
	
	//
	// Get/set methods
	//
	
	/**
	 * Get name of field
	 * @return string
	 */
	 function getName() {
	 	return $this->getData('name');
	}
	
	/**
	 * Set name of field
	 * @param $name string
	 */
	function setName($name) {
		return $this->setData('name',$name);
	}

	/**
	 * Get schema plugin name for field
	 * @return string
	 */
	 function getSchemaPluginName() {
	 	return $this->getData('schema_plugin');
	}
	
	/**
	 * Set schema plugin name for field
	 * @param $schemaPlugin string
	 */
	function setSchemaPluginName($schemaPlugin) {
		return $this->setData('schema_plugin',$schemaPlugin);
	}

	/**
	 * Get ID of field.
	 * @return int
	 */
	function getFieldId() {
		return $this->getData('fieldId');
	}
	
	/**
	 * Set ID of field.
	 * @param $fieldId int
	 */
	function setFieldId($fieldId) {
		return $this->setData('fieldId', $fieldId);
	}

	function &getSchemaPlugin() {
		$plugins =& PluginRegistry::loadCategory('schemas');
		$returner = null;
		if (isset($plugins[$this->getSchemaPluginName()])) {
			$returner =& $plugins[$this->getSchemaPluginName()];
		}
		return $returner;
	}

	function getDisplayName($locale = null) {
		$plugin =& $this->getSchemaPlugin();
		if (!$plugin) return null;
		return $plugin->getFieldName($this->getName(), $locale);
	}

	function getDisplayDescription($locale = null) {
		$plugin =& $this->getSchemaPlugin();
		if (!$plugin) return null;
		return $plugin->getFieldName($this->getName(), $locale);
	}
}

?>
