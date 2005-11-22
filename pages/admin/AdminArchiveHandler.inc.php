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

		$archiveDao = &DAORegistry::getDAO('ArchiveDAO');
		$archives = &$archiveDao->getArchives($rangeInfo);
		
		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign_by_ref('archives', $archives);
		$templateMgr->assign('helpTopicId', 'site.siteManagement');
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
		
		$settingsForm = &new ArchiveForm(!isset($args) || empty($args) ? null : $args[0]);
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
		$settingsForm->readInputData();
		
		if ($settingsForm->validate()) {
			$settingsForm->execute();
			Request::redirect('admin/archives');
			
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
			if ($archiveDao->deleteArchiveById($archiveId)) {
				// Delete archive file tree
				// FIXME move this somewhere better.
				import('file.FileManager');
				$fileManager = &new FileManager();

				$archivePath = Config::getVar('files', 'files_dir') . '/archives/' . $archiveId;
				$fileManager->rmtree($archivePath);

				import('file.PublicFileManager');
				$publicFileManager = &new PublicFileManager();
				$publicFileManager->rmtree($publicFileManager->getArchiveFilesPath($archiveId));
			}
		}
		
		Request::redirect('admin/archives');
	}
	
}

?>
