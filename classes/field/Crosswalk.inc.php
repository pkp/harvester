<?php

/**
 * Crosswalk.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package field
 *
 * Crosswalk class.
 * Describes basic field properties.
 *
 * $Id$
 */

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
