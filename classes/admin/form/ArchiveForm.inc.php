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

		$harvesterPlugin = Request::getUserVar('harvesterPlugin');
		HookRegistry::call('ArchiveForm::ArchiveForm', array(&$this, $harvesterPlugin));
	}
	
	/**
	 * Display the form.
	 */
	function display() {
		$harvesters =& PluginRegistry::loadCategory('harvesters');

		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign('archiveId', $this->archiveId);
		$templateMgr->assign('helpTopicId', 'site.siteManagement');
		$templateMgr->assign_by_ref('harvesters', $harvesters);
		parent::display();
	}
	
	/**
	 * Initialize form data from current settings.
	 */
	function initData() {
		if (isset($this->archiveId)) {
			$archiveDao = &DAORegistry::getDAO('ArchiveDAO');
			$archive = &$archiveDao->getArchive($this->archiveId);
			$harvesterPlugin = Request::getUserVar('harvesterPlugin');
			
			if ($archive != null) {
				$this->_data = array(
					'title' => $archive->getTitle(),
					'description' => $archive->getDescription(),
					'url' => $archive->getUrl(),
					'harvesterPlugin' => $harvesterPlugin
				);

			} else {
				$this->archiveId = null;
			}

			HookRegistry::call('ArchiveForm::initData', array(&$this, &$archive, $harvesterPlugin));

			// Allow user-submitted parameters to override the 
			// usual form values. This is useful for when users
			// change the harvester plugin so that they don't have
			// to re-key changes to form elements.
			if (!empty($harvesterPlugin)) {
				$parameterNames = $this->getParameterNames($harvesterPlugin);
				foreach ($parameterNames as $name) {
					$value = Request::getUserVar($name);
					if (!empty($value)) {
						$this->setData($name, $value);
					}
				}
			}
		}
	}

	function getParameterNames($harvesterPlugin) {
		$parameterNames = array('title', 'description', 'url', 'harvesterPlugin');
		HookRegistry::call('ArchiveForm::getParameterNames', array(&$this, &$parameterNames, $harvesterPlugin));
		return $parameterNames;
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$harvesterPlugin = Request::getUserVar('harvesterPlugin');
		$this->readUserVars($this->getParameterNames($harvesterPlugin));
	}
	
	/**
	 * Save archive settings.
	 */
	function execute() {
		$archiveDao = &DAORegistry::getDAO('ArchiveDAO');
		
		if (isset($this->archiveId)) {
			$archive = &$archiveDao->getArchive($this->archiveId);
		}
		
		if (!isset($archive)) {
			$archive = &new Archive();
		}

		$archive->setHarvesterPlugin($this->getData('harvesterPlugin'));
		$archive->setDescription($this->getData('description'));
		$archive->setUrl($this->getData('url'));
		$archive->setOaiUrl($this->getData('oaiUrl'));
		$archive->setTitle($this->getData('title'));

		if ($archive->getArchiveId() != null) {
			$archiveDao->updateArchive($archive);
		} else {
			$archiveId = $archiveDao->insertArchive($archive);
		}

		HookRegistry::call('ArchiveForm::execute', array(&$this, &$archive));
	}
	
}

?>
