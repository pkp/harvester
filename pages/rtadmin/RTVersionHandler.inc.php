<?php

/**
 * @file pages/rtadmin/RTVersionHandler.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.rtadmin
 * @class RTVersionHandler
 *
 * Handle Reading Tools administration requests -- setup section.
 *
 */

// $Id$


import('rt.harvester2.HarvesterRTAdmin');

class RTVersionHandler extends RTAdminHandler {
	function createVersion($args) {
		RTAdminHandler::validate();

		$rtDao = &DAORegistry::getDAO('RTDAO');

		import('rt.harvester2.form.VersionForm');
		$archiveId = Request::getUserVar('archiveId');
		$versionForm = &new VersionForm(null, $archiveId);

		if (isset($args[0]) && $args[0]=='save') {
			$versionForm->readInputData();
			$versionForm->execute();
			Request::redirect(null, 'versions');
		} else {
			RTAdminHandler::setupTemplate(true, $archiveId);
			$versionForm->display();
		}
	}

	function exportVersion($args) {
		RTAdminHandler::validate();

		$rtDao = &DAORegistry::getDAO('RTDAO');

		$versionId = isset($args[0])?$args[0]:0;
		$version = &$rtDao->getVersion($versionId);

		if ($version) {
			$templateMgr = &TemplateManager::getManager();
			$templateMgr->assign_by_ref('version', $version);

			$templateMgr->display('rtadmin/exportXml.tpl', 'application/xml');
		}
		else Request::redirect(null, 'versions');
	}

	function importVersion() {
		RTAdminHandler::validate();
		$fileField = 'versionFile';
		if (isset($_FILES[$fileField]['tmp_name']) && is_uploaded_file($_FILES[$fileField]['tmp_name'])) {
			$rtAdmin = &new HarvesterRTAdmin($archiveId);
			$rtAdmin->importVersion($_FILES[$fileField]['tmp_name']);
		}
		Request::redirect(null, 'versions');
	}

	function restoreVersions($args) {
		RTAdminHandler::validate();

		$archiveId = array_shift($args);

		$rtAdmin = &new HarvesterRTAdmin($archiveId);
		$rtAdmin->restoreVersions();

		Request::redirect(null, 'versions');
	}

	function versions($args) {
		$archiveId = array_shift($args);

		RTAdminHandler::validate();
		RTAdminHandler::setupTemplate(true, $archiveId);


		$rtDao = &DAORegistry::getDAO('RTDAO');
		$rangeInfo = PKPHandler::getRangeInfo('versions');

		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign_by_ref('versions', $rtDao->getVersions($archiveId, $rangeInfo));
		$templateMgr->assign('archiveId', $archiveId);
		$templateMgr->display('rtadmin/versions.tpl');
	}

	function editVersion($args) {
		RTAdminHandler::validate();

		$rtDao = &DAORegistry::getDAO('RTDAO');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);

		$version = &$rtDao->getVersion($versionId, $archiveId);

		if (isset($version)) {
			import('rt.harvester2.form.VersionForm');
			RTAdminHandler::setupTemplate(true, $archiveId, $version);
			$versionForm = &new VersionForm($versionId, $archiveId);
			$versionForm->initData();
			$versionForm->display();
		}
		else Request::redirect(null, 'versions');
	}

	function deleteVersion($args) {
		RTAdminHandler::validate();

		$rtDao = &DAORegistry::getDAO('RTDAO');

		$versionId = isset($args[0])?$args[0]:0;

		$rtDao->deleteVersion($versionId, $archiveId);

		Request::redirect(null, 'versions');
	}

	function saveVersion($args) {
		RTAdminHandler::validate();

		$rtDao = &DAORegistry::getDAO('RTDAO');

		$versionId = isset($args[0])?$args[0]:0;
		$archiveId = Request::getUserVar('archiveId');
		$version = &$rtDao->getVersion($versionId, $archiveId);

		if (isset($version)) {
			import('rt.harvester2.form.VersionForm');
			$versionForm = &new VersionForm($versionId, $archiveId);
			$versionForm->readInputData();
			$versionForm->execute();
		}

		Request::redirect(null, 'versions', $archiveId);
	}
}

?>
