<?php

/**
 * @file ZendSearchBlockPlugin.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ZendSearchBlockPlugin
 * @ingroup plugins_blocks_zendSearch
 *
 * @brief Class for search block plugin
 */

// $Id$


import('lib.pkp.classes.plugins.BlockPlugin');

class ZendSearchBlockPlugin extends BlockPlugin {
	/** @var $parentPluginName string Name of parent plugin */
	var $parentPluginName;

	/**
	 * Constructor
	 */
	function ZendSearchBlockPlugin($parentPluginName) {
		$this->parentPluginName = $parentPluginName;
		parent::BlockPlugin();
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.generic.zendSearch.blockDisplayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.generic.zendSearch.blockDescription');
	}

	/**
	 * Get the Zend Search plugin
	 * @return object
	 */
	function &getZendSearchPlugin() {
		$plugin =& PluginRegistry::getPlugin('generic', $this->parentPluginName);
		return $plugin;
	}

	/**
	 * Override the builtin to get the correct plugin path.
	 * @return string
	 */
	function getPluginPath() {
		$plugin =& $this->getZendSearchPlugin();
		return $plugin->getPluginPath();
	}

	/**
	 * Override the builtin to get the correct template path.
	 * @return string
	 */
	function getTemplatePath() {
		$plugin =& $this->getZendSearchPlugin();
		return $plugin->getTemplatePath();
	}
}

?>
