<?php

/**
 * ModsPlugin.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 *
 * MODS schema plugin
 *
 * $Id$
 */

import('plugins.SchemaPlugin');

class ModsPlugin extends SchemaPlugin {
	/**
	 * Register the plugin.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	function getName() {
		return 'ModsPlugin';
	}

	/**
	 * Get the display name of this plugin's protocol.
	 * @return String
	 */
	function getSchemaDisplayName() {
		return Locale::translate('plugins.schemas.mods.schemaName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.schemas.mods.description');
	}

	function &getXMLHandler(&$harvester) {
		$this->import('ModsXMLHandler');
		$handler =& new ModsXMLHandler(&$harvester);
		return $handler;
	}

	function getFieldList() {
		static $fieldList;
		if (!isset($fieldList)) {
			$fieldList = array(
				'identifier',
				'title',
				'subTitle',
				'partNumber',
				'partName',
				'nonSort',
				'namePart',
				'displayForm',
				'affiliation',
				'role',
				'roleTerm',
				'typeOfResource',
				'genre',
				'placeTerm',
				'publisher',
				// dateIssued, dateCreated, dateCaptured, dateValid, dateModified, copyrightDate, dateOther, recordCreationDate, recordChangeDate
				'edition',
				'issuance',
				'frequency',
				'languageTerm',
				'form',
				'reformattingQuality',
				'internetMediaType',
				'extent',
				'digitalOrigin',
				'physicalDescriptionNote',
				'abstract',
				'tableOfContents',
				'targetAudience',
				'topic',
				'geographic',
				'temporal',
				'geographicCode',
				'continent',
				'country',
				'province',
				'region',
				'state',
				'territory',
				'county',
				'city',
				'island',
				'area',
				'scale',
				'projection',
				'coordinates',
				'occupation',
				'classification',
				'physicalLocation',
				'url',
				'accessCondition',
				'extension',
				'recordContentSource',
				'recordIdentifier',
				'recordOrigin',
				'languageOfCataloging',
				'note'
			);
		}
		return $fieldList;
	}

	function getFieldName($fieldSymbolic, $locale = null) {
		return Locale::translate("plugins.schemas.mods.fields.$fieldSymbolic.name", $locale);
	}

	function getFieldDescription($fieldSymbolic, $locale = null) {
		return Locale::translate("plugins.schemas.mods.fields.$fieldSymbolic.description", $locale);
	}

	/**
	 * Get a URL for the supplied record, if available; null otherwise.
	 * @param $record object
	 * @param $entries array
	 * @return string
	 */
	function getUrl(&$record, $entries) {
		$returner = null;
		if (isset($entries['identifier']) && preg_match('/^[a-z]+:\/\//', $entries['identifier'])) {
			$returner = $entries['identifier'];
		}
		return $returner;
	}
}

?>
