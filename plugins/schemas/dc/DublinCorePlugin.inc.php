<?php

/**
 * @file DublinCorePlugin.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.schemas.dc
 * @class DublinCorePlugin
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
		HookRegistry::call('DublinCorePlugin::getFieldList', array(&$this, &$fieldList));
		return $fieldList;
	}

	/**
	 * Get a list of the fields that can be used to sort in the browse list.
	 * @return array
	 */
	function getSortFields() {
		$returner = array('title', 'date');
		HookRegistry::call('DublinCorePlugin::getSortFields', array(&$this, &$returner));
		return $returner;
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
	 * @param $entries array optional
	 * @return string
	 */
	function getUrl(&$record) {
		$parsedContents =& $record->getParsedContents();
		if (isset($parsedContents['identifier'])) {
			foreach ((array) $parsedContents['identifier'] as $url) {
				if (preg_match('/^[a-z]+:\/\//', $url)) return $url;
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
	function getAuthors(&$record) {
		$parsedContents =& $record->getParsedContents();
		if (isset($parsedContents['creator'])) {
			return $parsedContents['creator'];
		}
		return null;
	}

	/**
	 * Get the title for the supplied record, if available; null otherwise.
	 * @param $record object
	 * @param $entries array
	 * @return string
	 */
	function getTitle(&$record) {
		$parsedContents =& $record->getParsedContents();
		if (isset($parsedContents['title'])) {
			return array_shift($parsedContents['title']);
		}
		return null;
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
		HookRegistry::call('DublinCorePlugin::getFieldType', array(&$this, $fieldName, &$returner));
		return $returner;
	}

	/**
	 * Parse a record's contents into an object
	 * @param $contents string
	 * @return object
	 */
	function &parseContents(&$contents) {
		$xmlParser =& new XMLParser();
		$result =& $xmlParser->parseText($contents);

		$returner = array();
		foreach ($result->getChildren() as $child) {
			$name = $child->getName();
			$value = $child->getValue();
			if (String::substr($name, 0, 3) == 'dc:') $name = String::substr($name, 3);
			$returner[$name][] = $value;
		}

		unset($result, $xmlParser);
		return $returner;
	}
}

?>
