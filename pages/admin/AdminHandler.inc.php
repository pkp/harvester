<?php

/**
 * @file pages/admin/AdminHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.admin
 * @class AdminHandler
 *
 * Handle requests for site administration functions.
 *
 */

import('classes.handler.Handler');

class AdminHandler extends Handler {
	/**
	 * Constructor
	 **/
	function AdminHandler() {
		parent::Handler();
	}

	/**
	 * Display site admin index page.
	 */
	function index($args, &$request) {
		$this->validate();
		$this->setupTemplate($request);

		$templateMgr =& TemplateManager::getManager();

		// Display a warning message if there is a new version of OJS available
		$newVersionAvailable = false;
		if (Config::getVar('general', 'show_upgrade_warning')) {
			import('lib.pkp.classes.site.VersionCheck');
			if($latestVersion = VersionCheck::checkIfNewVersionExists()) {
				$newVersionAvailable = true;
				$templateMgr->assign('latestVersion', $latestVersion);
				$currentVersion =& VersionCheck::getCurrentDBVersion();
				$templateMgr->assign('currentVersion', $currentVersion->getVersionString());
			}
		}

		$templateMgr->assign('newVersionAvailable', $newVersionAvailable);
		$templateMgr->display('admin/index.tpl');
	}

	/**
	 * Validate that user has admin privileges
	 * Redirects to the user index page if not properly authenticated.
	 */
	function validate() {
		$this->addCheck(new HandlerValidatorRoles($this, true, null, null, array(ROLE_ID_SITE_ADMIN)));
		parent::validate();
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($request, $subclass = false) {
		parent::setupTemplate($request);
		$templateMgr =& TemplateManager::getManager();
		if ($subclass) {
			$templateMgr->assign('pageHierarchy',
				array(array($request->url('admin'), 'admin.siteAdmin'))
			);
		}
	}

	//
	// Layout
	//

	function layout($args, &$request) {
		$this->setupTemplate($request);
		$this->validate();

		import('classes.admin.form.LayoutForm');
		$layoutForm = new LayoutForm();
		if ($layoutForm->isLocaleResubmit()) {
			$layoutForm->readInputData();
		} else {
			$layoutForm->initData();
		}
		$layoutForm->display();
	}

	function saveLayout($args, &$request) {
		$this->setupTemplate($request);
		$this->validate();

		import('classes.admin.form.LayoutForm');
		$layoutForm = new LayoutForm();
		$layoutForm->readInputData();
		if ($layoutForm->validate()) {
			$layoutForm->execute();
			$request->redirect('admin');
		} else {
			$layoutForm->display();
		}
	}
}

?>
