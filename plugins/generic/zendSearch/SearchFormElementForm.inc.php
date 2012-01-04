<?php

/**
 * @file classes/admin/form/SearchFormElementForm.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SearchFormElementForm
 * @ingroup plugins_generic_zendSearch
 * @see SearchFormElementForm
 *
 * @brief Form for administrator to create/edit search form.
 */

// $Id$


import('lib.pkp.classes.form.Form');

class SearchFormElementForm extends Form {
	/** @var searchFormElementId int the ID of the form element being edited */
	var $searchFormElementId;

	/** @var $fieldDao object */
	var $fieldDao;

	/** @var $searchFormElementDao object */
	var $searchFormElementDao;

	/**
	 * Constructor
	 * @param $parentPluginName string Name of parent plugin
	 * @param $searchFormElementId int leave as default for new sort order
	 */
	function SearchFormElementForm($parentPluginName, $searchFormElementId = null) {
		$this->searchFormElementId = isset($searchFormElementId) ? (int) $searchFormElementId : null;
		$plugin =& PluginRegistry::getPlugin('generic', $parentPluginName);
		parent::Form($plugin->getTemplatePath() . 'searchFormElementForm.tpl');

		// Title is required
		$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'plugins.generic.zendSearch.formElement.title.required'));

		// Symbolic name is required and unique
		$this->addCheck(new FormValidatorCustom($this, 'symbolic', 'required', 'plugins.generic.zendSearch.formElement.symbolic.required', create_function('$symbolic,$form,$searchFormElementDao', 'return !$searchFormElementDao->searchFormElementExistsBySymbolic($symbolic) || ($form->getData(\'oldSymbolic\') != null && $form->getData(\'oldSymbolic\') == $symbolic);'), array(&$this, DAORegistry::getDAO('SearchFormElementDAO'))));

		// Sort order type is valid
		$this->addCheck(new FormValidatorInSet($this, 'type', 'required', 'plugins.generic.zendSearch.formElement.type.required', array_keys(SearchFormElement::getTypeMap())));
		$this->addCheck(new FormValidatorPost($this));

		$this->searchFormElementDao =& DAORegistry::getDAO('SearchFormElementDAO');
		$this->fieldDao =& DAORegistry::getDAO('FieldDAO');
	}

	/**
	 * Get a list of localized field names for this form
	 * @return array
	 */
	function getLocaleFieldNames() {
		return $this->searchFormElementDao->getLocaleFieldNames();
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('searchFormElementId', $this->searchFormElementId);
		$templateMgr->assign('typeOptions', SearchFormElement::getTypeMap());

		$schemaPlugins =& PluginRegistry::loadCategory('schemas');
		$templateMgr->assign_by_ref('schemaPlugins', $schemaPlugins);

		parent::display();
	}

	/**
	 * Initialize form data from current search form element.
	 */
	function initData() {
		if (isset($this->searchFormElementId)) {
			$searchFormElement =& $this->searchFormElementDao->getSearchFormElement($this->searchFormElementId);
			if ($searchFormElement != null) {
				$fields =& $this->searchFormElementDao->getFieldsBySearchFormElement($this->searchFormElementId);
				$this->_data = array(
					'title' => $searchFormElement->getTitle(null), // Localized
					'symbolic' => $searchFormElement->getSymbolic(),
					'type' => $searchFormElement->getType(),
					'fields' => $fields->toArray(),
					'rangeStart' => $searchFormElement->getRangeStart(),
					'rangeEnd' => $searchFormElement->getRangeEnd()
				);

			} else {
				$this->searchFormElementId = null;
			}
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		if (isset($this->searchFormElementId)) {
			$searchFormElement =& $this->searchFormElementDao->getSearchFormElement($this->searchFormElementId);
			$this->_data['oldType'] = $searchFormElement->getType();
			$this->_data['rangeStart'] = $searchFormElement->getRangeStart();
			$this->_data['rangeEnd'] = $searchFormElement->getRangeEnd();
			$this->_data['recalculateRange'] = true;
			$this->_data['recalculateOptions'] = true;
			$this->setData('oldSymbolic', $searchFormElement->getSymbolic());
		}
		$this->readUserVars(array('symbolic', 'title', 'type', 'fieldNames', 'recalculateOptions', 'recalculateRange'));
	}

	/**
	 * Save sort order. 
	 */
	function execute() {
		if (isset($this->searchFormElementId)) {
			$searchFormElement =& $this->searchFormElementDao->getSearchFormElement($this->searchFormElementId);
		}

		if (!isset($searchFormElement)) {
			$searchFormElement = new SearchFormElement();
		}

		$searchFormElement->setTitle($this->getData('title'), null); // Localized
		$searchFormElement->setSymbolic($this->getData('symbolic'), null);
		$searchFormElement->setType($this->getData('type'));

		// Update or insert
		if ($searchFormElement->getSearchFormElementId() != null) {
			$this->searchFormElementDao->updateSearchFormElement($searchFormElement);
		} else {
			$this->searchFormElementDao->insertSearchFormElement($searchFormElement);
		}

		// Store the field IDs for the search form element
		$fieldNames = (array) $this->getData('fieldNames');
		$fieldIds = array();
		$fields = array();
		$schemaPlugins =& PluginRegistry::loadCategory('schemas');
		foreach ($schemaPlugins as $schemaPlugin) {
			$schema =& $schemaPlugin->getSchema();
			$schemaId = $schema->getSchemaId();
			foreach ($schemaPlugin->getFieldList() as $fieldName) {
				if (isset($fieldNames[$schemaId]) && in_array($fieldName, (array) $fieldNames[$schemaId])) {
					$field =& $this->fieldDao->buildField($fieldName, $schemaPlugin->getName());
					$fieldIds[] = $field->getFieldId();
					$fields[$schemaPlugin->getName()][] = $fieldName;
					unset($field);
				}
				
			}
			unset($schema);
		}

		// Check whether the field list has changed
		$oldFields =& $this->searchFormElementDao->getFieldsBySearchFormElement($this->searchFormElementId);
		$oldFieldIds = array();
		while ($oldField =& $oldFields->next()) {
			$oldFieldIds[] = $oldField->getFieldId();
			unset($oldField);
		}
		$fieldsChanged = count(array_diff($oldFieldIds, $fieldIds)) != 0 || count(array_diff($fieldIds, $oldFieldIds)) != 0;

		// If the fields changed, save them and mark the element unclean
		if ($fieldsChanged) {
			$this->searchFormElementDao->setSearchFormElementFields(
				$searchFormElement->getSearchFormElementId(),
				$fieldIds
			);
			$searchFormElement->setIsClean(false);
			$this->searchFormElementDao->updateSearchFormElement($searchFormElement);
		}

		// Recalculate the range if necessary
		$rangeStart = null;
		$rangeEnd = null;
		if ($searchFormElement->getType() == SEARCH_FORM_ELEMENT_TYPE_DATE && $this->getData('recalculateRange')) {
			$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
			$recordDao =& DAORegistry::getDAO('RecordDAO');
			$archives =& $archiveDao->getArchives();
			while ($archive =& $archives->next()) {
				import('classes.sortOrder.SortOrderDAO');
				$schemaPluginName = $archive->getSchemaPluginName();
				if (isset($fields[$schemaPluginName])) {
					$schemaFields =& $fields[$schemaPluginName];
					$schemaPlugin =& $archive->getSchemaPlugin();
					$records =& $recordDao->getRecords($archive->getArchiveId());
					while ($record =& $records->next()) {
						foreach ($schemaFields as $fieldName) {
							$fieldValue = $schemaPlugin->getFieldValue($record, $fieldName, SORT_ORDER_TYPE_STRING);
							if ($rangeStart === null || strcmp($rangeStart, $fieldValue) > 0) $rangeStart = $fieldValue;
							if ($rangeEnd === null || strcmp($rangeEnd, $fieldValue) < 0) $rangeEnd = $fieldValue;
						}
						unset($record);
					}
					unset($records, $schemaFields, $schemaPlugin);
				}
				unset($archive, $schemaPlugin);
			}

			$searchFormElement->setRangeStart($rangeStart);
			$searchFormElement->setRangeEnd($rangeEnd);
			$this->searchFormElementDao->updateSearchFormElement($searchFormElement);
		} elseif ($searchFormElement->getType() == SEARCH_FORM_ELEMENT_TYPE_SELECT && $this->getData('recalculateOptions')) {
			$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
			$recordDao =& DAORegistry::getDAO('RecordDAO');
			$archives =& $archiveDao->getArchives();
			while ($archive =& $archives->next()) {
				import('classes.sortOrder.SortOrderDAO');
				$schemaPluginName = $archive->getSchemaPluginName();
				if (isset($fields[$schemaPluginName])) {
					$schemaFields =& $fields[$schemaPluginName];
					$schemaPlugin =& $archive->getSchemaPlugin();
					$records =& $recordDao->getRecords($archive->getArchiveId());
					while ($record =& $records->next()) {
						foreach ($schemaFields as $fieldName) {
							$fieldValue = $schemaPlugin->getFieldValue($record, $fieldName, SORT_ORDER_TYPE_STRING);
							if (!$this->searchFormElementDao->searchFormElementOptionExists($this->searchFormElementId, $fieldValue)) {
								$this->searchFormElementDao->insertSearchFormElementOption($this->searchFormElementId, $fieldValue);
							}
						}
						unset($record);
					}
					unset($records, $schemaFields, $schemaPlugin);
				}
				unset($archive, $schemaPlugin);
			}
		}
	}
}

?>
