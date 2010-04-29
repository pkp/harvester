<?php

/**
 * @file ZendSearchBlockPlugin.inc.php
 *
 * Copyright (c) 2005-2010 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ZendSearchBlockPlugin
 * @ingroup plugins_blocks_zendSearch
 *
 * @brief Class for search block plugin
 */

// $Id$


import('plugins.BlockPlugin');

class ZendSearchBlockPlugin extends BlockPlugin {
	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return Locale::translate('plugins.generic.zendSearch.blockDisplayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.generic.zendSearch.blockDescription');
	}

	/**
	 * Get the Zend Search plugin
	 * @return object
	 */
	function &getZendSearchPlugin() {
		$plugin =& PluginRegistry::getPlugin('generic', 'ZendSearchPlugin');
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
