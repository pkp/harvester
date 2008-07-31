<?php

/**
 * @file pages/install/InstallHandler.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.install
 * @class InstallHandler
 *
 * Handle installation requests. 
 *
 */

// $Id$


/* FIXME Prevent classes from trying to initialize the session manager (and thus the database connection) */
define('SESSION_DISABLE_INIT', 1);

import('install.form.InstallForm');
import('core.Handler');

class InstallHandler extends Handler {

	/**
	 * Display the install page.
	 */
	function index() {
		// Make sure errors are displayed to the browser during install.
		@ini_set('display_errors', E_ALL);

		InstallHandler::validate();

		if (($setLocale = Request::getUserVar('setLocale')) != null && Locale::isLocaleValid($setLocale)) {
			Request::setCookieVar('currentLocale', $setLocale);
		}

		$installForm = &new InstallForm();
		$installForm->initData();
		$installForm->display();
	}

	/**
	 * Redirect to index if system has already been installed.
	 */
	function validate() {
		if (Config::getVar('general', 'installed')) {
			Request::redirect('index');	
		}
	}

	/**
	 * Execute installer.
	 */
	function install() {
		InstallHandler::validate();

		$installForm = &new InstallForm();
		$installForm->readInputData();

		if ($installForm->validate()) {
			$installForm->execute();

		} else {
			$installForm->display();
		}
	}
}

?>
