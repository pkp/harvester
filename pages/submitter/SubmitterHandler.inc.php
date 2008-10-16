<?php

/**
 * @file pages/submitter/SubmitterHandler.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.submitter
 * @class SubmitterHandler
 *
 * Handle requests for adding new archives.
 *
 */

// $Id$


import('core.PKPHandler');

class SubmitterHandler extends PKPHandler {
	/**
	 * Display a list of the user's archives
	 */
	function index() {
		SubmitterHandler::validate();
		SubmitterHandler::setupTemplate();

		$user =& Request::getUser();

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$archives =& $archiveDao->getArchivesByUserId($user->getUserId());

		// Load the harvester plugins so we can display names.
		$plugins =& PluginRegistry::loadCategory('harvesters');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('harvesters', $plugins);
		$templateMgr->assign_by_ref('archives', $archives);
		$templateMgr->display('submitter/archives.tpl');
	}

	/**
	 * Display add page.
	 */
	function createArchive() {
		SubmitterHandler::editArchive();
	}

	/**
	 * Display add/edit page.
	 */
	function editArchive($args = array()) {
		$archiveId = null;
		if (is_array($args) && !empty($args)) $archiveId = (int) array_shift($args);
		SubmitterHandler::validate($archiveId);
		SubmitterHandler::setupTemplate();

		$site =& Request::getSite();
		if (!$site->getSetting('enableSubmit')) Request::redirect('index');

		import('admin.form.ArchiveForm');

		$archiveForm =& new ArchiveForm($archiveId);
		$archiveForm->initData();
		$archiveForm->display();
	}

	/**
	 * Save changes to an archive's settings.
	 */
	function updateArchive() {
		$archiveId = Request::getUserVar('archiveId');
		if (empty($archiveId)) $archiveId = null;
		else $archiveId = (int) $archiveId;

		SubmitterHandler::validate($archiveId);

		import('admin.form.ArchiveForm');


		$archiveForm = &new ArchiveForm($archiveId);
		$archiveForm->initData();
		$archiveForm->readInputData();

		$dataModified = false;

		if (Request::getUserVar('uploadArchiveImage')) {
			if (!$archiveForm->uploadArchiveImage()) {
				$archiveForm->addError('archiveImage', Locale::translate('archive.image.profileImageInvalid'));
			}
			$dataModified = true;
		} else if (Request::getUserVar('deleteArchiveImage')) {
			$archiveForm->deleteArchiveImage();
			$dataModified = true;
		}

		if (!$dataModified && $archiveForm->validate()) {
			$archiveForm->execute();
			Request::redirect('submitter', $archiveId);

		} else {
			SubmitterHandler::setupTemplate(true);
			$archiveForm->display();
		}
	}

	/**
	 * Delete an archive.
	 * @param $args array first parameter is the ID of the archive to delete
	 */
	function deleteArchive($args) {
		$archiveId = (int) array_shift($args);
		list($archive) = SubmitterHandler::validate($archiveId);

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');

		// Disable timeout, as this operation may take
		// a long time.
		@set_time_limit(0);

		$archiveDao->deleteArchiveById($archiveId);

		Request::redirect('submitter');
	}

	function validate ($archiveId = null) {
		$returner = null;
		$user =& Request::getUser();
		if ($archiveId !== null) {
			$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
			$archive =& $archiveDao->getArchive((int) $archiveId);

			if (!$archive) Request::redirect('index');
			if ($archive->getUserId() != $user->getUserId()) Request::redirect('index');

			$returner = array(&$archive);
		}
		return $returner;
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		$templateMgr =& TemplateManager::getManager();
		if ($subclass) {
			$templateMgr->assign('pageHierarchy',
				array(array(Request::url('submitter'), 'navigation.addArchive'))
			);
		}
	}


}

?>
