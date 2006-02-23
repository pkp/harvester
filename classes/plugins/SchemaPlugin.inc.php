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
}

?>
