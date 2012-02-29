<?php

/**
 * @file classes/admin/form/SiteSettingsForm.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SiteSettingsForm
 * @ingroup admin_form
 * @see PKPSiteSettingsForm
 *
 * @brief Form to edit site settings.
 */

// $Id$


import('lib.pkp.classes.admin.form.PKPSiteSettingsForm');

class SiteSettingsForm extends PKPSiteSettingsForm {
	/**
	 * Constructor.
	 */
	function SiteSettingsForm() {
		parent::PKPSiteSettingsForm();
	}

	function initData() {
		$site =& Request::getSite();
		parent::initData();
		$this->_data['enableSubmit'] = $site->getSetting('enableSubmit');
		$this->_data['disableSubmissions'] = $site->getSetting('disableSubmissions');
		$this->_data['theme'] = $site->getSetting('theme');
	}

	function readInputData() {
		parent::readInputData();
		$this->readUserVars(array('enableSubmit', 'disableSubmissions', 'theme'));
	}

	function execute() {
		if (parent::execute()) {
			$site =& Request::getSite();
			$site->updateSetting('enableSubmit', $this->getData('enableSubmit')?1:0);
			$site->updateSetting('disableSubmissions', $this->getData('disableSubmissions')?1:0);
			$site->updateSetting('theme', $this->getData('theme'));
		}
		return false;
	}

	/**
	 * Uploads an image.
	 * @param $settingName string setting key associated with the file
	 */
	function uploadImage($settingName) {
		$site =& Request::getSite();
		$settingsDao =& DAORegistry::getDAO('SiteSettingsDAO');

		import('classes.file.PublicFileManager');
		$fileManager = new PublicFileManager();
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
		$site =& Request::getSite();
		$settingsDao =& DAORegistry::getDAO('SiteSettingsDAO');
		$setting = $settingsDao->getSetting($settingName);

		import('classes.file.PublicFileManager');
		$fileManager = new PublicFileManager();
	 	if ($fileManager->removeSiteFile($setting['uploadName'])) {
			return $settingsDao->deleteSetting($settingName);
		} else {
			return false;
		}
	}

	function display() {
		$site =& Request::getSite();
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('customLogo', $site->getSetting('customLogo'));
		$allThemes =& PluginRegistry::loadCategory('themes');
		$themes = array();
		foreach ($allThemes as $key => $junk) {
			$plugin =& $allThemes[$key]; // by ref
			$themes[basename($plugin->getPluginPath())] =& $plugin;
			unset($plugin);
		}
		$templateMgr->assign_by_ref('themes', $themes);
		return parent::display();
	}

	/**
	 * Uploads custom stylesheet.
	 * @param $settingName string setting key associated with the file
	 */
	function uploadStyleSheet($settingName) {
		$site =& Request::getSite();
		$settingsDao =& DAORegistry::getDAO('SiteSettingsDAO');

		import('classes.file.PublicFileManager');
		$fileManager = new PublicFileManager();
		if ($fileManager->uploadedFileExists($settingName)) {
			$type = $fileManager->getUploadedFileType($settingName);
			if ($type != 'text/plain' && $type != 'text/css') {
				return false;
			}

			$uploadName = $settingName . '.css';
			if($fileManager->uploadSiteFile($settingName, $site->getSiteStyleFilename())) {
				$value = array(
					'name' => $fileManager->getUploadedFileName($settingName),
					'uploadName' => $uploadName,
					'dateUploaded' => Core::getCurrentDate()
				);

				return $settingsDao->updateSetting($settingName, $value, 'object');
			}
		}

		return false;
	}
}

?>
