<?php

/**
 * AdminArchiveHandler.inc.php
 *
 * Copyright (c) 2003-2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.admin
 *
 * Handle requests for archive management in site administration. 
 *
 * $Id$
 */

class AdminArchiveHandler extends AdminHandler {

	/**
	 * Display a list of the archives hosted on the site.
	 */
	function archives() {
		parent::validate();
		parent::setupTemplate(true);
		
		$rangeInfo = Handler::getRangeInfo('archives');

		// Load the harvester plugins so we can display names.
		$plugins =& PluginRegistry::loadCategory('harvesters');

		$archiveDao = &DAORegistry::getDAO('ArchiveDAO');
		$archives = &$archiveDao->getArchives($rangeInfo);
		
		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign_by_ref('archives', $archives);
		$templateMgr->assign('helpTopicId', 'site.siteManagement');
		$templateMgr->assign('harvesters', $plugins);
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
		parent::validate();
		parent::setupTemplate(true);
		
		import('admin.form.ArchiveForm');
		
		$settingsForm = &new ArchiveForm(!isset($args) || empty($args) ? null : (int) $args[0]);
		$settingsForm->initData();
		$settingsForm->display();
	}
	
	/**
	 * Save changes to a archive's settings.
	 */
	function updateArchive() {
		parent::validate();
		
		import('admin.form.ArchiveForm');
		
		$settingsForm = &new ArchiveForm(Request::getUserVar('archiveId'));
		$settingsForm->initData();
		$settingsForm->readInputData();
		
		if ($settingsForm->validate()) {
			$settingsForm->execute();
			Request::redirect('admin', 'archives');
			
		} else {
			parent::setupTemplate(true);
			$settingsForm->display();
		}
	}
	
	/**
	 * Delete a archive.
	 * @param $args array first parameter is the ID of the archive to delete
	 */
	function deleteArchive($args) {
		parent::validate();
		
		$archiveDao = &DAORegistry::getDAO('ArchiveDAO');
		
		if (isset($args) && !empty($args) && !empty($args[0])) {
			$archiveId = $args[0];
			$archiveDao->deleteArchiveById($archiveId);
		}
		
		Request::redirect('admin', 'archives');
	}
	
	/**
	 * Manage an archive.
	 */
	function manage($args) {
		parent::validate();
		parent::setupTemplate(true);
		
		$archiveDao = &DAORegistry::getDAO('ArchiveDAO');
		
		if (isset($args) && !empty($args) && !empty($args[0])) {
			$archiveId = $args[0];
			$archive =& $archiveDao->getArchive($archiveId);
			if ($archive) {
				$recordDao =& DAORegistry::getDAO('RecordDAO');

				$templateMgr = &TemplateManager::getManager();
				$templateMgr->assign('numRecords', $recordDao->getRecordCount($archiveId));
				$templateMgr->assign('lastIndexed', $archive->getLastIndexedDate());
				$templateMgr->assign('title', $archive->getTitle());
				$templateMgr->assign('archiveId', $archive->getArchiveId());
				$templateMgr->display('admin/manage.tpl');
				return;
			}
		}
		Request::redirect('admin', 'archives');
	}

	/**
	 * Update the metadata index for an archive.
	 */
	function updateIndex($args) {
		parent::validate();
		parent::setupTemplate(true);
		
		$archiveDao = &DAORegistry::getDAO('ArchiveDAO');
		
		if (isset($args) && !empty($args) && !empty($args[0])) {
			$archiveId = $args[0];
			$archive =& $archiveDao->getArchive($archiveId);
			if (!$archive) Request::redirect('admin', 'archives');

			// Disable timeout, as this operation may take
			// a long time.
			set_time_limit(0);

			// Get the harvester for this archive
			$plugins =& PluginRegistry::loadCategory('harvesters');
			$pluginName = $archive->getHarvesterPlugin();
			if (!isset($plugins[$pluginName])) Request::redirect('admin', 'manage', $archive->getArchiveId());
			$plugin = $plugins[$pluginName];

			if ($plugin->updateIndex($archive)) {
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
				$templateMgr->assign('errors', $plugin->getErrors());
				$templateMgr->assign('archiveId', $archiveId);
				return $templateMgr->display('admin/updateFailed.tpl');
			}
		}
		Request::redirect('admin', 'archives');
	}

	/**
	 * Flush the metadata index for an archive.
	 */
	function flushIndex($args) {
		parent::validate();
		parent::setupTemplate(true);
		
		$archiveDao = &DAORegistry::getDAO('ArchiveDAO');
		
		if (isset($args) && !empty($args) && !empty($args[0])) {
			$archiveId = $args[0];
			$archive =& $archiveDao->getArchive($archiveId);
			if ($archive) {
				$recordDao =& DAORegistry::getDAO('RecordDAO');
				$recordDao->deleteRecordsByArchiveId($archive->getArchiveId());
				$archive->setLastIndexedDate(null);
			}
		}
		Request::redirect('admin', 'archives');
	}
}

?>
