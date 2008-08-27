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
		Handler::delegate('pages.admin.AdminSettingsHandler');
	}

	function saveSettings() {
		Handler::delegate('pages.admin.AdminSettingsHandler');
	}


	//
	// Layout
	//

	function layout() {
		import('admin.form.LayoutForm');
		$layoutForm =& new LayoutForm();
		if ($layoutForm->isLocaleResubmit()) {
			$layoutForm->readInputData();
		} else {
			$layoutForm->initData();
		}
		$layoutForm->display();
	}

	function saveLayout() {
		import('admin.form.LayoutForm');
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
		Handler::delegate('pages.admin.AdminArchiveHandler');
	}

	function createArchive() {
		Handler::delegate('pages.admin.AdminArchiveHandler');
	}

	function editArchive() {
		Handler::delegate('pages.admin.AdminArchiveHandler');
	}

	function manage() {
		Handler::delegate('pages.admin.AdminArchiveHandler');
	}

	function updateArchive() {
		Handler::delegate('pages.admin.AdminArchiveHandler');
	}

	function deleteArchive() {
		Handler::delegate('pages.admin.AdminArchiveHandler');
	}

	function updateIndex() {
		Handler::delegate('pages.admin.AdminArchiveHandler');
	}

	function flushIndex() {
		Handler::delegate('pages.admin.AdminArchiveHandler');
	}

	//
	// Sort Order Management
	//

	function sortOrders() {
		Handler::delegate('pages.admin.AdminSortOrdersHandler');
	}

	function createSortOrder() {
		Handler::delegate('pages.admin.AdminSortOrdersHandler');
	}

	function editSortOrder() {
		Handler::delegate('pages.admin.AdminSortOrdersHandler');
	}

	function updateSortOrder() {
		Handler::delegate('pages.admin.AdminSortOrdersHandler');
	}

	function deleteSortOrder() {
		Handler::delegate('pages.admin.AdminSortOrdersHandler');
	}

	//
	// Languages
	//

	function languages() {
		Handler::delegate('pages.admin.AdminLanguagesHandler');
	}

	function saveLanguageSettings() {
		Handler::delegate('pages.admin.AdminLanguagesHandler');
	}

	function installLocale() {
		Handler::delegate('pages.admin.AdminLanguagesHandler');
	}

	function uninstallLocale() {
		Handler::delegate('pages.admin.AdminLanguagesHandler');
	}

	function reloadLocale() {
		Handler::delegate('pages.admin.AdminLanguagesHandler');
	}


	//
	// Administrative functions
	//

	function systemInfo() {
		Handler::delegate('pages.admin.AdminFunctionsHandler');
	}

	function editSystemConfig() {
		Handler::delegate('pages.admin.AdminFunctionsHandler');
	}

	function saveSystemConfig() {
		Handler::delegate('pages.admin.AdminFunctionsHandler');
	}

	function phpinfo() {
		Handler::delegate('pages.admin.AdminFunctionsHandler');
	}

	function expireSessions() {
		Handler::delegate('pages.admin.AdminFunctionsHandler');
	}

	function clearTemplateCache() {
		Handler::delegate('pages.admin.AdminFunctionsHandler');
	}

	function clearDataCache() {
		Handler::delegate('pages.admin.AdminFunctionsHandler');
	}

	//
	// Plugin Management
	//

	function plugins() {
		Handler::delegate('pages.admin.PluginHandler');
	}

	function plugin() {
		Handler::delegate('pages.admin.PluginHandler');
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
