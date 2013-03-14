<?php

/**
 * @file pages/user/UserHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserHandler
 * @ingroup pages_user
 *
 * @brief Handle requests for user functions.
 */

import('lib.pkp.pages.user.PKPUserHandler');

class UserHandler extends PKPUserHandler {
	/**
	 * Constructor
	 */
	function UserHandler() {
		parent::PKPUserHandler();
	}

	/**
	 * Display user index page.
	 */
	function index($args, &$request) {
		$this->validate();

		$templateMgr =& TemplateManager::getManager($request);
		$roleDao = DAORegistry::getDAO('RoleDAO');
		$user =& $request->getUser();
		$site =& $request->getSite();

		$this->setupTemplate($request);

		$templateMgr->assign('isSiteAdmin', Validation::isSiteAdmin());
		$templateMgr->assign('userRoles', $roleDao->getRolesByUserId($user->getId()));
		$templateMgr->assign('enableSubmit', $site->getSetting('enableSubmit'));
		$templateMgr->display('user/index.tpl');
	}

	/**
	 * Become a given role.
	 */
	function become($args, &$request) {
		parent::validate(true);

		$user =& $request->getUser();

		switch (array_shift($args)) {
			case 'submitter':
				$roleId = ROLE_ID_SUBMITTER;
				$setting = 'enableSubmit';
				$deniedKey = 'user.noRoles.enableSubmitClosed';
				break;
			default:
				$request->redirect('index');
		}

		$site =& $request->getSite();
		if ($site->getSetting($setting)) {
			$role = new Role();
			$role->setRoleId($roleId);
			$role->setUserId($user->getId());

			$roleDao = DAORegistry::getDAO('RoleDAO');
			$roleDao->insertRole($role);
			$request->redirectUrl(Request::getUserVar('source'));
		} else {
			$templateMgr =& TemplateManager::getManager($request);
			$templateMgr->assign('message', $deniedKey);
			return $templateMgr->display('common/message.tpl');
		}
	}

	/**
	 * Validate that user is logged in.
	 * Redirects to login form if not logged in.
	 * @param $loginCheck boolean check if user is logged in
	 */
	function validate($loginCheck = true) {
		parent::validate();
		if ($loginCheck && !Validation::isLoggedIn()) {
			Validation::redirectLogin();
		}
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($request, $subclass = false) {
		parent::setupTemplate($request);
		$templateMgr =& TemplateManager::getManager($request);
		if ($subclass) {
			$templateMgr->assign('pageHierarchy', array(array($request->url(null, 'user'), 'navigation.user')));
		}
	}

	/**
	 * View the public user profile for a user, specified by user ID,
	 * if that user should be exposed for public view.
	 */
	function viewPublicProfile($args, &$request) {
		$this->validate(false);
		$templateMgr =& TemplateManager::getManager($request);
		$userId = (int) array_shift($args);

		$accountIsVisible = false;

		// Ensure that the user's profile info should be exposed:

		$commentDao = DAORegistry::getDAO('CommentDAO');
		if ($commentDao->attributedCommentsExistForUser($userId)) {
			// At least one comment is attributed to the user
			$accountIsVisible = true;
		}

		if (!$accountIsVisible) $request->redirect(null, 'index');

		$userDao = DAORegistry::getDAO('UserDAO');
		$user =& $userDao->getById($userId);

		$templateMgr->assign_by_ref('user', $user);
		$templateMgr->display('user/publicProfile.tpl');
	}
}

?>
