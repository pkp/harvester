<?php

/**
 * @file classes/plugins/Plugin.inc.php
 *
 * Copyright (c) 2005-2010 Alec Smecher and John Willinsky
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

	function getSetting($name) {
		return parent::getSetting(array(), $name);
	}

	/**
	 * Update a plugin setting.
	 * @param $name string The name of the setting
	 * @param $value mixed
	 * @param $type string optional
	 */
	function updateSetting($name, $value, $type = null) {
		parent::updateSetting(array(), $name, $value, $type);
	}
}

?>
