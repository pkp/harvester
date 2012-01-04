<?php

/**
 * @file classes/admin/form/SortOrderForm.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SortOrderForm
 * @ingroup admin_form
 * @see SortOrderForm
 *
 * @brief Form for administrator to create/edit sort orderings.
 */

// $Id$


import('lib.pkp.classes.form.Form');


class SortOrderForm extends Form {
	/** @var sortOrderId int the ID of the sort order being edited */
	var $sortOrderId;

	/** @var $fieldDao object */
	var $fieldDao;

	/** @var $sortOrderDao object */
	var $sortOrderDao;
	/**
	 * Constructor
	 * @param sortOrderId int leave as default for new sort order
	 */
	function SortOrderForm($sortOrderId = null) {
		$this->sortOrderId = isset($sortOrderId) ? (int) $sortOrderId : null;

		parent::Form('admin/sortOrderForm.tpl');

		// Sort order name is provided
		$this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'admin.sortOrders.form.nameRequired'));

		// Sort order type is valid
		import('classes.sortOrder.SortOrder');
		$this->addCheck(new FormValidatorInSet($this, 'type', 'required', 'admin.sortOrders.form.typeRequired', array_keys(SortOrder::getTypeOptions())));
		$this->addCheck(new FormValidatorPost($this));

		$this->sortOrderDao =& DAORegistry::getDAO('SortOrderDAO');
		$this->fieldDao =& DAORegistry::getDAO('FieldDAO');
	}

	/**
	 * Get a list of localized field names for this form
	 * @return array
	 */
	function getLocaleFieldNames() {
		return $this->sortOrderDao->getLocaleFieldNames();
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('sortOrderId', $this->sortOrderId);
		import('classes.sortOrder.SortOrder');
		$templateMgr->assign('typeOptions', SortOrder::getTypeOptions());

		$schemaPlugins =& PluginRegistry::loadCategory('schemas');
		$templateMgr->assign_by_ref('schemaPlugins', $schemaPlugins);

		parent::display();
	}

	/**
	 * Initialize form data from current sort order.
	 */
	function initData() {
		if (isset($this->sortOrderId)) {
			$sortOrder =& $this->sortOrderDao->getSortOrder($this->sortOrderId);

			if ($sortOrder != null) {
				$fields =& $this->fieldDao->getFieldsBySortOrder($this->sortOrderId);
				$this->_data = array(
					'name' => $sortOrder->getName(null), // Localized
					'fields' => $fields->toArray()
				);

			} else {
				$this->sortOrderId = null;
			}
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('name', 'type', 'fieldNames'));
	}

	/**
	 * Save sort order. 
	 */
	function execute() {
		if (isset($this->sortOrderId)) {
			$sortOrder =& $this->sortOrderDao->getSortOrder($this->sortOrderId);
		}

		if (!isset($sortOrder)) {
			$sortOrder = new SortOrder();
		}

		$sortOrder->setName($this->getData('name'), null); // Localized
		$sortOrder->setType($this->getData('type'));
		$sortOrder->setIsClean(false); // Index needs regenerating

		// Update or insert sort order
		if ($sortOrder->getSortOrderId() != null) {
			$this->sortOrderDao->updateSortOrder($sortOrder);
		} else {
			$this->sortOrderDao->insertSortOrder($sortOrder);
		}

		// Store the field IDs for the sort order
		$fieldNames = (array) $this->getData('fieldNames');
		$fieldIds = array();
		$schemaPlugins =& PluginRegistry::loadCategory('schemas');
		foreach ($schemaPlugins as $schemaPlugin) {
			$schema =& $schemaPlugin->getSchema();
			$schemaId = $schema->getSchemaId();
			foreach ($schemaPlugin->getFieldList() as $fieldName) {
				if (isset($fieldNames[$schemaId]) && $fieldNames[$schemaId] == $fieldName) {
					$field =& $this->fieldDao->buildField($fieldName, $schemaPlugin->getName());
					$fieldIds[] = $field->getFieldId();
					unset($field);
				}
				
			}
			unset($schema);
		}

		$this->sortOrderDao->setSortOrderFields(
			$sortOrder->getSortOrderId(),
			$fieldIds
		);
	}
}

?>
