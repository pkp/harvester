<?php

/**
 * DublinCorePlugin.inc.php
 *
 * Copyright (c) 2005 The Public Knowledge Project
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

	function getInstallDataFile() {
		return $this->getPluginPath() . '/data.xml';
	}

	function &getXMLHandler(&$harvester, &$metadata) {
		$this->import('DublinCoreXMLHandler');
		$handler =& new DublinCoreXMLHandler(&$harvester, &$metadata);
		return $handler;
	}
}

?>
