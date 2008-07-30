<?php

/**
 * @file pages/admin/AdminArchiveHandler.inc.php
 *
 * Copyright (c) 2005-2007 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.admin
 * @class AdminArchiveHandler
 *
 * Handle requests for archive management in site administration. 
 *
 */

// $Id$


class AdminArchiveHandler extends AdminHandler {

	/**
	 * Display a list of the archives hosted on the site.
	 */
	function archives() {
		AdminArchiveHandler::validate();
		AdminArchiveHandler::setupTemplate();

		$rangeInfo = Handler::getRangeInfo('archives');

		// Load the harvester plugins so we can display names.
		$plugins =& PluginRegistry::loadCategory('harvesters');

		$archiveDao = &DAORegistry::getDAO('ArchiveDAO');
		$archives = &$archiveDao->getArchives(false, $rangeInfo);

		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign_by_ref('archives', $archives);
		$templateMgr->assign('harvesters', $plugins);
		if ($rangeInfo) $templateMgr->assign('archivesPage', $rangeInfo->getPage());
		$templateMgr->display('admin/archives.tpl');
	}

	/**
	 * Display form to create a new archive.
	 */
	function createArchive() {
		AdminArchiveHandler::editArchive();
	}

	/**
	 * Display form to create/edit a archive.
	 * @param $args array optional, if set the first parameter is the ID of the archive to edit
	 */
	function editArchive($args = array()) {
		AdminArchiveHandler::validate();
		AdminArchiveHandler::setupTemplate(true);

		import('admin.form.ArchiveForm');

		$archiveForm = &new ArchiveForm(!isset($args) || empty($args) ? null : (int) $args[0]);
		$archiveForm->initData();
		$archiveForm->display();
	}

	/**
	 * Save changes to a archive's settings.
	 */
	function updateArchive() {
		AdminArchiveHandler::validate();

		import('admin.form.ArchiveForm');

		$archiveId = (int) Request::getUserVar('archiveId');

		$archiveForm = &new ArchiveForm($archiveId);
		$archiveForm->initData();
		$archiveForm->readInputData();

		if ($archiveForm->validate()) {
			$archiveForm->execute();
			Request::redirect('admin', 'manage', $archiveId);

		} else {
			AdminArchiveHandler::setupTemplate(true);
			$archiveForm->display();
		}
	}

	/**
	 * Delete a archive.
	 * @param $args array first parameter is the ID of the archive to delete
	 */
	function deleteArchive($args) {
		AdminArchiveHandler::validate();

		$archiveDao = &DAORegistry::getDAO('ArchiveDAO');

		// Disable timeout, as this operation may take
		// a long time.
		@set_time_limit(0);

		if (isset($args) && isset($args[0])) {
			$archiveId = $args[0];
			$archiveDao->deleteArchiveById($archiveId);
		}

		Request::redirect('admin', 'archives', null, array('archivesPage' => Request::getUserVar('archivesPage')));
	}

	/**
	 * Manage an archive.
	 */
	function manage($args) {
		AdminArchiveHandler::validate();
		AdminArchiveHandler::setupTemplate(true);

		$archiveDao = &DAORegistry::getDAO('ArchiveDAO');

		if (isset($args) && isset($args[0])) {
			$archiveId = $args[0];
			$archive =& $archiveDao->getArchive($archiveId, false);
			if ($archive) {
				$harvesterPlugin =& $archive->getHarvesterPlugin();
				$harvesterPlugin->displayManagementPage($archive);
				return;
			}
		}
		Request::redirect('admin', 'archives');
	}

	/**
	 * Update the metadata index for an archive.
	 */
	function updateIndex($args) {
		AdminArchiveHandler::validate();
		AdminArchiveHandler::setupTemplate(true);

		$archiveDao = &DAORegistry::getDAO('ArchiveDAO');

		if (isset($args) && isset($args[0])) {
			$archiveId = (int) $args[0];
			$archive =& $archiveDao->getArchive($archiveId, false);
			if (!$archive) Request::redirect('admin', 'archives');

			// Disable timeout, as this operation may take
			// a long time.
			@set_time_limit(0);

			// Get the harvester for this archive
			$plugins =& PluginRegistry::loadCategory('harvesters');
			$pluginName = $archive->getHarvesterPluginName();
			if (!isset($plugins[$pluginName])) Request::redirect('admin', 'manage', $archive->getArchiveId());
			$plugin = $plugins[$pluginName];
			$params = $plugin->readUpdateParams($archive);

			if ($plugin->updateIndex($archive, $params)) {
				$recordDao =& DAORegistry::getDAO('RecordDAO');
				$templateMgr = &TemplateManager::getManager();
				$templateMgr->assign('messageTranslated',
					Locale::translate('admin.archive.manage.updateIndex.success', array(
						'recordCount' => $recordDao->getRecordCount($archiveId)
					))
				);
				$templateMgr->assign('backLink', Request::url('admin', 'archives'));
				$templateMgr->assign('backLinkLabel', 'admin.archives');
				$templateMgr->assign('pageTitle', 'admin.archives.manage.updateIndex');
				return $templateMgr->display('common/message.tpl');
			} else {
				$templateMgr = &TemplateManager::getManager();
				$templateMgr->assign('errors', array_unique($plugin->getErrors()));
				$templateMgr->assign('archiveId', $archiveId);
				return $templateMgr->display('admin/updateFailed.tpl');
			}
			Request::redirect('admin', 'manage', $archiveId);
		}
		Request::redirect('admin', 'archives');
	}

	/**
	 * Flush the metadata index for an archive.
	 */
	function flushIndex($args) {
		AdminArchiveHandler::validate();
		AdminArchiveHandler::setupTemplate(true);

		$archiveDao = &DAORegistry::getDAO('ArchiveDAO');

		if (isset($args) && isset($args[0])) {
			$archiveId = (int) $args[0];
			$archive =& $archiveDao->getArchive($archiveId, false);
			if ($archive) {
				$recordDao =& DAORegistry::getDAO('RecordDAO');
				$recordDao->deleteRecordsByArchiveId($archive->getArchiveId());
				$archive->setLastIndexedDate(null);
				$archive->updateRecordCount();
			}
			Request::redirect('admin', 'manage', $archiveId);
		}
		Request::redirect('admin', 'archives');
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		$templateMgr = &TemplateManager::getManager();
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
