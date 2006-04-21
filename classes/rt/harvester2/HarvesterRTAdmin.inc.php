<?php

/**
 * HarvesterRTAdmin.inc.php
 *
 * Copyright (c) 2003-2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package rt.harvester2
 *
 * OJS-specific Reading Tools administration interface.
 *
 * $Id$
 */

import('rt.RTAdmin');
import('rt.harvester2.RTDAO');

define('RT_DIRECTORY', 'rt');

class HarvesterRTAdmin extends RTAdmin {

	/** @var $archiveId int */
	var $archiveId;
	
	/** @var $dao DAO */
	var $dao;
	

	function HarvesterRTAdmin($archiveId) {
		$this->archiveId = $archiveId;
		$this->dao = &DAORegistry::getDAO('RTDAO');
	}

	function restoreVersions($deleteBeforeLoad = true) {
		import('rt.RTXMLParser');
		$parser = &new RTXMLParser();

		if ($deleteBeforeLoad) $this->dao->deleteVersionsByArchiveId($this->archiveId);

		$versions = $parser->parseAll(RT_DIRECTORY . '/' . Locale::getLocale()); // FIXME?
		foreach ($versions as $version) {
			$this->dao->insertVersion($this->archiveId, $version);
		}
	}

	function importVersion($filename) {
		import ('rt.RTXMLParser');
		$parser = &new RTXMLParser();

		$version = &$parser->parse($filename);
		$this->dao->insertVersion($this->archiveId, $version);
	}
}

?>
