<?php

/**
 * ArchiveForm.inc.php
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package admin.form
 *
 * Form for site administrator to edit archive settings.
 *
 * $Id$
 */

import('db.DBDataXMLParser');
import('form.Form');

class ArchiveForm extends Form {

	/** The ID of the archive being edited */
	var $archiveId;

	/** The archive object */
	var $archive;

	/** The name of the harvester being used for this archive. */
	var $harvesterPlugin;

	/**
	 * Constructor.
	 * @param $archiveId omit for a new archive
	 */
	function ArchiveForm($archiveId = null) {
		parent::Form('admin/archiveForm.tpl');
		
		$this->archiveId = isset($archiveId) ? (int) $archiveId : null;
		
		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'title', 'required', 'admin.archives.form.titleRequired'));
		$this->addCheck(new FormValidator($this, 'url', 'required', 'admin.archives.form.urlRequired'));

		$this->harvesterPlugin = Request::getUserVar('harvesterPlugin');

		if ($archiveId) {
			$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
			$this->archive =& $archiveDao->getArchive($this->archiveId);
			if (empty($this->harvesterPlugin) && $this->archive) $this->harvesterPlugin = $this->archive->getHarvesterPlugin();
		}

		if (empty($this->harvesterPlugin)) {
			$site =& Request::getSite();
			$this->harvesterPlugin = $site->getSetting('defaultHarvesterPlugin');
		}

		$harvesters =& PluginRegistry::loadCategory('harvesters');

		HookRegistry::call('ArchiveForm::ArchiveForm', array(&$this, $this->harvesterPlugin));
	}
	
	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign('archiveId', $this->archiveId);
		$templateMgr->assign('helpTopicId', 'site.siteManagement');
		$templateMgr->assign_by_ref('harvesters', PluginRegistry::getPlugins('harvesters'));
		parent::display();
	}
	
	/**
	 * Initialize form data from current settings.
	 */
	function initData() {
		if (isset($this->archive)) {
			$this->_data = array(
				'title' => $this->archive->getTitle(),
				'description' => $this->archive->getDescription(),
				'url' => $this->archive->getUrl(),
				'harvesterPlugin' => $this->harvesterPlugin
			);
		} else {
			$this->archiveId = null;
			$this->_data = array(
				'harvesterPlugin' => $this->harvesterPlugin
			);
		}

		HookRegistry::call('ArchiveForm::initData', array(&$this, &$this->archive, $this->harvesterPlugin));

		// Allow user-submitted parameters to override the 
		// usual form values. This is useful for when users
		// change the harvester plugin so that they don't have
		// to re-key changes to form elements.
		if (!empty($this->harvesterPlugin)) {
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
		$parameterNames = array('title', 'description', 'url');
		HookRegistry::call('ArchiveForm::getParameterNames', array(&$this, &$parameterNames, $this->harvesterPlugin));
		return $parameterNames;
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars($this->getParameterNames());
	}
	
	/**
	 * Save archive settings.
	 */
	function execute() {
		$archiveDao = &DAORegistry::getDAO('ArchiveDAO');
		
		if (!isset($this->archive)) {
			$this->archive = &new Archive();
		}

		$this->harvesterPlugin = Request::getUserVar('harvesterPlugin');
		$this->archive->setHarvesterPlugin($this->harvesterPlugin);
		$this->archive->setDescription($this->getData('description'));
		$this->archive->setUrl($this->getData('url'));
		$this->archive->setTitle($this->getData('title'));

		if ($this->archive->getArchiveId() != null) {
			$archiveDao->updateArchive($this->archive);
		} else {
			$archiveId = $archiveDao->insertArchive($this->archive);
		}

		HookRegistry::call('ArchiveForm::execute', array(&$this, &$this->archive, $this->harvesterPlugin));
	}
	
}

?>
