<?php

/**
 * @file pages/admin/AdminSettingsHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.admin
 * @class AdminSettingsHandler
 *
 * Handle requests for changing site admin settings.
 *
 */

// $Id$

import('pages.admin.AdminHandler');

class AdminSettingsHandler extends AdminHandler {

	/**
	 * Display form to modify site settings.
	 */
	function settings() {
		$this->validate();
		$this->setupTemplate(true);

		import('classes.admin.form.SiteSettingsForm');

		$settingsForm = new SiteSettingsForm();
		$settingsForm->initData();
		$settingsForm->display();
	}

	/**
	 * Validate and save changes to site settings.
	 * @param $args array
	 * @param $request object
	 */
	function saveSettings($args, &$request) {
		$this->validate();
		$this->setupTemplate(true);

		import('classes.admin.form.SiteSettingsForm');

		$settingsForm = new SiteSettingsForm();
		$settingsForm->readInputData();

		$editData = false;

		if ($request->getUserVar('uploadStyleSheet')) {
			if ($settingsForm->uploadStyleSheet('styleSheet')) {
				$editData = true;
			} else {
				$settingsForm->addError('styleSheet', __('admin.settings.styleSheet.invalid'));
			}
		} elseif ($request->getUserVar('deleteStyleSheet')) {
			$editData = true;
			$settingsForm->deleteImage('styleSheet');
		} elseif ($request->getUserVar('uploadCustomLogo')) {
			if ($settingsForm->uploadImage('customLogo')) {
				$editData = true;
			} else {
				$settingsForm->addError('customLogo', __('admin.settings.customLogo.invalid'));
			}
		} elseif ($request->getUserVar('deleteCustomLogo')) {
			$editData = true;
			$settingsForm->deleteImage('customLogo');
		}

		if (!$editData && $settingsForm->validate()) {
			$settingsForm->execute();
			import('classes.notification.NotificationManager');
			$notificationManager = new NotificationManager();
			$notificationManager->createTrivialNotification('notification.notification', 'common.changesSaved');
			$request->redirect(null, 'index');
		}
		$settingsForm->display();
	}
}

?>
