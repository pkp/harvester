<?php

/**
 * @file IPBanPluginPlugin.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.ipban
 * @class IPBanPlugin
 *
 * IP Banning plugin; bans all access by IP
 *
 */

// $Id$


import('lib.pkp.classes.plugins.GenericPlugin');

class IPBanPlugin extends GenericPlugin {
	/**
	 * @see PKPPlugin::register()
	 */
	function register($category, $path) {
		if (!Config::getVar('general', 'installed')) return false;
		$success = parent::register($category, $path);
		if ($success) {
			HookRegistry::register('LoadHandler', array(&$this, '_loadHandlerCallback'));
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

	/**
	 * Get the display name of this plugin's protocol.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.generic.ipban.name');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.generic.ipban.description');
	}
}

?>
