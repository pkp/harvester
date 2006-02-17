<?php

/**
 * SearchableFieldForm.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package admin.form
 *
 * Form for site administrator to edit searchable field settings.
 *
 * $Id$
 */

import('db.DBDataXMLParser');
import('form.Form');

class SearchableFieldForm extends Form {
	/** Indexer DAO */
	var $indexerDao;

	/** The ID of the searchable field being edited */
	var $searchableFieldId;

	/** The searchable field object */
	var $searchableField;

	/**
	 * Constructor.
	 * @param $searchableFieldId omit for a new searchable field
	 */
	function SearchableFieldForm($searchableFieldId = null) {
		parent::Form('admin/searchableFieldForm.tpl');

		$this->indexerDao =& DAORegistry::getDAO('IndexerDAO');

		$this->searchableFieldId = isset($searchableFieldId) ? (int) $searchableFieldId : null;
		
		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'name', 'required', 'admin.indexing.form.nameRequired'));
		$this->addCheck(new FormValidator($this, 'description', 'required', 'admin.indexing.form.descriptionRequired'));

		$this->harvesterPlugin = Request::getUserVar('harvesterPlugin');

		if ($searchableFieldId) {
			$searchableFieldDao =& DAORegistry::getDAO('SearchableFieldDAO');
			$this->searchableField =& $searchableFieldDao->getSearchableFieldById($this->searchableFieldId);
		}

		HookRegistry::call('SearchableFieldForm::SearchableFieldForm', array(&$this));
	}
	
	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign('searchableFieldId', $this->searchableFieldId);
		$templateMgr->assign('helpTopicId', 'site.siteManagement');
		parent::display();
	}
	
	/**
	 * Initialize form data from current settings.
	 */
	function initData() {
		if (isset($this->searchableField)) {
			$indexers =& $this->indexerDao->getIndexersBySearchableFieldId($this->searchableFieldId);
			$indexers = $indexers->toArray();

			$this->_data = array(
				'name' => $this->searchableField->getName(),
				'description' => $this->searchableField->getDescription(),
				'indexers' => &$indexers
			);

			// Give the indexers a chance to initialize the form
			foreach ($indexers as $indexer) {
				$indexerPlugin =& $indexer->getIndexerPlugin();
				$indexerPlugin->initAdminFormData($indexer, $this);
			}
		} else {
			$this->searchableFieldId = null;
			$this->_data = array(
			);
		}

		$this->_data['indexerPlugins'] =& PluginRegistry::loadCategory('indexers');

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
		$parameterNames = array('name', 'description');

		$indexers =& $this->indexerDao->getIndexersBySearchableFieldId($this->searchableFieldId);

		// Add the form parameters for the indexers on this page
		while ($indexer =& $indexers->next()) {
			$indexerPlugin =& $indexer->getIndexerPlugin();
			$parameterNames = array_merge($parameterNames, $indexerPlugin->getAdminFormFields($indexer));
		}
		return $parameterNames;
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars($this->getParameterNames());
	}
	
	/**
	 * Save searchable field settings.
	 */
	function execute() {
		$searchableFieldDao = &DAORegistry::getDAO('SearchableFieldDAO');
		
		if (!isset($this->searchableField)) {
			$this->searchableField = &new SearchableField();
		}

		$this->searchableField->setName($this->getData('name'));
		$this->searchableField->setDescription($this->getData('description'));

		if ($this->searchableField->getSearchableFieldId() != null) {
			$searchableFieldDao->updateSearchableField($this->searchableField);
			$indexers =& $this->indexerDao->getIndexersBySearchableFieldId($this->searchableFieldId);

			// Allow the indexers to save their parameters
			while ($indexer =& $indexers->next()) {
				$indexerPlugin =& $indexer->getIndexerPlugin();
				$indexerPlugin->saveAdminForm($indexer, $this);
			}
		} else {
			$this->searchableField->setSeq(9999); // KLUDGE
			$searchableFieldId = $searchableFieldDao->insertSearchableField($this->searchableField);
			$searchableFieldDao->resequenceSearchableFields();
		}

		HookRegistry::call('SearchableFieldForm::execute', array(&$this, &$this->searchableField));
	}
	
}

?>
