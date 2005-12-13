<?php

/**
 * SiteSettingsForm.inc.php
 *
 * Copyright (c) 2003-2004 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package admin.form
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
		$this->addCheck(new FormValidator($this, 'adminPassword', 'required', 'installer.form.passwordRequired'));
		$this->addCheck(new FormValidatorCustom($this, 'adminPassword', 'required', 'installer.form.passwordsDoNotMatch', create_function('$password,$form', 'return $password == $form->getData(\'adminPassword2\');'), array(&$this)));
		
	}
	
	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'site.siteManagement');
		parent::display();
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
			'adminUsername' => $site->getUsername()
		);
	}
	
	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(
			array('title', 'intro', 'about', 'redirect', 'contactName', 'contactEmail', 'adminUsername', 'adminPassword', 'adminPassword2')
		);
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
		$site->setPassword(Validation::encryptCredentials($this->getData('adminUsername'), $this->getData('adminPassword')));
	}
	
}

?>
