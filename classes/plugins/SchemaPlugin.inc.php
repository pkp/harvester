<?php

/**
 * SchemaPlugin.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 *
 * Abstract class for schema plugins
 *
 * $Id$
 */

import('field.Field');

class SchemaPlugin extends Plugin {
	function SchemaPlugin() {
		parent::Plugin();
	}

	/**
	 * Register this plugin for all the appropriate hooks.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		if ($success) {
		}
		return $success;
	}

	/**
	 * Get the display name of this plugin's protocol.
	 * @return String
	 */
	function getSchemaDisplayName() {
		fatalError('ABSTRACT CLASS');
	}

	/**
	 * Get the symbolic name of this plugin. Should be unique within
	 * the category.
	 */
	function getName() {
		fatalError('ABSTRACT CLASS');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		fatalError('ABSTRACT CLASS');
	}

	/**
	 * Get an XML handler for this schema.
	 */
	function &getXMLHandler(&$harvester) {
		$nullVar = null;
		return $nullVar;
	}

	/**
	 * Get a name for a field.
	 * @param $fieldSymbolic string Symbolic name for the field
	 * @param $locale string Name of locale (optional)
	 * @return string
	 */
	function getFieldName($fieldSymbolic, $locale = null) {
		return null;
	}

	/**
	 * Get a description for a field.
	 * @param $fieldSymbolic string Symbolic name for the field
	 * @param $locale string Name of locale (optional)
	 * @return string
	 */
	function getFieldDescription($fieldSymbolic, $locale = null) {
		return null;
	}

	/**
	 * Get a list of symbolic names for fields in this schema.
	 * @return array
	 */
	function getFieldList() {
		fatalError('ABSTRACT CLASS!');
	}

	/**
	 * Display a record summary.
	 */
	function displayRecordSummary(&$record) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('record', $record);
		$templateMgr->assign('entries', $record->getEntries());
		$templateMgr->display($this->getTemplatePath() . 'summary.tpl', null);
	}

	/**
	 * Display a record.
	 */
	function displayRecord(&$record) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('record', $record);
		$templateMgr->assign_by_ref('archive', $record->getArchive());
		$templateMgr->assign('entries', $record->getEntries());
		$templateMgr->display($this->getTemplatePath() . 'record.tpl', null);
	}

	/**
	 * Get a URL for the supplied record, if available; null otherwise.
	 * @param $record object
	 * @param $entries array
	 * @return string
	 */
	function getUrl(&$record, $entries) {
		return null;
	}

	/**
	 * Get an indexer for the specified field
	 */
	function &getIndexer($fieldId) {
		fatalError('ABSTRACT CLASS!');
	}

	/**
	 * Get the field type for the specified field.
	 * Child classes should probably override this.
	 */
	function getFieldType($fieldId) {
		// The default type for all fields is string.
		return FIELD_TYPE_STRING;
	}

	/**
	 * Determine whether a field is mixed type (i.e. has a date indexed and text too)
	 */
	function isFieldMixedType($fieldId) {
		return false; // Default to single-use
	}

	/**
	 * Index the given record.
	 */
	function indexRecord(&$record, $entries) {
		$fieldDao =& DAORegistry::getDAO('FieldDAO');
		$schemaPlugin =& $record->getSchemaPlugin();
		foreach ($entries as $fieldName => $entry) {
			$fieldType = $this->getFieldType($fieldName);
			foreach ($entry as $entryId => $info) {
				$field =& $fieldDao->buildField($fieldName, $this->getName());
				switch ($fieldType) {
					case FIELD_TYPE_STRING:
						SearchIndex::updateTextIndex($record->getRecordId(), $field->getFieldId(), $info['value'], $info['attributes']);
						break;
					case FIELD_TYPE_DATE:
						$date = $schemaPlugin->parseDate($fieldName, $info['value'], $info['attributes']);
						$isMixedType = $schemaPlugin->isFieldMixedType($fieldName);
						if ($date !== null) SearchIndex::updateDateIndex(
							$record->getRecordId(),
							$field->getFieldId(),
							$date,
							$isMixedType?$info['value']:null,
							$info['attributes']
						);
						break;
				}
				unset($field);
			}
		}
	}

	/**
	 * Parse a date into a value suitable for indexing.
	 * @return int timestamp or string date, or null on failure
	 */
	function parseDate($fieldName, $value, $attributes = null) {
		$date = strtotime($value);
		if ($date === false || $date === -1) return null;
		return date('Y-m-d H:i:s', $date);
	}
}

?>
