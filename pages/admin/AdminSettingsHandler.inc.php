<?php

/**
 * @file AdminSettingsHandler.inc.php
 *
 * Copyright (c) 2005-2007 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.admin
 * @class AdminSettingsHandler
 *
 * Handle requests for changing site admin settings. 
 *
 * $Id$
 */

class AdminSettingsHandler extends AdminHandler {
	
	/**
	 * Display form to modify site settings.
	 */
	function settings() {
		parent::validate();
		parent::setupTemplate(true);
		
		import('admin.form.SiteSettingsForm');

		$settingsForm = &new SiteSettingsForm();
		$settingsForm->initData();
		$settingsForm->display();
	}
	
	/**
	 * Validate and save changes to site settings.
	 */
	function saveSettings() {
		parent::validate();
		parent::setupTemplate(true);
		
		import('admin.form.SiteSettingsForm');
		
		$settingsForm = &new SiteSettingsForm();
		$settingsForm->readInputData();

		$editData = false;

		if (Request::getUserVar('uploadStyleSheet')) {
			if ($settingsForm->uploadStyleSheet('styleSheet')) {
				$editData = true;
			} else {
				$settingsForm->addError('styleSheet', 'admin.settings.styleSheet.invalid');
			}
		} elseif (Request::getUserVar('deleteStyleSheet')) {
			$editData = true;
			$settingsForm->deleteImage('styleSheet');
		} elseif (Request::getUserVar('uploadCustomLogo')) {
			if ($settingsForm->uploadImage('customLogo')) {
				$editData = true;
			} else {
				$settingsForm->addError('customLogo', 'admin.settings.customLogo.invalid');
			}
		} elseif (Request::getUserVar('deleteCustomLogo')) {
			$editData = true;
			$settingsForm->deleteImage('customLogo');
		}

		if (!$editData && $settingsForm->validate()) {
			$settingsForm->execute();
		
			$templateMgr = &TemplateManager::getManager();
			$templateMgr->assign(array(
				'currentUrl' => 'admin/settings',
				'pageTitle' => 'admin.siteSettings',
				'message' => 'common.changesSaved',
				'backLink' => Request::getPageUrl() . '/admin',
				'backLinkLabel' => 'admin.siteAdmin'
			));
			$templateMgr->display('common/message.tpl');
			
		} else {
			$settingsForm->display();
		}
	}
	
}

?>
