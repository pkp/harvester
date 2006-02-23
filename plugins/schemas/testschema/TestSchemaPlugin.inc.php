<?php

/**
 * TestSchemaPlugin.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 *
 * Test schema plugin
 *
 * $Id$
 */

import('plugins.SchemaPlugin');

class TestSchemaPlugin extends SchemaPlugin {
	/**
	 * Register the plugin.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	function getName() {
		return 'TestSchemaPlugin';
	}

	/**
	 * Get the display name of this plugin's protocol.
	 * @return String
	 */
	function getSchemaDisplayName() {
		return Locale::translate('plugins.schemas.test.schemaName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.schemas.test.description');
	}

	function &getXMLHandler(&$harvester) {
		fatalError('THIS IS NOT AN ACTUAL SCHEMA');
	}

	function getFieldList() {
		static $fieldList;
		if (!isset($fieldList)) {
			$fieldList = array(
				'fieldOne',
				'fieldTwo',
				'fieldThree'
			);
		}
		return $fieldList;
	}

	function getFieldName($fieldSymbolic, $locale = null) {
		return Locale::translate("plugins.schemas.test.fields.$fieldSymbolic.name", $locale);
	}

	function getFieldDescription($fieldSymbolic, $locale = null) {
		return Locale::translate("plugins.schemas.test.fields.$fieldSymbolic.description", $locale);
	}
}

?>
