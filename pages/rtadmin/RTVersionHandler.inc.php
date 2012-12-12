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



import('classes.rt.harvester2.HarvesterRTAdmin');
import('pages.rtadmin.RTAdminHandler');

class RTVersionHandler extends RTAdminHandler {
	function createVersion($args, &$request) {
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		import('classes.rt.harvester2.form.VersionForm');
		$archiveId = (int) $request->getUserVar('archiveId');
		$versionForm = new VersionForm(null, $archiveId);

		if (isset($args[0]) && $args[0]=='save') {
			$versionForm->readInputData();
			$versionForm->execute();
			$request->redirect(null, 'versions');
		} else {
			$this->setupTemplate($request, true, $archiveId);
			$versionForm->display();
		}
	}

	function exportVersion($args, &$request) {
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$versionId = (int) array_shift($args);
		$version =& $rtDao->getVersion($versionId);

		if ($version) {
			$templateMgr =& TemplateManager::getManager($request);
			$templateMgr->assign_by_ref('version', $version);

			$templateMgr->display('rtadmin/exportXml.tpl', 'application/xml');
		}
		else $request->redirect(null, 'versions');
	}

	function importVersion($args, &$request) {
		$this->validate();
		$fileField = 'versionFile';
		if (isset($_FILES[$fileField]['tmp_name']) && is_uploaded_file($_FILES[$fileField]['tmp_name'])) {
			$rtAdmin = new HarvesterRTAdmin($archiveId);
			$rtAdmin->importVersion($_FILES[$fileField]['tmp_name']);
		}
		$request->redirect(null, 'versions');
	}

	function restoreVersions($args, &$request) {
		$this->validate();

		$archiveId = (int) array_shift($args);

		$rtAdmin = new HarvesterRTAdmin($archiveId);
		$rtAdmin->restoreVersions();

		$request->redirect(null, 'versions');
	}

	function versions($args, &$request) {
		$archiveId = (int) array_shift($args);

		$this->validate();
		$this->setupTemplate($request, true, $archiveId);


		$rtDao = DAORegistry::getDAO('RTDAO');
		$rangeInfo = $this->getRangeInfo($request, 'versions');

		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign_by_ref('versions', $rtDao->getVersions($archiveId, $rangeInfo));
		$templateMgr->assign('archiveId', $archiveId);
		$templateMgr->display('rtadmin/versions.tpl');
	}

	function editVersion($args, &$request) {
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = (int) array_shift($args);
		$versionId = (int) array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);

		if (isset($version)) {
			import('classes.rt.harvester2.form.VersionForm');
			$this->setupTemplate($request, true, $archiveId, $version);
			$versionForm = new VersionForm($versionId, $archiveId);
			$versionForm->initData();
			$versionForm->display();
		}
		else $request->redirect(null, 'versions');
	}

	function deleteVersion($args, &$request) {
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = (int) array_shift($args);
		$versionId = (int) array_shift($args);

		$rtDao->deleteVersion($versionId, $archiveId);

		$request->redirect(null, 'versions', array($archiveId));
	}

	function saveVersion($args, &$request) {
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$versionId = (int) array_shift($args);
		$archiveId = (int) $request->getUserVar('archiveId');
		$version =& $rtDao->getVersion($versionId, $archiveId);

		if (isset($version)) {
			import('classes.rt.harvester2.form.VersionForm');
			$versionForm = new VersionForm($versionId, $archiveId);
			$versionForm->readInputData();
			$versionForm->execute();
		}

		$request->redirect(null, 'versions', $archiveId);
	}
}

?>
