<?php

/**
 * SchemaPlugin.inc.php
 *
 * Copyright (c) 2005-2006 Alec Smecher and John Willinsky
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
			// Make sure postprocessors are loaded.
			PluginRegistry::loadCategory('postprocessors');
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
	 * Get a list of the fields that can be used to sort in the browse list.
	 * @return array
	 */
	function getSortFields() {
		return array();
	}

	function getFieldId($fieldName) {
		$fieldDao =& DAORegistry::getDAO('FieldDAO');
		$field =& $fieldDao->buildField($fieldName, $this->getName());
		return $field->getFieldId();
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

	function getRtVersion(&$archive) {
		// Get the Reading Tools, if any.
		$rtDao =& DAORegistry::getDAO('RTDAO');
		$versionId = $archive->getSetting('rtVersionId');
		$version =& $rtDao->getVersion($versionId, $archive->getArchiveId());
		$defineTermsContextId = null;

		if ($version === null) { // Fall back on the site default
			$site =& Request::getSite();
			$versionId = $site->getSetting('rtVersionId');
			$version =& $rtDao->getVersion($versionId, null);
		}

		if ($version) {
			// Determine the "Define Terms" context ID.
			foreach ($version->getContexts() as $context) {
				if ($context->getDefineTerms()) {
					$defineTermsContextId = $context->getContextId();
					break;
				}
			}
		}
		return array(&$version, $defineTermsContextId);
	}

	/**
	 * Display a record.
	 */
	function displayRecord(&$record) {
		$templateMgr =& TemplateManager::getManager();

		$archive =& $record->getArchive();
		$templateMgr->assign_by_ref('archive', $archive);
		$templateMgr->assign_by_ref('record', $record);
		$templateMgr->assign('entries', $record->getEntries());

		list($version, $defineTermsContextId) = $this->getRtVersion($archive);

		if ($version) {
			$templateMgr->assign('sidebarTemplate', 'rt/rt.tpl');
			$templateMgr->assign_by_ref('version', $version);
			$templateMgr->assign('defineTermsContextId', $defineTermsContextId);
		}

		$templateMgr->display($this->getTemplatePath() . 'record.tpl', null);
	}

	/**
	 * Get a URL for the supplied record, if available; null otherwise.
	 * @param $record object
	 * @param $entries array
	 * @return string
	 */
	function getUrl(&$record, $entries = null) {
		return null;
	}

	/**
	 * Get the title for the supplied record, if available; null otherwise.
	 * @param $record object
	 * @param $entries array
	 * @return string
	 */
	function getTitle(&$record, $entries = null) {
		return null;
	}

	/**
	 * Get the authors for the supplied record, if available; null otherwise
	 * @param $record object
	 * @param $entries array
	 * @return array
	 */
	function getAuthors(&$record, $entries = null) {
		return null;
	}

	/**
	 * Get the author string for the supplied record, if available; null otherwise.
	 * @param $record object
	 * @param $entries array
	 * @return string
	 */
	function getAuthorString(&$record, $entries = null) {
		return (join('; ', $this->getAuthors($record, $entries)));
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
	function indexRecord(&$archive, &$record, $entries) {
		$searchDao =& DAORegistry::getDAO('SearchDAO');
		$searchDao->deleteRecordObjects($record->getRecordId());

		$fieldDao =& DAORegistry::getDAO('FieldDAO');
		$schemaPlugin =& $record->getSchemaPlugin();
		foreach ($entries as $fieldName => $entry) {
			$fieldType = $this->getFieldType($fieldName);
			foreach ($entry as $entryId => $info) {
				$field =& $fieldDao->buildField($fieldName, $this->getName());
				if (HookRegistry::call('SchemaPlugin::indexRecord', array(&$archive, &$record, &$field, &$info['value'], &$info['attributes']))) return true;
				switch ($fieldType) {
					case FIELD_TYPE_STRING:
					case FIELD_TYPE_SELECT:
						SearchIndex::updateTextIndex($record->getRecordId(), $field->getFieldId(), $info['value'], false);
						break;
					case FIELD_TYPE_DATE:
						$date = $schemaPlugin->parseDate($fieldName, $info['value'], $info['attributes']);
						$isMixedType = $schemaPlugin->isFieldMixedType($fieldName);
						if ($date !== null) SearchIndex::updateDateIndex(
							$record->getRecordId(),
							$field->getFieldId(),
							$date,
							$isMixedType?$info['value']:null,
							false
						);
						else SearchIndex::updateTextIndex($record->getRecordId(), $field->getFieldId(), $info['value'], $info['attributes'], false);
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
		if (empty($value)) return null;
		$date = strtotime($value);
		if ($date === false || $date === -1) return null;
		return date('Y-m-d H:i:s', $date);
	}

	/**
	 * Get the "importance" of this field. This is used to display subsets of the complete
	 * field list of a schema by importance.
	 * @param $name string
	 * @return int
	 */
	function getFieldImportance($name) {
		// By default, all fields are of maximum importance.
		return 0;
	}

	/**
	 * Get a list of field importance levels supported by this plugin, in .
	 * @return array
	 */
	function getSupportedFieldImportance() {
		// Default: Just maximum importance.
		return array(0);
	}
}

?>
