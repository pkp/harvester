<?php

/**
 * @file EtdmsPlugin.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * Edited and modified by Kennedy Onyancha - DoKS (KHK Kempen) (2007)
 * @package plugins.schemas.etdms
 * @class EtdmsPlugin
 *
 * Etdms schema plugin
 *
 * $Id$
 */

import('classes.plugins.SchemaPlugin');

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
		return __('plugins.schemas.etdms.schemaName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.schemas.etdms.description');
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
	 * Get the value of a field by symbolic name.
	 * @var $record object
	 * @var $name string
	 * @var $type SORT_ORDER_TYPE_...
	 * @return mixed
	 */
	function getFieldValue(&$record, $name, $type) {
		$fieldValue = null;
		$parsedContents = $record->getParsedContents();
		if (isset($parsedContents[$name])) switch ($type) {
			case SORT_ORDER_TYPE_STRING:
				$fieldValue = join(';', $parsedContents[$name]);
				break;
			case SORT_ORDER_TYPE_NUMBER:
				$fieldValue = (int) array_shift($parsedContents[$name]);
				break;
			case SORT_ORDER_TYPE_DATE:
				$fieldValue = strtotime($thing = array_shift($parsedContents[$name]));
				if ($fieldValue === -1 || $fieldValue === false) $fieldValue = null;
				break;
			default:
				fatalError('UNKNOWN TYPE');
		}
		HookRegistry::call('EtdmsPlugin::getFieldValue', array(&$this, &$fieldValue));
		return $fieldValue;
	}

	function getFieldName($fieldSymbolic, $locale = null) {
		return __("plugins.schemas.etdms.fields.$fieldSymbolic.name", $locale);
	}

	function getFieldDescription($fieldSymbolic, $locale = null) {
		return __("plugins.schemas.etdms.fields.$fieldSymbolic.description", $locale);
	}

	/**
	 * Get a URL for the supplied record, if available; null otherwise.
	 * @param $record object
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
	 * @return array
	 */
	function getAuthors(&$record) {
		$parsedContents =& $record->getParsedContents();
		if (isset($parsedContents['creator'])) {
			return $parsedContents['creator'];
		}
		return array();
	}

	/**
	 * Get the title for the supplied record, if available; null otherwise.
	 * @param $record object
	 * @return string
	 */
	function getTitle(&$record) {
		$parsedContents =& $record->getParsedContents();
		if (isset($parsedContents['title'])) {
			return array_shift($parsedContents['title']);
		}
		return null;
	}

	/**
	 * Get the identifier for the supplied record.
	 * @param $record object
	 * @return string
	 */
	function getIdentifier(&$record) {
		$parsedContents =& $record->getParsedContents();
		if (isset($parsedContents['identifier'])) {
			return array_shift($parsedContents['identifier']);
		}
		return null;
	}

	/**
	 * Parse a record's contents into an object
	 * @param $contents string
	 * @return object
	 */
	function &parseContents(&$contents) {
		$xmlParser = new XMLParser();
		$result =& $xmlParser->parseText($contents);

		$returner = array();
		foreach ($result->getChildren() as $child) {
			$name = $child->getName();
			$value = $child->getValue();
			if (String::substr($name, 0, 6) == 'etdms:') $name = String::substr($name, 3);
			$returner[$name][] = $value;
		}

		$result->destroy();
		$xmlParser->destroy();
		unset($result, $xmlParser);

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

	function getMetadataPrefix() {
		return 'oai_etdms';
	}

	function getFormatClass() {
		$this->import('OAIMetadataFormat_ETDMS');
		return 'OAIMetadataFormat_ETDMS';
	}

	function getSchemaName() {
		return 'http://www.ndltd.org/standards/metadata/etdms/1.0/etdms.xsd';
	}

	function getNamespace() {
		return 'http://www.ndltd.org/standards/metadata/etdms/1.0/';
	}
}

?>
