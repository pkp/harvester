<?php

/**
 * @file classes/field/Field.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Field
 * @in_group field
 *
 * @brief Field class.
 * Describes basic field properties.
 *
 */

// $Id$

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
	 * Get schema ID for field
	 * @return string
	 */
	 function getSchemaId() {
	 	return $this->getData('schemaId');
	}

	/**
	 * Set schema ID for field
	 * @param $schemaId string
	 */
	function setSchemaId($schemaId) {
		return $this->setData('schemaId',$schemaId);
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
		$schemaDao =& DAORegistry::getDAO('SchemaDAO');
		$schema =& $schemaDao->getSchema($this->getSchemaId());
		$plugin =& $schema->getPlugin();
		if (!$plugin) {
			fatalError('Unknown schema plugin ' . $this->getSchemaId() . ' for field ' . $this->getName() . '!');
		}
		return $plugin;
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
