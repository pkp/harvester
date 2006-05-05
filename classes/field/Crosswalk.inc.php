<?php

/**
 * @file Crosswalk.inc.php
 *
 * Copyright (c) 2005-2006 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package field
 * @class Crosswalk
 *
 * Crosswalk class.
 * Describes basic field properties.
 *
 * $Id$
 */

// Bring in FIELD_TYPE_...
import('field.Field');

class Crosswalk extends DataObject {

	/**
	 * Constructor.
	 */
	function Crosswalk() {
		parent::DataObject();
	}
	
	//
	// Get/set methods
	//
	
	/**
	 * Get name of crosswalk
	 * @return string
	 */
	 function getName() {
	 	return $this->getData('name');
	}
	
	/**
	 * Set name of crosswalk
	 * @param $name string
	 */
	function setName($name) {
		return $this->setData('name',$name);
	}

	/**
	 * Get public ID of crosswalk
	 * @return string
	 */
	 function getPublicCrosswalkId() {
	 	return $this->getData('publicCrosswalkId');
	}
	
	/**
	 * Set public ID of crosswalk
	 * @param $publicCrosswalkId string
	 */
	function setPublicCrosswalkId($publicCrosswalkId) {
		return $this->setData('publicCrosswalkId',$publicCrosswalkId);
	}

	/**
	 * Get type of crosswalk
	 * @return int FIELD_TYPE_...
	 */
	 function getType() {
	 	return $this->getData('type');
	}
	
	/**
	 * Set type of crosswalk
	 * @param $type int FIELD_TYPE_...
	 */
	function setType($type) {
		return $this->setData('type',$type);
	}

	/**
	 * Is this crosswalk sortable?
	 * @return boolean
	 */
	 function getSortable() {
	 	return $this->getData('sortable');
	}
	
	/**
	 * Set whether or not this crosswalk is sortable
	 * @param $sortable boolean
	 */
	function setSortable($sortable) {
		return $this->setData('sortable',$sortable);
	}

	/**
	 * Get localized name of crosswalk
	 * @return string
	 */
	function getCrosswalkName() {
	 	// FIXME: Localize.
	 	return $this->getName();
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
	 * Get localized description of crosswalk
	 * @return string
	 */
	function getCrosswalkDescription() {
	 	// FIXME: Localize.
	 	return $this->getDescription();
	}
	
	/**
	 * Get ID of crosswalk.
	 * @return int
	 */
	function getCrosswalkId() {
		return $this->getData('crosswalkId');
	}
	
	/**
	 * Set ID of crosswalk.
	 * @param $crosswalkId int
	 */
	function setCrosswalkId($crosswalkId) {
		return $this->setData('crosswalkId', $crosswalkId);
	}

	function &getFields() {
		$crosswalkDao =& DAORegistry::getDAO('CrosswalkDAO');
		return $crosswalkDao->getFieldsByCrosswalkId($this->getCrosswalkId());
	}
}

?>
