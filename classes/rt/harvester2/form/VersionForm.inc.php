<?php

/**
 * @file VersionForm.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package rt.ojs.form
 * @class VersionForm
 *
 * Form to change metadata information for an RT version.
 *
 * $Id$
 */

import('lib.pkp.classes.form.Form');

class VersionForm extends Form {

	/** @var int the ID of the version */
	var $versionId;

	/** @var int the ID of the archive */
	var $archiveId;

	/** @var Version current version */
	var $version;

	/**
	 * Constructor.
	 */
	function VersionForm($versionId, $archiveId) {
		parent::Form('rtadmin/version.tpl');

		$this->addCheck(new FormValidatorPost($this));

		$this->archiveId = $archiveId;

		$rtDao =& DAORegistry::getDAO('RTDAO');
		$this->version =& $rtDao->getVersion($versionId, $archiveId);

		if (isset($this->version)) {
			$this->versionId = $versionId;
		}
	}

	/**
	 * Initialize form data from current version.
	 */
	function initData() {
		if (isset($this->version)) {
			$version =& $this->version;
			$this->_data = array(
				'key' => $version->getKey(),
				'title' => $version->getTitle(),
				'locale' => $version->getLocale(),
				'description' => $version->getDescription()
			);
		} else {
			$this->_data = array();
		}
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();

		if (isset($this->version)) {
			$templateMgr->assign_by_ref('version', $this->version);
			$templateMgr->assign('versionId', $this->versionId);
			$templateMgr->assign('archiveId', $this->archiveId);
		}

		parent::display();
	}


	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(
			array(
				'key',
				'title',
				'locale',
				'description'
			)
		);
	}

	/**
	 * Save changes to version.
	 * @return int the version ID
	 */
	function execute() {
		$rtDao =& DAORegistry::getDAO('RTDAO');

		$version = $this->version;
		if (!isset($version)) {
			$version = new RTVersion();
		}

		$version->setTitle($this->getData('title'));
		$version->setKey($this->getData('key'));
		$version->setLocale($this->getData('locale'));
		$version->setDescription($this->getData('description'));

		if (isset($this->version)) {
			$rtDao->updateVersion($this->archiveId, $version);
		} else {
			$rtDao->insertVersion($this->archiveId, $version);
			$this->versionId = $version->getVersionId();
		}

		return $this->versionId;
	}

}

?>
