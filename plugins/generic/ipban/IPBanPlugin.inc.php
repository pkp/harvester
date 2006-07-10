<?php

/**
 * @file IPBanPluginPlugin.inc.php
 *
 * Copyright (c) 2005-2006 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.ipban
 * @class IPBanPlugin
 *
 * IP Banning plugin; bans all access by IP
 *
 * $Id$
 */

import('plugins.Plugin');

class IPBanPlugin extends Plugin {
	/**
	 * Register the plugin.
	 */
	function register($category, $path) {
		if (!Config::getVar('general', 'installed')) return false;
		$success = parent::register($category, $path);
		$this->addLocaleData();
		if ($success) {
			if ($this->isEnabled()) {
				HookRegistry::register('LoadHandler', array(&$this, '_loadHandlerCallback'));
			}
		}
		return $success;
	}

	/**
	 * Prevent the Harvester from responding to certain IP addresses.
	 */
	function _loadHandlerCallback($hookName, $args) {
		$ips = array();
		@$ips = array_map('rtrim',file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ips.txt'));
		if (is_array($ips) && in_array(Request::getRemoteAddr(), $ips)) exit();
		return false;
	}

	function getName() {
		return 'IPBanPlugin';
	}

	/**
	 * Get the display name of this plugin's protocol.
	 * @return String
	 */
	function getDisplayName() {
		return Locale::translate('plugins.generic.ipban.name');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.generic.ipban.description');
	}

	function getManagementVerbs() {
		if ($this->isEnabled()) return array(
			array('disable', Locale::translate('common.disable'))
		);
		else return array(
			array('enable', Locale::translate('common.enable'))
		);
	}

	function manage($verb, $params) {
		switch ($verb) {
			case 'enable':
				$this->updateSetting('enabled', true);
				break;
			case 'disable':
				$this->updateSetting('enabled', false);
				break;
		}
		Request::redirect('admin', 'plugins');
	}

	function isEnabled() {
		return $this->getSetting('enabled');
	}
}

?>
