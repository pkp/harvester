<?php

/**
 * @file pages/admin/AdminArchiveHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.admin
 * @class AdminArchiveHandler
 *
 * Handle requests for archive management in site administration. 
 *
 */

// $Id$

import('pages.admin.AdminHandler');

class AdminArchiveHandler extends AdminHandler {

	/**
	 * Display a list of the archives hosted on the site.
	 * @param $args array
	 * @param $request object
	 */
	function archives($args, &$request) {
		$this->validate();
		$this->setupTemplate();

		$rangeInfo = PKPHandler::getRangeInfo('archives');
		
		$sort = $request->getUserVar('sort');
		$sort = isset($sort) ? $sort : 'title';
		$sortDirection = $request->getUserVar('sortDirection');

		// Load the harvester plugins so we can display names.
		$plugins =& PluginRegistry::loadCategory('harvesters');

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$archives =& $archiveDao->getArchives(false, $rangeInfo, $sort, $sortDirection);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('archives', $archives);
		$templateMgr->assign('harvesters', $plugins);
		if ($rangeInfo) $templateMgr->assign('archivesPage', $rangeInfo->getPage());
		$templateMgr->assign('sort', $sort);
		$templateMgr->assign('sortDirection', $sortDirection);
		$templateMgr->display('admin/archives.tpl');
	}

	/**
	 * Display form to create a new archive.
	 */
	function createArchive() {
		$this->editArchive();
	}

	/**
	 * Display form to create/edit a archive.
	 * @param $args array optional, if set the first parameter is the ID of the archive to edit
	 */
	function editArchive($args = array()) {
		$this->validate();
		$this->setupTemplate(true);

		import('classes.admin.form.ArchiveForm');

		$archiveForm = new ArchiveForm(!isset($args) || empty($args) ? null : (int) $args[0], true);
		$archiveForm->initData();
		$archiveForm->display();
	}

	/**
	 * Save changes to a archive's settings.
	 * @param $args array
	 * @param $request object
	 */
	function updateArchive($args, &$request) {
		$this->validate();
		$this->setupTemplate();

		import('classes.admin.form.ArchiveForm');

		$archiveId = (int) $request->getUserVar('archiveId');

		$archiveForm = new ArchiveForm($archiveId, true);
		$archiveForm->initData();
		$archiveForm->readInputData();

		$dataModified = false;

		if ($request->getUserVar('uploadArchiveImage')) {
			if (!$archiveForm->uploadArchiveImage()) {
				$archiveForm->addError('archiveImage', __('archive.image.profileImageInvalid'));
			}
			$dataModified = true;
		} else if ($request->getUserVar('deleteArchiveImage')) {
			$archiveForm->deleteArchiveImage();
			$dataModified = true;
		}

		if (!$dataModified && $archiveForm->validate()) {
			$archiveForm->execute();
			import('lib.pkp.classes.notification.NotificationManager');
			$notificationManager = new NotificationManager();
			$notificationManager->createTrivialNotification('notification.notification', 'common.changesSaved');
			$request->redirect(null, 'archives');
		} else {
			$this->setupTemplate(true);
			$archiveForm->display();
		}
	}

	/**
	 * Delete a archive.
	 * @param $args array first parameter is the ID of the archive to delete
	 * @param $request object
	 */
	function deleteArchive($args, &$request) {
		$this->validate();

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');

		// Disable timeout, as this operation may take
		// a long time.
		@set_time_limit(0);

		if (isset($args) && isset($args[0])) {
			$archiveId = $args[0];
			$archiveDao->deleteArchiveById($archiveId);
		}

		$request->redirect('admin', 'archives', null, array('archivesPage' => $request->getUserVar('archivesPage')));
	}

	/**
	 * Manage an archive.
	 * @param $args array
	 * @param $request object
	 */
	function manage($args, &$request) {
		$this->validate();
		$this->setupTemplate(true);

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');

		if (isset($args) && isset($args[0])) {
			$archiveId = $args[0];
			$archive =& $archiveDao->getArchive($archiveId, false);
			if ($archive) {
				$harvesterPlugin =& $archive->getHarvesterPlugin();
				$harvesterPlugin->displayManagementPage($archive);
				return;
			}
		}
		$request->redirect('admin', 'archives');
	}

	/**
	 * Update the metadata index for an archive.
	 * @param $args array
	 * @param $request object
	 */
	function updateIndex($args, &$request) {
		$this->validate();
		$this->setupTemplate(true);

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');

		if (isset($args) && isset($args[0])) {
			$archiveId = (int) $args[0];
			$archive =& $archiveDao->getArchive($archiveId, false);
			if (!$archive) $request->redirect('admin', 'archives');

			// Disable timeout, as this operation may take
			// a long time.
			@set_time_limit(0);

			// Get the harvester for this archive
			$plugins =& PluginRegistry::loadCategory('harvesters');
			$pluginName = $archive->getHarvesterPluginName();
			if (!isset($plugins[$pluginName])) $request->redirect('admin', 'manage', $archive->getArchiveId());
			$plugin = $plugins[$pluginName];
			$params = $plugin->readUpdateParams($archive);

			if ($plugin->updateIndex($archive, $params)) {
				$recordDao =& DAORegistry::getDAO('RecordDAO');
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign('messageTranslated',
					__('admin.archive.manage.updateIndex.success', array(
						'recordCount' => $recordDao->getRecordCount($archiveId)
					))
				);
				$templateMgr->assign('backLink', $request->url('admin', 'archives'));
				$templateMgr->assign('backLinkLabel', 'admin.archives');
				$templateMgr->assign('pageTitle', 'admin.archives.manage.updateIndex');
				return $templateMgr->display('common/message.tpl');
			} else {
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign('errors', array_unique($plugin->getErrors()));
				$templateMgr->assign('archiveId', $archiveId);
				return $templateMgr->display('admin/updateFailed.tpl');
			}
			$request->redirect('admin', 'manage', $archiveId);
		}
		$request->redirect('admin', 'archives');
	}

	/**
	 * Flush the metadata index for an archive.
	 * @param $args array
	 * @param $request object
	 */
	function flushIndex($args, &$request) {
		$this->validate();
		$this->setupTemplate(true);

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');

		if (isset($args) && isset($args[0])) {
			$archiveId = (int) $args[0];
			$archive =& $archiveDao->getArchive($archiveId, false);
			if ($archive) {
				$recordDao =& DAORegistry::getDAO('RecordDAO');
				$recordDao->deleteRecordsByArchiveId($archive->getArchiveId());
				$archive->setLastIndexedDate(null);
				$archive->updateRecordCount();
			}
			$request->redirect('admin', 'manage', $archiveId);
		}
		$request->redirect('admin', 'archives');
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		parent::setupTemplate();
		$templateMgr =& TemplateManager::getManager();
		$pageHierarchy = array(
			array(Request::url('admin'), 'admin.siteAdmin')
		);
		if ($subclass) {
			$pageHierarchy[] = array(Request::url('admin', 'archives'), 'admin.archives');
		}
		$templateMgr->assign('pageHierarchy', $pageHierarchy);
	}
}

?>
