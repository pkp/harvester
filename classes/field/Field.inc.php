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
	 * Get type of field
	 * @return string
	 */
	 function getType() {
	 	return $this->getData('type');
	}
	
	/**
	 * Set type of field
	 * @param $type string
	 */
	function setType($type) {
		return $this->setData('type',$type);
	}

	/**
	 * Get sequence of field
	 * @return int
	 */
	 function getSeq() {
	 	return $this->getData('seq');
	}
	
	/**
	 * Set sequence of field
	 * @param $seq int
	 */
	function setSeq($seq) {
		return $this->setData('seq',$seq);
	}

	/**
	 * Get description of field
	 * @return string
	 */
	 function getDescription() {
	 	return $this->getData('description');
	}
	
	/**
	 * Set description of field
	 * @param $description string
	 */
	function setDescription($description) {
		return $this->setData('description', $description);
	}
	
	/**
	 * Get key of field
	 * @return string
	 */
	 function getFieldKey() {
	 	return $this->getData('fieldKey');
	}
	
	/**
	 * Set key of field
	 * @param $fieldKey string
	 */
	function setFieldKey($fieldKey) {
		return $this->setData('fieldKey',$fieldKey);
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
}

?>
