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


import('core.Handler');

class AdminHandler extends Handler {

	/**
	 * Display site admin index page.
	 */
	function index() {
		AdminHandler::validate();
		AdminHandler::setupTemplate();

		$templateMgr = &TemplateManager::getManager();
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
		$templateMgr = &TemplateManager::getManager();
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
		import('pages.admin.AdminSettingsHandler');
		AdminSettingsHandler::settings();
	}

	function saveSettings() {
		import('pages.admin.AdminSettingsHandler');
		AdminSettingsHandler::saveSettings();
	}

	//
	// Archive Management
	//

	function archives() {
		import('pages.admin.AdminArchiveHandler');
		AdminArchiveHandler::archives();
	}

	function createArchive() {
		import('pages.admin.AdminArchiveHandler');
		AdminArchiveHandler::createArchive();
	}

	function editArchive($args = array()) {
		import('pages.admin.AdminArchiveHandler');
		AdminArchiveHandler::editArchive($args);
	}

	function manage($args = array()) {
		import('pages.admin.AdminArchiveHandler');
		AdminArchiveHandler::manage($args);
	}

	function updateArchive() {
		import('pages.admin.AdminArchiveHandler');
		AdminArchiveHandler::updateArchive();
	}

	function deleteArchive($args) {
		import('pages.admin.AdminArchiveHandler');
		AdminArchiveHandler::deleteArchive($args);
	}

	function updateIndex($args) {
		import('pages.admin.AdminArchiveHandler');
		AdminArchiveHandler::updateIndex($args);
	}

	function flushIndex($args) {
		import('pages.admin.AdminArchiveHandler');
		AdminArchiveHandler::flushIndex($args);
	}

	//
	// Languages
	//

	function languages() {
		import('pages.admin.AdminLanguagesHandler');
		AdminLanguagesHandler::languages();
	}

	function saveLanguageSettings() {
		import('pages.admin.AdminLanguagesHandler');
		AdminLanguagesHandler::saveLanguageSettings();
	}

	function installLocale() {
		import('pages.admin.AdminLanguagesHandler');
		AdminLanguagesHandler::installLocale();
	}

	function uninstallLocale() {
		import('pages.admin.AdminLanguagesHandler');
		AdminLanguagesHandler::uninstallLocale();
	}

	function reloadLocale() {
		import('pages.admin.AdminLanguagesHandler');
		AdminLanguagesHandler::reloadLocale();
	}


	//
	// Administrative functions
	//

	function systemInfo() {
		import('pages.admin.AdminFunctionsHandler');
		AdminFunctionsHandler::systemInfo();
	}

	function editSystemConfig() {
		import('pages.admin.AdminFunctionsHandler');
		AdminFunctionsHandler::editSystemConfig();
	}

	function saveSystemConfig() {
		import('pages.admin.AdminFunctionsHandler');
		AdminFunctionsHandler::saveSystemConfig();
	}

	function phpinfo() {
		import('pages.admin.AdminFunctionsHandler');
		AdminFunctionsHandler::phpInfo();
	}

	function expireSessions() {
		import('pages.admin.AdminFunctionsHandler');
		AdminFunctionsHandler::expireSessions();
	}

	function clearTemplateCache() {
		import('pages.admin.AdminFunctionsHandler');
		AdminFunctionsHandler::clearTemplateCache();
	}

	function clearDataCache() {
		import('pages.admin.AdminFunctionsHandler');
		AdminFunctionsHandler::clearDataCache();
	}

	//
	// Plugin Management
	//

	function plugins($args) {
		import('pages.admin.PluginHandler');
		PluginHandler::plugins($args);
	}

	function plugin($args) {
		import('pages.admin.PluginHandler');
		PluginHandler::plugin($args);
	}

	//
	// Captcha
	//

	function viewCaptcha($args) {
		$captchaId = (int) array_shift($args);
		import('captcha.CaptchaManager');
		$captchaManager =& new CaptchaManager();
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
