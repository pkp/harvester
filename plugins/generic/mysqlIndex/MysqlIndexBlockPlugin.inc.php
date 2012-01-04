<?php

/**
 * @file MysqlIndexBlockPlugin.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MysqlIndexBlockPlugin
 * @ingroup plugins_blocks_mysqlIndex
 *
 * @brief Class for search block plugin
 */

// $Id$


import('lib.pkp.classes.plugins.BlockPlugin');

class MysqlIndexBlockPlugin extends BlockPlugin {
	/** @var $parentPluginName string Name of MysqlIndexPlugin */
	var $parentPluginName;

	/**
	 * Constructor.
	 */
	function MysqlIndexBlockPlugin($parentPluginName) {
		parent::BlockPlugin();
		$this->parentPluginName = $parentPluginName;
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.generic.mysqlIndex.blockDisplayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.generic.mysqlIndex.blockDescription');
	}

	/**
	 * Get the Zend Search plugin
	 * @return object
	 */
	function &getMysqlIndexPlugin() {
		$plugin =& PluginRegistry::getPlugin('generic', $this->parentPluginName);
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
