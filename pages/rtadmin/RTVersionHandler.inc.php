<?php

/**
 * @file pages/rtadmin/RTVersionHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.rtadmin
 * @class RTVersionHandler
 *
 * Handle Reading Tools administration requests -- setup section.
 *
 */

// $Id$


import('classes.rt.harvester2.HarvesterRTAdmin');
import('pages.rtadmin.RTAdminHandler');

class RTVersionHandler extends RTAdminHandler {
	function createVersion($args) {
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		import('classes.rt.harvester2.form.VersionForm');
		$archiveId = (int) Request::getUserVar('archiveId');
		$versionForm = new VersionForm(null, $archiveId);

		if (isset($args[0]) && $args[0]=='save') {
			$versionForm->readInputData();
			$versionForm->execute();
			Request::redirect(null, 'versions');
		} else {
			$this->setupTemplate(true, $archiveId);
			$versionForm->display();
		}
	}

	function exportVersion($args) {
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$versionId = (int) array_shift($args);
		$version =& $rtDao->getVersion($versionId);

		if ($version) {
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign_by_ref('version', $version);

			$templateMgr->display('rtadmin/exportXml.tpl', 'application/xml');
		}
		else Request::redirect(null, 'versions');
	}

	function importVersion() {
		$this->validate();
		$fileField = 'versionFile';
		if (isset($_FILES[$fileField]['tmp_name']) && is_uploaded_file($_FILES[$fileField]['tmp_name'])) {
			$rtAdmin = new HarvesterRTAdmin($archiveId);
			$rtAdmin->importVersion($_FILES[$fileField]['tmp_name']);
		}
		Request::redirect(null, 'versions');
	}

	function restoreVersions($args) {
		$this->validate();

		$archiveId = (int) array_shift($args);

		$rtAdmin = new HarvesterRTAdmin($archiveId);
		$rtAdmin->restoreVersions();

		Request::redirect(null, 'versions');
	}

	function versions($args) {
		$archiveId = (int) array_shift($args);

		$this->validate();
		$this->setupTemplate(true, $archiveId);


		$rtDao =& DAORegistry::getDAO('RTDAO');
		$rangeInfo = PKPHandler::getRangeInfo('versions');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('versions', $rtDao->getVersions($archiveId, $rangeInfo));
		$templateMgr->assign('archiveId', $archiveId);
		$templateMgr->display('rtadmin/versions.tpl');
	}

	function editVersion($args) {
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = (int) array_shift($args);
		$versionId = (int) array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);

		if (isset($version)) {
			import('classes.rt.harvester2.form.VersionForm');
			$this->setupTemplate(true, $archiveId, $version);
			$versionForm = new VersionForm($versionId, $archiveId);
			$versionForm->initData();
			$versionForm->display();
		}
		else Request::redirect(null, 'versions');
	}

	function deleteVersion($args) {
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = (int) array_shift($args);
		$versionId = (int) array_shift($args);

		$rtDao->deleteVersion($versionId, $archiveId);

		Request::redirect(null, 'versions', array($archiveId));
	}

	function saveVersion($args) {
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$versionId = (int) array_shift($args);
		$archiveId = (int) Request::getUserVar('archiveId');
		$version =& $rtDao->getVersion($versionId, $archiveId);

		if (isset($version)) {
			import('classes.rt.harvester2.form.VersionForm');
			$versionForm = new VersionForm($versionId, $archiveId);
			$versionForm->readInputData();
			$versionForm->execute();
		}

		Request::redirect(null, 'versions', $archiveId);
	}
}

?>
