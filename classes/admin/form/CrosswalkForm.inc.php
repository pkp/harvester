<?php

/**
 * CrosswalkForm.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package admin.form
 *
 * Form for site administrator to edit crosswalk settings.
 *
 * $Id$
 */

import('db.DBDataXMLParser');
import('form.Form');

class CrosswalkForm extends Form {
	/** The ID of the crosswalk being edited */
	var $crosswalkId;

	/** The crosswalk object */
	var $crosswalk;

	/**
	 * Constructor.
	 * @param $crosswalkId omit for a new crosswalk
	 */
	function CrosswalkForm($crosswalkId = null) {
		parent::Form('admin/crosswalkForm.tpl');

		$this->crosswalkId = isset($crosswalkId) ? (int) $crosswalkId : null;
		
		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'name', 'required', 'admin.crosswalks.form.nameRequired'));
		$this->addCheck(new FormValidator($this, 'description', 'required', 'admin.crosswalks.form.descriptionRequired'));

		$this->harvesterPlugin = Request::getUserVar('harvesterPlugin');

		if ($crosswalkId) {
			$crosswalkDao =& DAORegistry::getDAO('CrosswalkDAO');
			$this->crosswalk =& $crosswalkDao->getCrosswalkById($this->crosswalkId);
		}

		HookRegistry::call('CrosswalkForm::CrosswalkForm', array(&$this));
	}
	
	/**
	 * Display the form.
	 */
	function display() {
		$schemaPlugins =& PluginRegistry::loadCategory('schemas');

		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign('crosswalkId', $this->crosswalkId);
		$templateMgr->assign('helpTopicId', 'site.siteManagement');
		$templateMgr->assign_by_ref('schemaPlugins', $schemaPlugins);
		$templateMgr->assign_by_ref('schemaPluginName', Request::getUserVar('schemaPluginName'));
		parent::display();
	}
	
	/**
	 * Initialize form data from current settings.
	 */
	function initData() {
		if (isset($this->crosswalk)) {
			$this->_data = array(
				'name' => $this->crosswalk->getName(),
				'description' => $this->crosswalk->getDescription()
			);
		} else {
			$this->crosswalkId = null;
			$this->_data = array(
			);
		}

		// Allow user-submitted parameters to override the 
		// usual form values. This is useful for when users
		// change the harvester plugin so that they don't have
		// to re-key changes to form elements.
		$parameterNames = $this->getParameterNames();
		foreach ($parameterNames as $name) {
			$value = Request::getUserVar($name);
			if (!empty($value)) {
				$this->setData($name, $value);
			}
		}
	}

	function getParameterNames() {
		return array('name', 'description');
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars($this->getParameterNames());
	}
	
	/**
	 * Save crosswalk settings.
	 */
	function execute() {
		$crosswalkDao = &DAORegistry::getDAO('CrosswalkDAO');
		
		if (!isset($this->crosswalk)) {
			$this->crosswalk = &new Crosswalk();
		}

		$this->crosswalk->setName($this->getData('name'));
		$this->crosswalk->setDescription($this->getData('description'));

		if ($this->crosswalk->getCrosswalkId() != null) {
			$crosswalkDao->updateCrosswalk($this->crosswalk);
		} else {
			$this->crosswalk->setSeq(9999); // KLUDGE
			$crosswalkId = $crosswalkDao->insertCrosswalk($this->crosswalk);
			$crosswalkDao->resequenceCrosswalks();
		}

		HookRegistry::call('CrosswalkForm::execute', array(&$this, &$this->crosswalk));
	}
	
}

?>
