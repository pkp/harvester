<?php

/**
 * @file EtdmsPlugin.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * Edited and modified by Kennedy Onyancha - DoKS (KHK Kempen) (2007)
 * @package plugins.schemas.etdms
 * @class EtdmsPlugin
 *
 * Etdms schema plugin
 *
 * $Id$
 */

import('plugins.SchemaPlugin');

class EtdmsPlugin extends SchemaPlugin {
	/**
	 * Register the plugin.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	function getName() {
		return 'EtdmsPlugin';
	}

	/**
	 * Get the display name of this plugin's protocol.
	 * @return String
	 */
	function getSchemaDisplayName() {
		return Locale::translate('plugins.schemas.etdms.schemaName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.schemas.etdms.description');
	}

	function &getXMLHandler(&$harvester) {
		$this->import('EtdmsXMLHandler');
		$handler = new EtdmsXMLHandler($harvester);
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
				'source',
				'name',
				'level',
				'discipline',
				'grantor' 
			);
		}
		HookRegistry::call('EtdmsPlugin::getFieldList', array(&$this, &$fieldList));
		return $fieldList;
	}

	/**
	 * Get a list of the fields that can be used to sort in the browse list.
	 * @return array
	 */
	function getSortFields() {
		$returner = array('title', 'date');
		HookRegistry::call('EtdmsPlugin::getSortFields', array(&$this, &$returner));
		return $returner;
	}

	function getFieldName($fieldSymbolic, $locale = null) {
		return Locale::translate("plugins.schemas.etdms.fields.$fieldSymbolic.name", $locale);
	}

	function getFieldDescription($fieldSymbolic, $locale = null) {
		return Locale::translate("plugins.schemas.etdms.fields.$fieldSymbolic.description", $locale);
	}

	/**
	 * Get a URL for the supplied record, if available; null otherwise.
	 * @param $record object
	 * @param $entries array optional
	 * @return string
	 */
	function getUrl(&$record, $entries = null) {
		if ($entries === null) $entries = $record->getEntries();
		if (is_array($entries['identifier'])) foreach ($entries['identifier'] as $entry) {
			if (preg_match('/^[a-z]+:\/\//', $entry['value'])) {
				return $entry['value'];
			}
		}
		return null;
	}

	/**
	 * Get the authors for the supplied record, if available; null otherwise
	 * @param $record object
	 * @param $entries array
	 * @return array
	 */
	function getAuthors(&$record, $entries = null) {
		if ($entries === null) $entries = $record->getEntries();
		$returner = array();
		if (is_array($entries['creator'])) foreach ($entries['creator'] as $entry) {
			$returner[] = $entry['value'];
		}
		return $returner;
	}

	/**
	 * Get the title for the supplied record, if available; null otherwise.
	 * @param $record object
	 * @param $entries array
	 * @return string
	 */
	function getTitle(&$record, $entries = null) {
		if ($entries === null) $entries = $record->getEntries();
		$returner = null;
		if (is_array($entries['title'])) foreach ($entries['title'] as $entry) {
			return $entry['value'];
		}
		return $returner;
	}

	function getFieldType($fieldName) {
		switch ($fieldName) {
			case 'date':
				$returner = FIELD_TYPE_DATE;
				break;
			case 'language':
				$returner = FIELD_TYPE_SELECT;
				break;
			default:
				$returner = FIELD_TYPE_STRING;
				break;
		}
		HookRegistry::call('EtdmsPlugin::getFieldType', array(&$this, $fieldName, &$returner));
		return $returner;
	}
}
?>
