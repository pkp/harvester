<?php

/**
 * @file pages/user/RegistrationHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RegistrationHandler
 * @ingroup pages_user
 *
 * @brief Handle requests for user registration. 
 */

// $Id$

import('pages.user.UserHandler');

class RegistrationHandler extends UserHandler {

	/**
	 * Display registration form for new users.
	 */
	function register() {
		$this->validate();
		$this->setupTemplate(true);

		import('classes.user.form.RegistrationForm');

		if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
			$regForm = new RegistrationForm();
		} else {
			$regForm =& new RegistrationForm();
		}
		if ($regForm->isLocaleResubmit()) {
			$regForm->readInputData();
		} else {
			$regForm->initData();
		}
		$regForm->display();
	}

	/**
	 * Validate user registration information and register new user.
	 */
	function registerUser() {
		$this->validate();
		import('classes.user.form.RegistrationForm');

		if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
			$regForm = new RegistrationForm();
		} else {
			$regForm =& new RegistrationForm();
		}
		$regForm->readInputData();

		if ($regForm->validate()) {
			$regForm->execute();
			if (Config::getVar('email', 'require_validation')) {
				// Send them home; they need to deal with the
				// registration email.
				Request::redirect(null, 'index');
			}

			$reason = null;

			Validation::login($regForm->getData('username'), $regForm->getData('password'), $reason);

			if ($reason !== null) {
				$this->setupTemplate(true);
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign('pageTitle', 'user.login');
				$templateMgr->assign('errorMsg', $reason==''?'user.login.accountDisabled':'user.login.accountDisabledWithReason');
				$templateMgr->assign('errorParams', array('reason' => $reason));
				$templateMgr->assign('backLink', Request::url('login'));
				$templateMgr->assign('backLinkLabel', 'user.login');
				return $templateMgr->display('common/error.tpl');
			}
			if($source = Request::getUserVar('source'))
				Request::redirectUrl($source);

			else Request::redirect('login');

		} else {
			$this->setupTemplate(true);
			$regForm->display();
		}
	}

	/**
	 * Show error message if user registration is not allowed.
	 */
	function registrationDisabled() {
		$this->setupTemplate(true);
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageTitle', 'user.register');
		$templateMgr->assign('errorMsg', 'user.register.registrationDisabled');
		$templateMgr->assign('backLink', Request::url('login'));
		$templateMgr->assign('backLinkLabel', 'user.login');
		$templateMgr->display('common/error.tpl');
	}

	/**
	 * Check credentials and activate a new user
	 * @author Marc Bria <marc.bria@uab.es>
	 */
	function activateUser($args) {
		$username = array_shift($args);
		$accessKeyCode = array_shift($args);

		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& $userDao->getUserByUsername($username);
		if (!$user) Request::redirect(null, 'login');

		// Checks user & token
		import('lib.pkp.classes.security.AccessKeyManager');
		$accessKeyManager = new AccessKeyManager();
		$accessKeyHash = AccessKeyManager::generateKeyHash($accessKeyCode);
		$accessKey =& $accessKeyManager->validateKey(
			'RegisterContext',
			$user->getId(),
			$accessKeyHash
		);

		if ($accessKey != null && $user->getDateValidated() === null) {
			// Activate user
			$user->setDisabled(false);
			$user->setDisabledReason('');
			$user->setDateValidated(Core::getCurrentDate());
			$userDao->updateObject($user);

			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign('message', 'user.login.activated');
			return $templateMgr->display('common/message.tpl');
		}
		Request::redirect(null, 'login');
	}

	/**
	 * Validation check.
	 * Checks if site allows user registration.
	 */	
	function validate() {
		parent::validate();
		$site =& Request::getSite();
		if (!$site->getSetting('enableSubmit')) {
			// Users cannot register themselves
			$this->registrationDisabled();
			exit;
		}
	}
}

?>
