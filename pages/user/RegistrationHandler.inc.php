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


import('pages.user.UserHandler');

class RegistrationHandler extends UserHandler {

	/**
	 * Display registration form for new users.
	 */
	function register($args, &$request) {
		$this->validate($request);
		$this->setupTemplate($request, true);

		import('classes.user.form.RegistrationForm');

		$regForm = new RegistrationForm();
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
	function registerUser($args, &$request) {
		$this->validate($request);
		import('classes.user.form.RegistrationForm');

		$regForm = new RegistrationForm();
		$regForm->readInputData();

		if ($regForm->validate()) {
			$regForm->execute();
			if (Config::getVar('email', 'require_validation')) {
				// Send them home; they need to deal with the
				// registration email.
				$request->redirect(null, 'index');
			}

			$reason = null;

			Validation::login($regForm->getData('username'), $regForm->getData('password'), $reason);

			if ($reason !== null) {
				$this->setupTemplate($request, true);
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign('pageTitle', 'user.login');
				$templateMgr->assign('errorMsg', $reason==''?'user.login.accountDisabled':'user.login.accountDisabledWithReason');
				$templateMgr->assign('errorParams', array('reason' => $reason));
				$templateMgr->assign('backLink', $request->url('login'));
				$templateMgr->assign('backLinkLabel', 'user.login');
				return $templateMgr->display('common/error.tpl');
			}
			if($source = $request->getUserVar('source'))
				$request->redirectUrl($source);
			else $request->redirect('login');
		} else {
			$this->setupTemplate($request, true);
			$regForm->display();
		}
	}

	/**
	 * Show error message if user registration is not allowed.
	 */
	function registrationDisabled($args, &$request) {
		$this->setupTemplate($request, true);
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageTitle', 'user.register');
		$templateMgr->assign('errorMsg', 'user.register.registrationDisabled');
		$templateMgr->assign('backLink', $request->url('login'));
		$templateMgr->assign('backLinkLabel', 'user.login');
		$templateMgr->display('common/error.tpl');
	}

	/**
	 * Check credentials and activate a new user
	 * @author Marc Bria <marc.bria@uab.es>
	 */
	function activateUser($args, &$request) {
		$username = array_shift($args);
		$accessKeyCode = array_shift($args);

		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& $userDao->getByUsername($username);
		if (!$user) $request->redirect(null, 'login');

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
		$request->redirect(null, 'login');
	}

	/**
	 * Validation check.
	 * Checks if site allows user registration.
	 */	
	function validate($request) {
		parent::validate();
		$site =& $request->getSite();
		if (!$site->getSetting('enableSubmit')) {
			// Users cannot register themselves
			$this->registrationDisabled();
			exit;
		}
	}
}

?>
