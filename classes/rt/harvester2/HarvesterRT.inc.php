<?php

/**
 * HarvesterRT.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package rt.harvester2
 *
 * Harvester2-specific Reading Tools end-user interface.
 *
 * $Id$
 */

import('rt.RT');
import('rt.harvester2.RTDAO');

class HarvesterRT extends RT {
	var $archiveId;
	var $enabled;

	function HarvesterRT($archiveId) {
		$this->setArchiveId($archiveId);
	}

	// Getter/setter methods

	function getArchiveId() {
		return $this->archiveId;
	}

	function setArchiveId($archiveId) {
		$this->archiveId = $archiveId;
	}
}

?>
