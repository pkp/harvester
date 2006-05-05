<?php

/**
 * @file AddHandler.inc.php
 *
 * Copyright (c) 2005-2006 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.add
 * @class AddHandler
 *
 * Handle requests for adding new archives.
 *
 * $Id$
 */

class AddHandler extends Handler {
	/**
	 * Display add page.
	 */
	function index() {
		AddHandler::validate();
		AddHandler::setupTemplate();

		import('admin.form.ArchiveForm');
		
		$archiveForm = &new ArchiveForm(!isset($args) || empty($args) ? null : (int) $args[0]);
		$archiveForm->initData();
		$archiveForm->display();
	}
	
	/**
	 * Save changes to a archive's settings.
	 */
	function updateArchive() {
		AddHandler::validate();
		
		import('admin.form.ArchiveForm');

		$archiveId = (int) Request::getUserVar('archiveId');

		$archiveForm = &new ArchiveForm($archiveId);
		$archiveForm->initData();
		$archiveForm->readInputData();
		
		if ($archiveForm->validate()) {
			$archiveForm->execute();
			Request::redirect('index', null);
		} else {
			AddHandler::setupTemplate(true);
			$archiveForm->display();
		}
	}
	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		$templateMgr = &TemplateManager::getManager();
		if ($subclass) {
			$templateMgr->assign('pageHierarchy',
				array(array(Request::url('add'), 'navigation.addArchive'))
			);
		}
	}
	
	
}

?>
