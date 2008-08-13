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


import('core.Handler');

class SubmitterHandler extends Handler {
	/**
	 * Display add page.
	 */
	function index() {
		$site =& Request::getSite();
		if (!$site->getSetting('enableSubmit')) Request::redirect('index');

		SubmitterHandler::validate();
		SubmitterHandler::setupTemplate();

		import('admin.form.ArchiveForm');

		$archiveForm =& new ArchiveForm(!isset($args) || empty($args) ? null : (int) $args[0]);
		$archiveForm->initData();
		$archiveForm->display();
	}

	/**
	 * Save changes to a archive's settings.
	 */
	function updateArchive() {
		$site =& Request::getSite();
		if (!$site->getSetting('enableSubmit')) Request::redirect('index');

		SubmitterHandler::validate();

		import('admin.form.ArchiveForm');

		$archiveId = (int) Request::getUserVar('archiveId');

		$archiveForm =& new ArchiveForm($archiveId);
		$archiveForm->initData();
		$archiveForm->readInputData();

		if ($archiveForm->validate()) {
			$archiveForm->execute();
			Request::redirect('index', null);
		} else {
			SubmitterHandler::setupTemplate(true);
			$archiveForm->display();
		}
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
