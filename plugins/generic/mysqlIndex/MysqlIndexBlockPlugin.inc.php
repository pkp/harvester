<?php

/**
 * @file MysqlIndexBlockPlugin.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MysqlIndexBlockPlugin
 * @ingroup plugins_blocks_mysqlIndex
 *
 * @brief Class for search block plugin
 */

// $Id$


import('plugins.BlockPlugin');

class MysqlIndexBlockPlugin extends BlockPlugin {
	function register($category, $path) {
		$success = parent::register($category, $path);
		if ($success) {
			$this->addLocaleData();
		}
		return $success;
	}

	/**
	 * Get the supported contexts (e.g. BLOCK_CONTEXT_...) for this block.
	 * @return array
	 */
	function getSupportedContexts() {
		return array(BLOCK_CONTEXT_LEFT_SIDEBAR, BLOCK_CONTEXT_RIGHT_SIDEBAR);
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'MysqlIndexBlockPlugin';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return Locale::translate('plugins.generic.mysqlIndex.blockDisplayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.generic.mysqlIndex.blockDescription');
	}

	/**
	 * Get the Zend Search plugin
	 * @return object
	 */
	function &getMysqlIndexPlugin() {
		$plugin =& PluginRegistry::getPlugin('generic', 'MysqlIndexPlugin');
		return $plugin;
	}

	/**
	 * Override the builtin to get the correct plugin path.
	 * @return string
	 */
	function getPluginPath() {
		$plugin =& $this->getMysqlIndexPlugin();
		return $plugin->getPluginPath();
	}

	/**
	 * Override the builtin to get the correct template path.
	 * @return string
	 */
	function getTemplatePath() {
		$plugin =& $this->getMysqlIndexPlugin();
		return $plugin->getTemplatePath();
	}
}

?>
