<?php

/**
 * @file pages/admin/PeopleHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PeopleHandler
 * @ingroup pages_admin
 *
 * @brief Handle requests for people management functions. 
 */

// $Id$

import('pages.admin.AdminHandler');

class PeopleHandler extends AdminHandler {

	/**
	 * Display list of people in the selected role.
	 * @param $args array first parameter is the role ID to display
	 */	
	function people($args) {
		$this->validate();
		$this->setupTemplate(true);

		$roleDao =& DAORegistry::getDAO('RoleDAO');

		if (Request::getUserVar('roleSymbolic')!=null) $roleSymbolic = Request::getUserVar('roleSymbolic');
		else $roleSymbolic = isset($args[0])?$args[0]:'all';

		if ($roleSymbolic != 'all' && String::regexp_match_get('/^(\w+)s$/', $roleSymbolic, $matches)) {
			$roleId = $roleDao->getRoleIdFromPath($matches[1]);
			if ($roleId == null) {
				Request::redirect(null, null, 'all');
			}
			$roleName = $roleDao->getRoleName($roleId, true);

		} else {
			$roleId = 0;
			$roleName = 'admin.people.allUsers';
		}
		
		$sort = Request::getUserVar('sort');
		$sort = isset($sort) ? $sort : 'name';
		$sortDirection = Request::getUserVar('sortDirection');

		$templateMgr =& TemplateManager::getManager();

		$searchType = null;
		$searchMatch = null;
		$search = Request::getUserVar('search');
		$searchInitial = Request::getUserVar('searchInitial');
		if (isset($search)) {
			$searchType = Request::getUserVar('searchField');
			$searchMatch = Request::getUserVar('searchMatch');

		} else if (isset($searchInitial)) {
			$searchInitial = String::strtoupper($searchInitial);
			$searchType = USER_FIELD_INITIAL;
			$search = $searchInitial;
		}

		$rangeInfo = PKPHandler::getRangeInfo('users');

		$users =& $roleDao->getUsersByRoleId($roleId, $searchType, $search, $searchMatch, $rangeInfo, $sort, $sortDirection);
		$templateMgr->assign('roleId', $roleId);

		$templateMgr->assign('currentUrl', Request::url(null, 'people', 'all'));
		$templateMgr->assign('roleName', $roleName);
		$templateMgr->assign_by_ref('users', $users);
		$templateMgr->assign_by_ref('thisUser', Request::getUser());

		$templateMgr->assign('searchField', $searchType);
		$templateMgr->assign('searchMatch', $searchMatch);
		$templateMgr->assign('search', $search);
		$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

		$fieldOptions = Array(
			USER_FIELD_FIRSTNAME => 'user.firstName',
			USER_FIELD_LASTNAME => 'user.lastName',
			USER_FIELD_USERNAME => 'user.username',
			USER_FIELD_INTERESTS => 'user.interests',
			USER_FIELD_EMAIL => 'user.email'
		);
		$templateMgr->assign('fieldOptions', $fieldOptions);
		$templateMgr->assign('rolePath', $roleDao->getRolePath($roleId));
		$templateMgr->assign('alphaList', explode(' ', __('common.alphaList')));
		$templateMgr->assign('roleSymbolic', $roleSymbolic);
		$templateMgr->assign('sort', $sort);
		$templateMgr->assign('sortDirection', $sortDirection);
		$templateMgr->display('admin/people/enrollment.tpl');
	}

	/**
	 * Search for users to enroll in a specific role.
	 * @param $args array first parameter is the selected role ID
	 */
	function enrollSearch($args) {
		$this->validate();

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$roleId = (int)(isset($args[0])?$args[0]:Request::getUserVar('roleId'));
		$templateMgr =& TemplateManager::getManager();

		$this->setupTemplate(true);

		$searchType = null;
		$searchMatch = null;
		$search = Request::getUserVar('search');
		$searchInitial = Request::getUserVar('searchInitial');
		if (isset($search)) {
			$searchType = Request::getUserVar('searchField');
			$searchMatch = Request::getUserVar('searchMatch');

		} else if (isset($searchInitial)) {
			$searchInitial = String::strtoupper($searchInitial);
			$searchType = USER_FIELD_INITIAL;
			$search = $searchInitial;
		}
		
		$sort = Request::getUserVar('sort');
		$sort = isset($sort) ? $sort : 'name';
		$sortDirection = Request::getUserVar('sortDirection');

		$rangeInfo = PKPHandler::getRangeInfo('users');

		$users =& $userDao->getUsersByField($searchType, $searchMatch, $search, true, $rangeInfo, $sort, $sortDirection);

		$templateMgr->assign('searchField', $searchType);
		$templateMgr->assign('searchMatch', $searchMatch);
		$templateMgr->assign('search', $search);
		$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

		$templateMgr->assign('roleId', $roleId);
		$templateMgr->assign('roleName', $roleDao->getRoleName($roleId));
		$fieldOptions = Array(
			USER_FIELD_FIRSTNAME => 'user.firstName',
			USER_FIELD_LASTNAME => 'user.lastName',
			USER_FIELD_USERNAME => 'user.username',
			USER_FIELD_EMAIL => 'user.email'
		);
		$templateMgr->assign('fieldOptions', $fieldOptions);
		$templateMgr->assign_by_ref('users', $users);
		$templateMgr->assign_by_ref('thisUser', Request::getUser());
		$templateMgr->assign('alphaList', explode(' ', __('common.alphaList')));
		$templateMgr->assign('sort', $sort);
		$templateMgr->assign('sortDirection', $sortDirection);
		$templateMgr->display('admin/people/searchUsers.tpl');
	}

	/**
	 * Enroll a user in a role.
	 */
	function enroll($args) {
		$this->validate();
		$roleId = (int)(isset($args[0])?$args[0]:Request::getUserVar('roleId'));

		// Get a list of users to enroll -- either from the
		// submitted array 'users', or the single user ID in
		// 'userId'
		$users = Request::getUserVar('users');
		if (!isset($users) && Request::getUserVar('userId') != null) {
			$users = array(Request::getUserVar('userId'));
		}

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$rolePath = $roleDao->getRolePath($roleId);

		if ($users != null && is_array($users) && $rolePath != '' && $rolePath != 'admin') {
			for ($i=0; $i<count($users); $i++) {
				if (!$roleDao->roleExists($users[$i], $roleId)) {
					$role = new Role();
					$role->setUserId($users[$i]);
					$role->setRoleId($roleId);

					$roleDao->insertRole($role);
				}
			}
		}

		Request::redirect(null, 'people', (empty($rolePath) ? null : $rolePath . 's'));
	}

	/**
	 * Unenroll a user from a role.
	 */
	function unEnroll($args) {
		$roleId = isset($args[0])?$args[0]:0;
		$this->validate();

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		if ($roleId != $roleDao->getRoleIdFromPath('admin')) {
			$roleDao->deleteRoleByUserId(Request::getUserVar('userId'), $roleId);
		}

		Request::redirect(null, 'people', $roleDao->getRolePath($roleId) . 's');
	}

	/**
	 * Display form to create a new user.
	 */
	function createUser() {
		PeopleHandler::editUser();
	}

	/**
	 * Get a suggested username, making sure it's not
	 * already used by the system. (Poor-man's AJAX.)
	 */
	function suggestUsername() {
		$this->validate();
		$suggestion = Validation::suggestUsername(
			Request::getUserVar('firstName'),
			Request::getUserVar('lastName')
		);
		echo $suggestion;
	}

	/**
	 * Display form to create/edit a user profile.
	 * @param $args array optional, if set the first parameter is the ID of the user to edit
	 */
	function editUser($args = array()) {
		$this->validate();
		$this->setupTemplate(true);

		$userId = isset($args[0])?$args[0]:null;

		$templateMgr =& TemplateManager::getManager();

		if ($userId !== null && !Validation::canAdminister($userId)) {
			// We don't have administrative rights
			// over this user. Display an error.
			$templateMgr->assign('pageTitle', 'admin.people');
			$templateMgr->assign('errorMsg', 'admin.people.noAdministrativeRights');
			$templateMgr->assign('backLink', Request::url(null, null, 'people', 'all'));
			$templateMgr->assign('backLinkLabel', 'admin.people.allUsers');
			return $templateMgr->display('common/error.tpl');
		}

		import('classes.admin.form.UserManagementForm');

		$templateMgr->assign('currentUrl', Request::url(null, 'people', 'all'));
		if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
			$userForm = new UserManagementForm($userId);
		} else {
			$userForm =& new UserManagementForm($userId);
		}
		if ($userForm->isLocaleResubmit()) {
			$userForm->readInputData();
		} else {
			$userForm->initData();
		}
		$userForm->display();
	}

	/**
	 * Disable a user's account.
	 * @param $args array the ID of the user to disable
	 */
	function disableUser($args) {
		$this->validate();
		$this->setupTemplate(true);

		$userId = isset($args[0])?$args[0]:Request::getUserVar('userId');
		$user =& Request::getUser();

		if ($userId != null && $userId != $user->getId()) {
			if (!Validation::canAdminister($userId)) {
				// We don't have administrative rights
				// over this user. Display an error.
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign('pageTitle', 'admin.people');
				$templateMgr->assign('errorMsg', 'admin.people.noAdministrativeRights');
				$templateMgr->assign('backLink', Request::url(null, null, 'people', 'all'));
				$templateMgr->assign('backLinkLabel', 'admin.people.allUsers');
				return $templateMgr->display('common/error.tpl');
			}
			$userDao =& DAORegistry::getDAO('UserDAO');
			$user =& $userDao->getUser($userId);
			if ($user) {
				$user->setDisabled(1);
				$user->setDisabledReason(Request::getUserVar('reason'));
			}
			$userDao->updateObject($user);
		}

		Request::redirect(null, 'people', 'all');
	}

	/**
	 * Enable a user's account.
	 * @param $args array the ID of the user to enable
	 */
	function enableUser($args) {
		$this->validate();
		$this->setupTemplate(true);

		$userId = isset($args[0])?$args[0]:null;
		$user =& Request::getUser();

		if ($userId != null && $userId != $user->getId()) {
			$userDao =& DAORegistry::getDAO('UserDAO');
			$user =& $userDao->getUser($userId, true);
			if ($user) {
				$user->setDisabled(0);
			}
			$userDao->updateObject($user);
		}

		Request::redirect(null, 'people', 'all');
	}

	/**
	 * Remove a user from all roles
	 * @param $args array the ID of the user to remove
	 */
	function removeUser($args) {
		$this->validate();
		$this->setupTemplate(true);

		$userId = isset($args[0])?$args[0]:null;
		$user =& Request::getUser();

		if ($userId != null && $userId != $user->getId()) {
			$roleDao =& DAORegistry::getDAO('RoleDAO');
			$roleDao->deleteRoleByUserId($userId);
		}

		Request::redirect(null, 'people', 'all');
	}

	/**
	 * Save changes to a user profile.
	 */
	function updateUser() {
		$this->validate();
		$this->setupTemplate(true);

		$userId = Request::getUserVar('userId');

		if (!empty($userId) && !Validation::canAdminister($userId)) {
			// We don't have administrative rights
			// over this user. Display an error.
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign('pageTitle', 'admin.people');
			$templateMgr->assign('errorMsg', 'admin.people.noAdministrativeRights');
			$templateMgr->assign('backLink', Request::url(null, 'people', 'all'));
			$templateMgr->assign('backLinkLabel', 'admin.people.allUsers');
			return $templateMgr->display('common/error.tpl');
		}

		import('classes.admin.form.UserManagementForm');

		if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
			$userForm = new UserManagementForm($userId);
		} else {
			$userForm =& new UserManagementForm($userId);
		}
		$userForm->readInputData();

		if ($userForm->validate()) {
			$userForm->execute();

			if (Request::getUserVar('createAnother')) {
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign('currentUrl', Request::url(null, 'people', 'all'));
				$templateMgr->assign('userCreated', true);
				unset($userForm);
				if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
					$userForm = new UserManagementForm();
				} else {
					$userForm = new UserManagementForm();
				}
				$userForm->initData();
				$userForm->display();

			} else {
				if ($source = Request::getUserVar('source')) Request::redirectUrl($source);
				else Request::redirect(null, 'people', 'all');
			}

		} else {
			$this->setupTemplate(true);
			$userForm->display();
		}
	}

	/**
	 * Display a user's profile.
	 * @param $args array first parameter is the ID or username of the user to display
	 */
	function userProfile($args) {
		$this->validate();
		$this->setupTemplate(true);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('currentUrl', Request::url(null, null, 'people', 'all'));

		$userDao =& DAORegistry::getDAO('UserDAO');
		$userId = isset($args[0]) ? $args[0] : 0;
		if (is_numeric($userId)) {
			$userId = (int) $userId;
			$user = $userDao->getUser($userId);
		} else {
			$user = $userDao->getUserByUsername($userId);
		}


		if ($user == null) {
			// Non-existent user requested
			$templateMgr->assign('pageTitle', 'admin.people');
			$templateMgr->assign('errorMsg', 'admin.people.invalidUser');
			$templateMgr->assign('backLink', Request::url(null, null, 'people', 'all'));
			$templateMgr->assign('backLinkLabel', 'admin.people.allUsers');
			$templateMgr->display('common/error.tpl');

		} else {
			$site =& Request::getSite();
			$roleDao =& DAORegistry::getDAO('RoleDAO');
			$roles =& $roleDao->getRolesByUserId($user->getId());

			$countryDao =& DAORegistry::getDAO('CountryDAO');
			$country = null;
			if ($user->getCountry() != '') {
				$country = $countryDao->getCountry($user->getCountry());
			}
			$templateMgr->assign('country', $country);

			$templateMgr->assign_by_ref('user', $user);
			$templateMgr->assign_by_ref('userRoles', $roles);
			$templateMgr->assign('localeNames', AppLocale::getAllLocales());
			$templateMgr->display('admin/people/userProfile.tpl');
		}
	}

	/**
	 * Sign in as another user.
	 * @param $args array ($userId)
	 */
	function signInAsUser($args) {
		$this->validate();

		if (isset($args[0]) && !empty($args[0])) {
			$userId = (int)$args[0];

			if (!Validation::canAdminister($userId)) {
				// We don't have administrative rights
				// over this user. Display an error.
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign('pageTitle', 'admin.people');
				$templateMgr->assign('errorMsg', 'admin.people.noAdministrativeRights');
				$templateMgr->assign('backLink', Request::url(null, null, 'people', 'all'));
				$templateMgr->assign('backLinkLabel', 'admin.people.allUsers');
				return $templateMgr->display('common/error.tpl');
			}

			$userDao =& DAORegistry::getDAO('UserDAO');
			$newUser =& $userDao->getUser($userId);
			$session =& Request::getSession();

			// FIXME Support "stack" of signed-in-as user IDs?
			if (isset($newUser) && $session->getUserId() != $newUser->getId()) {
				$session->setSessionVar('signedInAs', $session->getUserId());
				$session->setSessionVar('userId', $userId);
				$session->setUserId($userId);
				$session->setSessionVar('username', $newUser->getUsername());
				Request::redirect('user');
			}
		}
		Request::redirect(Request::getRequestedPage());
	}

	/**
	 * Restore original user account after signing in as a user.
	 */
	function signOutAsUser() {
		$this->validate();

		$session =& Request::getSession();
		$signedInAs = $session->getSessionVar('signedInAs');

		if (isset($signedInAs) && !empty($signedInAs)) {
			$signedInAs = (int)$signedInAs;

			$userDao =& DAORegistry::getDAO('UserDAO');
			$oldUser =& $userDao->getUser($signedInAs);

			$session->unsetSessionVar('signedInAs');

			if (isset($oldUser)) {
				$session->setSessionVar('userId', $signedInAs);
				$session->setUserId($signedInAs);
				$session->setSessionVar('username', $oldUser->getUsername());
			}
		}

		Request::redirect(Request::getRequestedPage());
	}
}

?>
