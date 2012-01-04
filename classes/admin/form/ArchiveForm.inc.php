<?php

/**
 * @file ArchiveForm.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package admin.form
 * @class ArchiveForm
 *
 * Form for site administrator to edit archive settings.
 *
 * $Id$
 */

import('lib.pkp.classes.db.DBDataXMLParser');
import('lib.pkp.classes.form.Form');

class ArchiveForm extends Form {

	/** @var $archiveId int The ID of the archive being edited */
	var $archiveId;

	/** @var $captchaEnabled boolean Whether or not Captcha tests are enabled */
	var $captchaEnabled;

	/** @var $archive object The archive object */
	var $archive;

	/** @var $harvesters array List of harvesters installed in the system */
	var $harvesters;

	/** The name of the harvester being used for this archive. */
	var $harvesterPluginName;

	/** Whether or not to allow archive management in the user interface */
	var $allowManagement;

	/**
	 * Constructor.
	 * @param $archiveId omit for a new archive
	 */
	function ArchiveForm($archiveId = null, $allowManagement = false) {
		parent::Form('admin/archiveForm.tpl');

		$this->archiveId = isset($archiveId) ? (int) $archiveId : null;
		$this->allowManagement = $allowManagement;

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'title', 'required', 'admin.archives.form.titleRequired'));
		$this->addCheck(new FormValidator($this, 'url', 'required', 'admin.archives.form.urlRequired'));
		$this->addCheck(new FormValidatorPost($this));

		import('lib.pkp.classes.captcha.CaptchaManager');
		$captchaManager = new CaptchaManager();
		$this->captchaEnabled = $captchaManager->isEnabled();
		if ($this->captchaEnabled && !Validation::isSiteAdmin()) {
			$this->addCheck(new FormValidatorCaptcha($this, 'captcha', 'captchaId', 'common.captchaField.badCaptcha'));
		}

		$this->harvesterPluginName = Request::getUserVar('harvesterPluginName');

		if ($archiveId) {
			$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
			$this->archive =& $archiveDao->getArchive($this->archiveId, false);
			if (empty($this->harvesterPluginName) && $this->archive) $this->harvesterPluginName = $this->archive->getHarvesterPluginName();
		}

		if (empty($this->harvesterPluginName)) {
			$site =& Request::getSite();
			$this->harvesterPluginName = $site->getSetting('defaultHarvesterPlugin');
		}

		$this->harvesters =& PluginRegistry::loadCategory('harvesters');

		HookRegistry::call('ArchiveForm::ArchiveForm', array(&$this, $this->harvesterPluginName));
	}

	/**
	 * Deletes an archive image.
	 */
	function deleteArchiveImage() {
		$archive =& $this->archive;
		$archiveImage = $archive->getSetting('archiveImage');
		if (!$archiveImage) return false;

		import('classes.file.PublicFileManager');
		$fileManager = new PublicFileManager();
		if ($fileManager->removeSiteFile($archiveImage['uploadName'])) {
			return $archive->updateSetting('archiveImage', null);
		} else {
			return false;
		}
	}

	function uploadArchiveImage() {
		import('classes.file.PublicFileManager');
		$fileManager = new PublicFileManager();

		$archive =& $this->archive;

		$type = $fileManager->getUploadedFileType('archiveImage');
		$extension = $fileManager->getImageExtension($type);
		if (!$extension) return false;

		$uploadName = 'archiveImage-' . (int) $archive->getArchiveId() . $extension;
		if (!$fileManager->uploadSiteFile('archiveImage', $uploadName)) return false;

		$filePath = $fileManager->getSiteFilesPath();
		list($width, $height) = getimagesize($filePath . '/' . $uploadName);

		if (!Validation::isSiteAdmin() && ($width > 150 || $height > 150 || $width <= 0 || $height <= 0)) {
			$archiveSetting = null;
			$archive->updateSetting('archiveImage', $archiveSetting);
			$fileManager->removeSiteFile($filePath);
			return false;
		}

		$archiveSetting = array(
			'name' => $fileManager->getUploadedFileName('archiveImage'),
			'uploadName' => $uploadName,
			'width' => $width,
			'height' => $height,
			'dateUploaded' => Core::getCurrentDate()
		);

		$archive->updateSetting('archiveImage', $archiveSetting);
		return true;
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('archiveId', $this->archiveId);
		if ($this->captchaEnabled && !Validation::isSiteAdmin()) {
			import('lib.pkp.classes.captcha.CaptchaManager');
			$captchaManager = new CaptchaManager();
			$captcha =& $captchaManager->createCaptcha();
			if ($captcha) {
				$templateMgr->assign('captchaEnabled', $this->captchaEnabled);
				$this->setData('captchaId', $captcha->getId());
			}
		}
		if ($this->archive) $templateMgr->assign('archiveImage', $this->archive->getSetting('archiveImage'));
		$templateMgr->assign_by_ref('harvesters', PluginRegistry::getPlugins('harvesters'));
		$templateMgr->assign('allowManagement', $this->allowManagement);
		HookRegistry::call('ArchiveForm::display', array(&$this, &$templateMgr, $this->harvesterPluginName));
		parent::display();
	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData() {
		if (isset($this->archive)) {
			$this->_data = array(
				'title' => $this->archive->getTitle(),
				'publicArchiveId' => $this->archive->getPublicArchiveId(),
				'description' => $this->archive->getSetting('description'),
				'url' => $this->archive->getUrl(),
				'harvesterPluginName' => $this->harvesterPluginName,
				'archive' => $this->archive,
				'enabled' => $this->archive->getEnabled()
			);
		} else {
			$this->archiveId = null;
			$this->_data = array(
				'harvesterPluginName' => $this->harvesterPluginName,
				'enabled' => true
			);
		}

		HookRegistry::call('ArchiveForm::initData', array(&$this, &$this->archive, $this->harvesterPluginName));

		// Allow user-submitted parameters to override the 
		// usual form values. This is useful for when users
		// change the harvester plugin so that they don't have
		// to re-key changes to form elements.
		if (!empty($this->harvesterPluginName)) {
			$parameterNames = $this->getParameterNames();
			foreach ($parameterNames as $name) {
				$value = Request::getUserVar($name);
				if (!empty($value)) {
					$this->setData($name, $value);
				}
			}
		}
	}

	function getParameterNames() {
		$parameterNames = array('title', 'description', 'url', 'enabled');

		if ($this->captchaEnabled && !Validation::isSiteAdmin()) {
			$parameterNames[] = 'captchaId';
			$parameterNames[] = 'captcha';
		}

		if (Validation::isSiteAdmin()) $parameterNames[] = 'publicArchiveId';
		HookRegistry::call('ArchiveForm::getParameterNames', array(&$this, &$parameterNames, $this->harvesterPluginName));
		return $parameterNames;
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars($this->getParameterNames());
	}

	function validate() {
		// Check to ensure that the plugin name is valid
		if (!isset($this->harvesters[(string) $this->harvesterPluginName])) {
			$this->addError('harvesterPluginName', __('archive.type.invalid'));
			return false;
		}

		if (Validation::isSiteAdmin()) {
			// Check to ensure that the public ID, if specified, is unique
			$publicArchiveId = $this->getData('publicArchiveId');
			$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
			if ($publicArchiveId != '' && $archiveDao->archiveExistsByPublicArchiveId($publicArchiveId, $this->archiveId)) {
				$this->addError('publicArchiveId', __('admin.archives.form.publicArchiveIdExists'));
				$this->addErrorField('publicArchiveId');
			}
		}
		return parent::validate();
	}

	/**
	 * Save archive settings.
	 */
	function execute() {
		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');

		if (!isset($this->archive)) {
			$this->archive = new Archive();
			$user =& Request::getUser();
			$this->archive->setUserId($user->getId());
		}

		$this->harvesterPluginName = Request::getUserVar('harvesterPluginName');
		$this->archive->setHarvesterPluginName($this->harvesterPluginName);
		$this->archive->setUrl($this->getData('url'));
		$this->archive->setTitle($this->getData('title'));
		if (Validation::isSiteAdmin()) {
			$this->archive->setPublicArchiveId($this->getData('publicArchiveId'));
			$this->archive->setEnabled($this->getData('enabled'));
		} else {
			$site =& Request::getSite();
			$this->archive->setEnabled($site->getSetting('disableSubmissions')?1:0);
		}

		if ($this->archive->getArchiveId() != null) {
			$archiveDao->updateArchive($this->archive);
		} else {
			$archiveId = $archiveDao->insertArchive($this->archive);

			// Include the current default set of reading tools.
			import('classes.rt.harvester2.HarvesterRTAdmin');
			$rtAdmin = new HarvesterRTADmin($archiveId);
			$rtAdmin->restoreVersions(false);
		}

		$this->archive->updateSetting('description', $this->getData('description'));

		HookRegistry::call('ArchiveForm::execute', array(&$this, &$this->archive, $this->harvesterPluginName));

		if (!Validation::isSiteAdmin()) {
			// Send an email notifying the administrator of the new archive.
			import('classes.mail.MailTemplate');
			$email = new MailTemplate('NEW_ARCHIVE_NOTIFY');
			if ($email->isEnabled()) {
				$email->assignParams(array(
					'archiveTitle' => $this->getData('title'),
					'siteTitle' => $site->getLocalizedTitle(),
					'loginUrl' => Request::url('admin', 'manage', $this->archive->getArchiveId())
				));
				$email->addRecipient($site->getLocalizedSetting('contactEmail'), $site->getLocalizedSetting('contactName'));
				$email->send();
			}
		}
	}

}

?>
