<?php

/**
 * @file SiteSettingsForm.inc.php
 *
 * Copyright (c) 2005-2007 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package admin.form
 * @class SiteSettingsForm
 *
 * Form to edit site settings.
 *
 * $Id$
 */

define('SITE_MIN_PASSWORD_LENGTH', 4);
import('form.Form');

class SiteSettingsForm extends Form {

	/**
	 * Constructor.
	 */
	function SiteSettingsForm() {
		parent::Form('admin/settings.tpl');

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'title', 'required', 'admin.settings.form.titleRequired'));
		$this->addCheck(new FormValidator($this, 'contactName', 'required', 'admin.settings.form.contactNameRequired'));
		$this->addCheck(new FormValidator($this, 'contactEmail', 'required', 'admin.settings.form.contactEmailRequired'));
		$this->addCheck(new FormValidator($this, 'adminUsername', 'required', 'installer.form.usernameRequired'));
		$this->addCheck(new FormValidatorAlphaNum($this, 'adminUsername', 'required', 'installer.form.usernameAlphaNumeric'));
		$this->addCheck(new FormValidatorCustom($this, 'adminPassword', 'optional', 'installer.form.passwordsDoNotMatch', create_function('$password,$form', 'return $password == $form->getData(\'adminPassword2\');'), array(&$this)));
		$this->addCheck(new FormValidatorPost($this));

	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData() {
		$site =& Request::getSite();

		$this->_data = array(
			'title' => $site->getTitle(),
			'intro' => $site->getIntro(),
			'about' => $site->getAbout(),
			'contactName' => $site->getContactName(),
			'contactEmail' => $site->getContactEmail(),
			'adminUsername' => $site->getUsername(),
			'enableSubmit' => $site->getSetting('enableSubmit')
		);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(
			array('title', 'intro', 'about', 'redirect', 'contactName', 'contactEmail', 'adminUsername', 'adminPassword', 'adminPassword2', 'enableSubmit')
		);
	}

	/**
	 * Display the form.
	 */
	function display() {
		$site =& Request::getSite();
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('styleSheet', $site->getSetting('styleSheet'));
		$templateMgr->assign('customLogo', $site->getSetting('customLogo'));
		parent::display();
	}

	/**
	 * Save site settings.
	 */
	function execute() {
		$site =& Request::getSite();

		$site->setTitle($this->getData('title'));
		$site->setIntro($this->getData('intro'));
		$site->setAbout($this->getData('about'));
		$site->setContactName($this->getData('contactName'));
		$site->setContactEmail($this->getData('contactEmail'));
		$site->setUsername($this->getData('adminUsername'));
		if ($this->getData('adminPassword') != '') $site->setPassword(Validation::encryptCredentials($this->getData('adminUsername'), $this->getData('adminPassword')));
		$site->updateSetting('enableSubmit', $this->getData('enableSubmit'));
	}

	/**
	 * Uploads an image.
	 * @param $settingName string setting key associated with the file
	 */
	function uploadImage($settingName) {
		$site =& Request::getSite();
		$settingsDao = &DAORegistry::getDAO('SiteSettingsDAO');

		import('file.PublicFileManager');
		$fileManager = &new PublicFileManager();
		if ($fileManager->uploadedFileExists($settingName)) {
			$type = $fileManager->getUploadedFileType($settingName);
			$extension = $fileManager->getImageExtension($type);
			if (!$extension) {
				return false;
			}

			$uploadName = $settingName . $extension;
			if ($fileManager->uploadSiteFile($settingName, $uploadName)) {
				// Get image dimensions
				$filePath = $fileManager->getSiteFilesPath();
				list($width, $height) = getimagesize($filePath . '/' . $settingName.$extension);

				$value = array(
					'name' => $fileManager->getUploadedFileName($settingName),
					'uploadName' => $uploadName,
					'width' => $width,
					'height' => $height,
					'dateUploaded' => Core::getCurrentDate()
				);

				return $settingsDao->updateSetting($settingName, $value, 'object');
			}
		}

		return false;
	}

	/**
	 * Deletes an image.
	 * @param $settingName string setting key associated with the file
	 */
	function deleteImage($settingName) {
		$site = &Request::getSite();
		$settingsDao = &DAORegistry::getDAO('SiteSettingsDAO');
		$setting = $settingsDao->getSetting($settingName);

		import('file.PublicFileManager');
		$fileManager = &new PublicFileManager();
	 	if ($fileManager->removeSiteFile($setting['uploadName'])) {
			return $settingsDao->deleteSetting($settingName);
		} else {
			return false;
		}
	}

	/**
	 * Uploads custom stylesheet.
	 * @param $settingName string setting key associated with the file
	 */
	function uploadStyleSheet($settingName) {
		$site =& Request::getSite();
		$settingsDao = &DAORegistry::getDAO('SiteSettingsDAO');

		import('file.PublicFileManager');
		$fileManager = &new PublicFileManager();
		if ($fileManager->uploadedFileExists($settingName)) {
			$type = $fileManager->getUploadedFileType($settingName);
			if ($type != 'text/plain' && $type != 'text/css') {
				return false;
			}

			$uploadName = $settingName . '.css';
			if($fileManager->uploadSiteFile($settingName, $uploadName)) {			
				$value = array(
					'name' => $fileManager->getUploadedFileName($settingName),
					'uploadName' => $uploadName,
					'dateUploaded' => date("Y-m-d g:i:s")
				);

				return $settingsDao->updateSetting($settingName, $value, 'object');
			}
		}

		return false;
	}
}

?>
