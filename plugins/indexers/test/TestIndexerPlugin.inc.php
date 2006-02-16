<?php

/**
 * TestIndexerPlugin.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 *
 * Test indexer plugin
 *
 * $Id$
 */

import('plugins.IndexerPlugin');

class TestIndexerPlugin extends IndexerPlugin {
	/**
	 * Register the plugin.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	function getName() {
		return 'TestIndexerPlugin';
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.indexers.test.description');
	}

	function displayAdminForm(&$indexer) {
		echo "ADMIN FORM FOR INDEXER " . $indexer->getIndexerId() . "<br/>\n";
	}
}

?>
