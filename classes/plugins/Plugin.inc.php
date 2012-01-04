<?php

/**
 * @file classes/plugins/Plugin.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Plugin
 * @ingroup plugins
 *
 * @brief Abstract class for plugins
 */

// $Id$


import('lib.pkp.classes.plugins.PKPPlugin');

class Plugin extends PKPPlugin {
	/**
	 * Constructor
	 */
	function Plugin() {
		parent::PKPPlugin();
	}

	/**
	 * Backwards compatible convenience version of
	 * the generic getContextSpecificSetting() method.
	 *
	 * @see PKPPlugin::getContextSpecificSetting()
	 *
	 * @param $name
	 */
	function getSetting($name) {
		return $this->getContextSpecificSetting(array(), $name);
	}

	/**
	 * Backwards compatible convenience version of
	 * the generic updateContextSpecificSetting() method.
	 *
	 * @see PKPPlugin::updateContextSpecificSetting()
	 *
	 * @param $name string The name of the setting
	 * @param $value mixed
	 * @param $type string optional
	 */
	function updateSetting($name, $value, $type = null) {
		$this->updateContextSpecificSetting(array(), $name, $value, $type);
	}
}

?>
