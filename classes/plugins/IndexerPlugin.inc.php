<?php

/**
 * IndexerPlugin.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 *
 * Abstract class for indexer plugins
 *
 * $Id$
 */

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
		}
		return $success;
	}

	/**
	 * Get the display name of this plugin's protocol.
	 * @return String
	 */
	function getIndexerDisplayName() {
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
	 * Display the fields for the administration form for this indexer.
	 * @param $indexer object
	 */
	function displayAdminForm(&$indexer) {
		fatalError('ABSTRACT CLASS');
	}

	/**
	 * Get an array of the names of the admin form fields for the
	 * specified indexer.
	 * @param $indexer object
	 * @return array
	 */
	function getAdminFormFields(&$indexer) {
		fatalError('ABSTRACT CLASS');
	}

	/**
	 * Initialize the admin form with data for the specified indexer.
	 * @param $indexer object
	 * @param $form object
	 */
	function initAdminFormData(&$indexer, &$form) {
		fatalError('ABSTRACT CLASS');
	}

	/**
	 * Save the admin form fields for the specified indexer.
	 * @param $indexer object
	 */
	function saveAdminForm(&$indexer) {
		fatalError('ABSTRACT CLASS');
	}
}

?>
