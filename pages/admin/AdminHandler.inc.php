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

// $Id$


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
	function index() {
		$this->validate();
		$this->setupTemplate();

		$templateMgr =& TemplateManager::getManager();
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
	function setupTemplate($subclass = false) {
		parent::setupTemplate();
		$templateMgr =& TemplateManager::getManager();
		if ($subclass) {
			$templateMgr->assign('pageHierarchy',
				array(array(Request::url('admin'), 'admin.siteAdmin'))
			);
		}
	}

	//
	// Layout
	//

	function layout() {
		$this->setupTemplate();
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

	function saveLayout() {
		$this->setupTemplate();
		$this->validate();

		import('classes.admin.form.LayoutForm');
		$layoutForm = new LayoutForm();
		$layoutForm->readInputData();
		if ($layoutForm->validate()) {
			$layoutForm->execute();
			Request::redirect('admin');
		} else {
			$layoutForm->display();
		}
	}

	//
	// Captcha
	//

	function viewCaptcha($args) {
		$captchaId = (int) array_shift($args);
		import('lib.pkp.classes.captcha.CaptchaManager');
		$captchaManager = new CaptchaManager();
		if ($captchaManager->isEnabled()) {
			$captchaDao =& DAORegistry::getDAO('CaptchaDAO');
			$captcha =& $captchaDao->getCaptcha($captchaId);
			if ($captcha) {
				$captchaManager->generateImage($captcha);
				exit();
			}
		}
		Request::redirect('index');
	}
}

?>
