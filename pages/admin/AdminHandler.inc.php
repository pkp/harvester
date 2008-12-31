<?php

/**
 * @file pages/admin/AdminHandler.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.admin
 * @class AdminHandler
 *
 * Handle requests for site administration functions. 
 *
 */

// $Id$


import('core.PKPHandler');

class AdminHandler extends PKPHandler {

	/**
	 * Display site admin index page.
	 */
	function index() {
		AdminHandler::validate();
		AdminHandler::setupTemplate();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->display('admin/index.tpl');
	}

	/**
	 * Validate that user has admin privileges
	 * Redirects to the user index page if not properly authenticated.
	 */
	function validate() {
		parent::validate();
		if (!Validation::isSiteAdmin()) {
			Validation::redirectLogin();
		}
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
	// Settings
	//

	function settings() {
		PKPHandler::delegate('pages.admin.AdminSettingsHandler');
	}

	function saveSettings() {
		PKPHandler::delegate('pages.admin.AdminSettingsHandler');
	}


	//
	// Layout
	//

	function layout() {
		AdminHandler::setupTemplate();
		AdminHandler::validate();

		import('admin.form.LayoutForm');
		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$layoutForm =& new LayoutForm();
		if ($layoutForm->isLocaleResubmit()) {
			$layoutForm->readInputData();
		} else {
			$layoutForm->initData();
		}
		$layoutForm->display();
	}

	function saveLayout() {
		AdminHandler::setupTemplate();
		AdminHandler::validate();

		import('admin.form.LayoutForm');
		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$layoutForm =& new LayoutForm();
		$layoutForm->readInputData();
		if ($layoutForm->validate()) {
			$layoutForm->execute();
			Request::redirect('admin');
		} else {
			$layoutForm->display();
		}
	}

	//
	// Archive Management
	//

	function archives() {
		PKPHandler::delegate('pages.admin.AdminArchiveHandler');
	}

	function createArchive() {
		PKPHandler::delegate('pages.admin.AdminArchiveHandler');
	}

	function editArchive() {
		PKPHandler::delegate('pages.admin.AdminArchiveHandler');
	}

	function manage() {
		PKPHandler::delegate('pages.admin.AdminArchiveHandler');
	}

	function updateArchive() {
		PKPHandler::delegate('pages.admin.AdminArchiveHandler');
	}

	function deleteArchive() {
		PKPHandler::delegate('pages.admin.AdminArchiveHandler');
	}

	function updateIndex() {
		PKPHandler::delegate('pages.admin.AdminArchiveHandler');
	}

	function flushIndex() {
		PKPHandler::delegate('pages.admin.AdminArchiveHandler');
	}

	//
	// Sort Order Management
	//

	function sortOrders() {
		PKPHandler::delegate('pages.admin.AdminSortOrdersHandler');
	}

	function createSortOrder() {
		PKPHandler::delegate('pages.admin.AdminSortOrdersHandler');
	}

	function editSortOrder() {
		PKPHandler::delegate('pages.admin.AdminSortOrdersHandler');
	}

	function updateSortOrder() {
		PKPHandler::delegate('pages.admin.AdminSortOrdersHandler');
	}

	function deleteSortOrder() {
		PKPHandler::delegate('pages.admin.AdminSortOrdersHandler');
	}

	//
	// Languages
	//

	function languages() {
		PKPHandler::delegate('pages.admin.AdminLanguagesHandler');
	}

	function saveLanguageSettings() {
		PKPHandler::delegate('pages.admin.AdminLanguagesHandler');
	}

	function installLocale() {
		PKPHandler::delegate('pages.admin.AdminLanguagesHandler');
	}

	function uninstallLocale() {
		PKPHandler::delegate('pages.admin.AdminLanguagesHandler');
	}

	function reloadLocale() {
		PKPHandler::delegate('pages.admin.AdminLanguagesHandler');
	}


	//
	// People Management
	//

	function people($args) {
		import('pages.admin.PeopleHandler');
		PeopleHandler::people($args);
	}

	function enrollSearch($args) {
		import('pages.admin.PeopleHandler');
		PeopleHandler::enrollSearch($args);
	}

	function enroll($args) {
		import('pages.admin.PeopleHandler');
		PeopleHandler::enroll($args);
	}

	function unEnroll($args) {
		import('pages.admin.PeopleHandler');
		PeopleHandler::unEnroll($args);
	}

	function enrollSyncSelect($args) {
		import('pages.admin.PeopleHandler');
		PeopleHandler::enrollSyncSelect($args);
	}

	function enrollSync($args) {
		import('pages.admin.PeopleHandler');
		PeopleHandler::enrollSync($args);
	}

	function createUser() {
		import('pages.admin.PeopleHandler');
		PeopleHandler::createUser();
	}

	function suggestUsername() {
		import('pages.admin.PeopleHandler');
		PeopleHandler::suggestUsername();
	}

	function mergeUsers($args) {
		import('pages.admin.PeopleHandler');
		PeopleHandler::mergeUsers($args);
	}

	function disableUser($args) {
		import('pages.admin.PeopleHandler');
		PeopleHandler::disableUser($args);
	}

	function enableUser($args) {
		import('pages.admin.PeopleHandler');
		PeopleHandler::enableUser($args);
	}

	function removeUser($args) {
		import('pages.admin.PeopleHandler');
		PeopleHandler::removeUser($args);
	}

	function editUser($args) {
		import('pages.admin.PeopleHandler');
		PeopleHandler::editUser($args);
	}

	function updateUser() {
		import('pages.admin.PeopleHandler');
		PeopleHandler::updateUser();
	}

	function userProfile($args) {
		import('pages.admin.PeopleHandler');
		PeopleHandler::userProfile($args);
	}

	function signInAsUser($args) {
		import('pages.admin.PeopleHandler');
		PeopleHandler::signInAsUser($args);
	}

	function signOutAsUser() {
		import('pages.admin.PeopleHandler');
		PeopleHandler::signOutAsUser();
	}


	//
	// Administrative functions
	//

	function systemInfo() {
		PKPHandler::delegate('pages.admin.AdminFunctionsHandler');
	}

	function editSystemConfig() {
		PKPHandler::delegate('pages.admin.AdminFunctionsHandler');
	}

	function saveSystemConfig() {
		PKPHandler::delegate('pages.admin.AdminFunctionsHandler');
	}

	function phpinfo() {
		PKPHandler::delegate('pages.admin.AdminFunctionsHandler');
	}

	function expireSessions() {
		PKPHandler::delegate('pages.admin.AdminFunctionsHandler');
	}

	function clearTemplateCache() {
		PKPHandler::delegate('pages.admin.AdminFunctionsHandler');
	}

	function clearDataCache() {
		PKPHandler::delegate('pages.admin.AdminFunctionsHandler');
	}

	//
	// Plugin Management
	//

	function plugins() {
		PKPHandler::delegate('pages.admin.PluginHandler');
	}

	function plugin() {
		PKPHandler::delegate('pages.admin.PluginHandler');
	}
	
	function pluginManagement($args) {
		import('pages.admin.PluginManagementHandler');
		PluginManagementHandler::managePlugins($args);
	}

	//
	// Captcha
	//

	function viewCaptcha($args) {
		$captchaId = (int) array_shift($args);
		import('captcha.CaptchaManager');
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
