<?php

/**
 * SearchableField.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package field
 *
 * SearchableField class.
 * Describes basic field properties.
 *
 * $Id$
 */

class SearchableField extends DataObject {

	/**
	 * Constructor.
	 */
	function SearchableField() {
		parent::DataObject();
	}
	
	//
	// Get/set methods
	//
	
	/**
	 * Get name of searchable field
	 * @return string
	 */
	 function getName() {
	 	return $this->getData('name');
	}
	
	/**
	 * Set name of searchable field
	 * @param $name string
	 */
	function setName($name) {
		return $this->setData('name',$name);
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
	 * Get ID of searchable field.
	 * @return int
	 */
	function getSearchableFieldId() {
		return $this->getData('searchableFieldId');
	}
	
	/**
	 * Set ID of searchable field.
	 * @param $searchableFieldId int
	 */
	function setSearchableFieldId($searchableFieldId) {
		return $this->setData('searchableFieldId', $searchableFieldId);
	}
}

?>
