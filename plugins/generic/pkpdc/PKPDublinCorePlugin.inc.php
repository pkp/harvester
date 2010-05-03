<?php

/**
 * @file PKPDublinCorePlugin.inc.php
 *
 * Copyright (c) 2005-2010 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.pkpdc
 * @class PKPDublinCorePlugin
 *
 * PKP extender for Dublin Core schema plugin
 *
 */

// $Id$


import('lib.pkp.classes.plugins.GenericPlugin');

class PKPDublinCorePlugin extends GenericPlugin {
	/** @var $currentRecord object */
	var $currentRecord;

	/** @var $subjectIndex int */
	var $subjectIndex;

	/** @var $fieldDao object */
	var $fieldDao;

	/** @var $recordDao object */
	var $recordDao;

	/**
	 * Register the plugin.
	 */
	function register($category, $path) {
		if (!Config::getVar('general', 'installed')) return false;
		$success = parent::register($category, $path);
		if ($success) {
			if ($this->getEnabled()) {
				HookRegistry::register('DublinCorePlugin::getFieldList', array(&$this, '_getFieldListCallback'));
				HookRegistry::register('DublinCorePlugin::getFieldType', array(&$this, '_getFieldTypeCallback'));
				HookRegistry::register('Harvester::insertEntry', array(&$this, '_insertEntryCallback'));
				HookRegistry::register('Template::Admin::Archives::displayHarvesterForm', array(&$this, '_displayHarvesterForm'));
				HookRegistry::register('ArchiveForm::getParameterNames', array(&$this, '_getArchiveFormParameterNames'));
				HookRegistry::register('ArchiveForm::initData', array(&$this, '_readAdditionalFormData'));
				HookRegistry::register('ArchiveForm::execute', array(&$this, '_saveAdditionalFormData'));
			}
			$this->currentRecord = null;
			$this->recordDao =& DAORegistry::getDAO('RecordDAO');
		}
		return $success;
	}

	/**
	 * Add two new fields, "discipline" and "subjectClass", to the regular
	 * Dublin Core set.
	 */
	function _getFieldListCallback($hookName, $params) {
		$dublinCorePlugin =& $params[0];
		$fieldList =& $params[1];

		// Add "discipline" and "subject classification"
		// to the list of Dublin Core fields
		$fieldList[] = 'discipline';
		$fieldList[] = 'subjectClass';

		// We want to allow other hook registrants after us
		return false;
	}

	/**
	 * Provide types for the two added fields.
	 */
	function _getFieldTypeCallback($hookName, $params) {
		$dublinCorePlugin =& $params[0];
		$fieldName = $params[1];
		$type =& $params[2];

		switch ($fieldName) {
			case 'discipline': $type = FIELD_TYPE_STRING; return true;
			case 'subjectClass': $type = FIELD_TYPE_SELECT; return true;
		}
		return false;
	}

	/**
	 * Handle insertion of a new entry when harvesting, potentially
	 * diverting data into the two new fields from the "subject" field.
	 */
	function _insertEntryCallback($hookName, $args) {
		$archive =& $args[0];
		$record =& $args[1];
		$field =& $args[2];
		$value =& $args[3];
		$attributes =& $args[4];
		$parentEntryId =& $args[5];

		$pkpDcHandling = $archive->getSetting('pkpDcHandling');

		// Make sure this is an OAI/DC/OJS harvest of the subject field
		if (
			$archive->getSchemaPluginName() != 'DublinCorePlugin' ||
			$archive->getHarvesterPluginName() != 'OAIHarvesterPlugin' ||
			empty($pkpDcHandling) ||
			$field->getName() != 'subject'
		) return false;

		// See if this is a new record. If so, reset the subject index.
		if ($this->currentRecord !== $record) {
			// We're starting a new record; reset the index.
			$this->subjectIndex = 0;
			unset($this->currentRecord);
			$this->currentRecord =& $record;
		}

		// Depending on the OJS version being harvested, divert
		// data to the other fields as required.
		switch ($pkpDcHandling) {
			case '2.x': // discipline, subject, subject_class
				switch ($this->subjectIndex) {
					case 0: $newField =& $this->fieldDao->buildField('discipline', 'DublinCorePlugin'); break;
					case 2: $newField =& $this->fieldDao->buildField('subjectClass', 'DublinCorePlugin'); break;
				}
				break;
			default: // 1.x: discipline, topic+
				switch ($this->subjectIndex) {
					case 0: $newField =& $this->fieldDao->buildField('discipline', 'DublinCorePlugin'); break;
				}
				break;
		}

		$this->subjectIndex++;

		if (isset($newField)) {
			// Override the default behavior.
			$this->recordDao->insertEntry(
				$record->getRecordId(),
				$newField->getFieldId(),
				$value,
				$attributes,
				$parentEntryId
			);
			return true;
		}

		return false; // Otherwise, allow regular handling
	}

	/**
	 * Add the OJS version selector to the archive form.
	 */
	function _displayHarvesterForm($hookName, $args) {
		$params =& $args[0];
		$smarty =& $args[1];
		$output =& $args[2];

		if (!isset($params['plugin']) || $params['plugin'] != 'OAIHarvesterPlugin') return false;

		$output .= $smarty->fetch($this->getTemplatePath() . '/harvesterForm.tpl');

		return false;
	}

	/**
	 * Add the field for the OJS version selector to the archive form.
	 */
	function _getArchiveFormParameterNames($hookName, $args) {
		$form =& $args[0];
		$parameterNames =& $args[1];
		$harvesterPlugin = $args[2];
		if ($harvesterPlugin == 'OAIHarvesterPlugin') {
			$parameterNames[] = 'pkpDcHandling';
		}
		return false;
	}

	/**
	 * Read the current OJS version selector into the form, if necessary
	 */
	function _readAdditionalFormData($hookName, $args) {
		$form =& $args[0];
		$archive =& $args[1];
		$harvesterPlugin =& $args[2];
		if ($archive && $harvesterPlugin == 'OAIHarvesterPlugin') {
			$form->setData('pkpDcHandling', $archive->getSetting('pkpDcHandling'));
		}
		return false;
	}

	/**
	 * Save any new value for the OJS version selector
	 */
	function _saveAdditionalFormData($hookName, $args) {
		$form =& $args[0];
		$archive =& $args[1];
		$harvesterPlugin =& $args[2];
		if ($harvesterPlugin == 'OAIHarvesterPlugin') {
			$archive->updateSetting('pkpDcHandling', Request::getUserVar('pkpDcHandling'));
		}
	}

	/**
	 * Get the display name of this plugin's protocol.
	 * @return String
	 */
	function getDisplayName() {
		return Locale::translate('plugins.generic.pkpdc.name');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.generic.pkpdc.description');
	}
}

?>
