<?php

/**
 * Archive.inc.php
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package archive
 *
 * Archive class.
 * Describes basic archive properties.
 *
 * $Id$
 */

class Archive extends DataObject {

	/**
	 * Constructor.
	 */
	function Archive() {
		parent::DataObject();
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
	 * Get description of archive
	 * @return string
	 */
	 function getDescription() {
	 	return $this->getData('description');
	}
	
	/**
	 * Set description of archive
	 * @param $description string
	 */
	function setDescription($description) {
		return $this->setData('description', $description);
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
}

?>
