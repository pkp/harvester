<?php

/**
 * @file classes/plugins/SchemaPlugin.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 * @class SchemaPlugin
 *
 * @brief Abstract class for schema plugins
 */

// $Id$


import('classes.plugins.Plugin');

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
			HookRegistry::register('OAI::metadataFormats', array(&$this, 'callback_formatRequest'));
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
	 * Get a name for a field.
	 * @param $fieldSymbolic string Symbolic name for the field
	 * @param $locale string Name of locale (optional)
	 * @return string
	 */
	function getFieldName($fieldSymbolic, $locale = null) {
		return null;
	}

	/**
	 * Get the ID for a field, building it if necessary.
	 */
	function getFieldId($fieldName) {
		$fieldDao =& DAORegistry::getDAO('FieldDAO');
		$field =& $fieldDao->buildField($fieldName, $this->getName());
		return $field->getFieldId();
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
	 * Get the value of a field by symbolic name for sort indexing.
	 * @var $record object
	 * @var $name string
	 * @return mixed null on failure
	 */
	function getFieldValue(&$record, $name, $type) {
		fatalError('ABSTRACT CLASS!');
	}

	/**
	 * Get the schema object associated with this plugin
	 */
	function &getSchema() {
		$schemaDao =& DAORegistry::getDAO('SchemaDAO');
		$schema =& $schemaDao->buildSchema($this->getName());
		return $schema;
	}

	/**
	 * Parse a record's contents into an object
	 * @param $contents string
	 * @return object
	 */
	function &parseContents(&$contents) {
		fatalError('ABSTRACT CLASS!');
	}

	/**
	 * Display a record summary.
	 */
	function displayRecordSummary(&$record) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('record', $record);
		$templateMgr->display($this->getTemplatePath() . 'summary.tpl', null, 'SchemaPlugin::displayRecordSummary');
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
			$version =& $rtDao->getVersion($versionId, 0);
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

		list($version, $defineTermsContextId) = $this->getRtVersion($archive);

		if ($version) {
			$templateMgr->assign_by_ref('version', $version);
			$templateMgr->assign('defineTermsContextId', $defineTermsContextId);
		}

		$templateMgr->display($this->getTemplatePath() . 'record.tpl', null, 'SchemaPlugin::displayRecord');
	}

	/**
	 * Get a URL for the supplied record, if available; null otherwise.
	 * @param $record object
	 * @return string
	 */
	function getUrl(&$record) {
		return null;
	}

	/**
	 * Get the title for the supplied record, if available; null otherwise.
	 * @param $record object
	 * @return string
	 */
	function getTitle(&$record) {
		return null;
	}

	/**
	 * Get the authors for the supplied record, if available
	 * @param $record object
	 * @return array
	 */
	function getAuthors(&$record) {
		return array();
	}

	/**
	 * Get the author string for the supplied record, if available; null otherwise.
	 * @param $record object
	 * @return string
	 */
	function getAuthorString(&$record) {
		return (join('; ', $this->getAuthors($record)));
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
	 * Get the metadata prefix for this plugin's format.
	 */
	function getMetadataPrefix() {
		return 'Base OAIMetadata plugin class: abstract function getMetadataPrefix()';
	}

	function getSchemaName() {
		return '';
	}

	function getNamespace() {
		return '';
	}

	/**
	 * Get a hold of the class that does the formatting.
	 */
	function getFormatClass() {
		return 'Base OAIMetadata plugin class: abstract function getFormatClass()';
	}

	function callback_formatRequest($hookName, $args) {
		$namesOnly = $args[0];
		$identifier = $args[1];
		$formats =& $args[2];

		if ($namesOnly) {
			$formats = array_merge($formats,array($this->getMetadataPrefix()));
		} else {
			$formatClass = $this->getFormatClass();
			$formats = array_merge(
				$formats,
				array($this->getMetadataPrefix() => new $formatClass($this->getMetadataPrefix(), $this->getSchemaName(), $this->getNamespace()))
			);
		}
		return false;
	}
}

?>
