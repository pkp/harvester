<?php

/**
 * @file Harvester.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package harvester
 * @class Harvester
 *
 * Generic harvester
 *
 * $Id$
 */

class Harvester {
	/** @var $errors array */
	var $errors;

	/** @var $recordDao object */
	var $recordDao;

	/** @var $archive object */
	var $archive;

	/** @var $status boolean Success (true)/failure */
	var $status;

	function Harvester($archive) {
		$this->errors = array();
		$this->status = true;

		$this->recordDao =& DAORegistry::getDAO('RecordDAO');

		$this->archive =& $archive;
	}

	/**
	 * Return an array of error messages.
	 */
	function getErrors() {
		return $this->errors;
	}

	/**
	 * Get the archive object.
	 */
	function &getArchive() {
		return $this->archive;
	}

	/**
	 * Add an error to the current list.
	 * @param $error string
	 */
	function addError($error) {
		array_push($this->errors, $error);
	}
}

?>
