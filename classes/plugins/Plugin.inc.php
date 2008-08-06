<?php

/**
 * @file classes/plugins/Plugin.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Plugin
 * @ingroup plugins
 *
 * @brief Abstract class for plugins
 */

// $Id$


import('plugins.PKPPlugin');

class Plugin extends PKPPlugin {
	/**
	 * Constructor
	 */
	function Plugin() {
		parent::PKPPlugin();
	}

	function getTemplatePath() {
		$basePath = dirname(dirname(dirname(__FILE__)));
		return "file:$basePath/" . $this->getPluginPath() . '/';
	}

	function getSetting($name) {
		if (!Config::getVar('general', 'installed')) return null;
		if (defined('RUNNING_UPGRADE')) {
			// Bug #2504: Make sure plugin_settings table is not
			// used if it's not available.
			$versionDao =& DAORegistry::getDAO('VersionDAO');
			$version =& $versionDao->getCurrentVersion();
			if ($version->compare('2.1.0') < 0) return null;
		}
		$pluginSettingsDao =& DAORegistry::getDAO('PluginSettingsDAO');
		return $pluginSettingsDao->getSetting($this->getName(), $name);
	}

	/**
	 * Update a plugin setting.
	 * @param $name string The name of the setting
	 * @param $value mixed
	 * @param $type string optional
	 */
	function updateSetting($name, $value, $type = null) {
		$pluginSettingsDao =& DAORegistry::getDAO('PluginSettingsDAO');
		$pluginSettingsDao->updateSetting($this->getName(), $name, $value, $type);
	}

	/**
	 * Callback used to install settings on system install.
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 */
	function installSiteSettings($hookName, $args) {
		$installer =& $args[0];
		$result =& $args[1];

		// Settings are only installed during automated installs. FIXME!
		if (!$installer->getParam('manualInstall')) {
			$pluginSettingsDao =& DAORegistry::getDAO('PluginSettingsDAO');
			$pluginSettingsDao->installSettings($this->getName(), $this->getInstallSitePluginSettingsFile());
		}

		return false;
	}
}

?>
