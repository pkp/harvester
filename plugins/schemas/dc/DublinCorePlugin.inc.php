<?php

/**
 * DublinCorePlugin.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 *
 * Dublin Core schema plugin
 *
 * $Id$
 */

import('plugins.SchemaPlugin');

class DublinCorePlugin extends SchemaPlugin {
	/**
	 * Register the plugin.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	function getName() {
		return 'DublinCorePlugin';
	}

	/**
	 * Get the display name of this plugin's protocol.
	 * @return String
	 */
	function getSchemaDisplayName() {
		return Locale::translate('plugins.schemas.dc.schemaName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.schemas.dc.description');
	}

	function &getXMLHandler(&$harvester) {
		$this->import('DublinCoreXMLHandler');
		$handler =& new DublinCoreXMLHandler(&$harvester);
		return $handler;
	}

	function getFieldList() {
		static $fieldList;
		if (!isset($fieldList)) {
			$fieldList = array(
				'identifier',
				'abstract',
				'title',
				'creator',
				'publisher',
				'contributor',
				'date',
				'type',
				'format',
				'language',
				'relation',
				'coverage',
				'rights',
				'subject',
				'description',
				'source'
			);
		}
		return $fieldList;
	}

	/**
	 * Get a list of the fields that can be used to sort in the browse list.
	 * @return array
	 */
	function getSortFields() {
		return array('title', 'date');
	}

	function getFieldName($fieldSymbolic, $locale = null) {
		return Locale::translate("plugins.schemas.dc.fields.$fieldSymbolic.name", $locale);
	}

	function getFieldDescription($fieldSymbolic, $locale = null) {
		return Locale::translate("plugins.schemas.dc.fields.$fieldSymbolic.description", $locale);
	}

	/**
	 * Get a URL for the supplied record, if available; null otherwise.
	 * @param $record object
	 * @param $entries array
	 * @return string
	 */
	function getUrl(&$record, $entries) {
		if (is_array($entries['identifier'])) foreach ($entries['identifier'] as $entry) {
			if (preg_match('/^[a-z]+:\/\//', $entry['value'])) {
				return $entry['value'];
			}
		}
		return null;
	}

	function getFieldType($fieldName) {
		switch ($fieldName) {
			case 'date':
				return FIELD_TYPE_DATE;
			case 'language':
				return FIELD_TYPE_SELECT;
			default:
				return FIELD_TYPE_STRING;
		}
	}
}

?>
