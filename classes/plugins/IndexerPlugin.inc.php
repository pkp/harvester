<?php

/**
 * @file IndexerPlugin.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 * @class IndexerPlugin
 *
 * Abstract class for harvester plugins
 */

// $Id$


import('classes.plugins.Plugin');

class IndexerPlugin extends Plugin {
	function IndexerPlugin() {
		parent::Plugin();
	}

	/**
	 * Register this plugin for all the appropriate hooks.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		if ($success) {
			//HookRegistry::register('ArchiveForm::display', array(&$this, '_displayArchiveForm'));
		}
		return $success;
	}

	/**
	 * Get the display name of this plugin's protocol.
	 * @return String
	 */
	function getProtocolDisplayName() {
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
	 * Index a record
	 * @param $record object
	 * @param $schemaPlugin object
	 * @return boolean
	 */
	function indexRecord(&$record, &$schemaPlugin) {
		fatalError('ABSTRACT CLASS');
	}
}

?>
