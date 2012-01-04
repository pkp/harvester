<?php

/**
 * @file pages/submitter/SubmitterHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.submitter
 * @class SubmitterHandler
 *
 * Handle requests for adding new archives.
 *
 */

// $Id$


import('classes.handler.Handler');

class SubmitterHandler extends Handler {
	/**
	 * Display a list of the user's archives
	 */
	function index() {
		$this->validate();
		$this->setupTemplate();

		$user =& Request::getUser();
		
		$sort = Request::getUserVar('sort');
		$sort = isset($sort) ? $sort : 'title';
		$sortDirection = Request::getUserVar('sortDirection');

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$archives =& $archiveDao->getArchivesByUserId($user->getId(), null, $sort, $sortDirection);

		// Load the harvester plugins so we can display names.
		$plugins =& PluginRegistry::loadCategory('harvesters');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('harvesters', $plugins);
		$templateMgr->assign_by_ref('archives', $archives);
		$templateMgr->assign('sort', $sort);
		$templateMgr->assign('sortDirection', $sortDirection);
		$templateMgr->display('submitter/archives.tpl');
	}

	/**
	 * Display add page.
	 */
	function createArchive() {
		$this->editArchive();
	}

	/**
	 * Display add/edit page.
	 */
	function editArchive($args = array()) {
		$archiveId = null;
		if (is_array($args) && !empty($args)) $archiveId = (int) array_shift($args);
		$this->validate($archiveId);
		$this->setupTemplate(true);

		$site =& Request::getSite();
		if (!$site->getSetting('enableSubmit')) Request::redirect('index');

		import('classes.admin.form.ArchiveForm');

		$archiveForm = new ArchiveForm($archiveId);
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

		$this->validate($archiveId);
		$this->setupTemplate(true);

		import('classes.admin.form.ArchiveForm');

		$archiveForm = new ArchiveForm($archiveId);
		$archiveForm->initData();
		$archiveForm->readInputData();

		$dataModified = false;

		if (Request::getUserVar('uploadArchiveImage')) {
			if (!$archiveForm->uploadArchiveImage()) {
				$archiveForm->addError('archiveImage', __('archive.image.profileImageInvalid'));
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
			$archiveForm->display();
		}
	}

	/**
	 * Delete an archive.
	 * @param $args array first parameter is the ID of the archive to delete
	 */
	function deleteArchive($args) {
		$archiveId = (int) array_shift($args);
		$this->validate($archiveId);

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');

		// Disable timeout, as this operation may take
		// a long time.
		@set_time_limit(0);

		$archiveDao->deleteArchiveById($archiveId);

		Request::redirect('submitter');
	}

	/**
	 * Perform plugin-specific management functions.
	 */
	function plugin($args) {
		$category = array_shift($args);
		$plugin = array_shift($args);
		$verb = array_shift($args);

		$this->validate();
		$this->setupTemplate(true);

		$plugins =& PluginRegistry::loadCategory($category);
		if (!isset($plugins[$plugin]) || !$plugins[$plugin]->allowSubmitterManagement($verb, $args) || !$plugins[$plugin]->manage($verb, $args)) {
			Request::redirect(null, 'plugins');
		}
	}
	
	function validate ($archiveId = null) {
		$user =& Request::getUser();
		if ($archiveId !== null) {
			$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
			$archive =& $archiveDao->getArchive((int) $archiveId, false);

			if (!$archive) Request::redirect('index');
			if ($archive->getUserId() != $user->getId()) Request::redirect('index');

			$this->archive =& $archive;
			return true;
		}
		return false;
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		parent::setupTemplate();
		$templateMgr =& TemplateManager::getManager();
		$pageHierarchy = array(
			array(Request::url('submitter'), 'user.role.submitter')
		);
		if ($subclass) {
			$pageHierarchy[] = array(Request::url('submitter'), 'admin.archives');
		}
		$templateMgr->assign('pageHierarchy', $pageHierarchy);
	}
}

?>
