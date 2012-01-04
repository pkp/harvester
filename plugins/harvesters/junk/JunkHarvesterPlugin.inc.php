<?php

/**
 * @file JunkHarvesterPlugin.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.harvesters.junk
 * @class JunkHarvesterPlugin
 *
 * Junk Harvester plugin
 *
 * $Id$
 */

import('classes.plugins.HarvesterPlugin');

class JunkHarvesterPlugin extends HarvesterPlugin {
	/**
	 * Register the plugin.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	function getName() {
		return 'JunkHarvesterPlugin';
	}

	/**
	 * Get the display name of this plugin's protocol.
	 * @return String
	 */
	function getProtocolDisplayName() {
		return __('plugins.harvesters.junk.protocolName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.harvesters.junk.description');
	}

	function addArchiveFormChecks(&$form) {
		$form->addCheck(new FormValidator($form, 'harvesterUrl', 'required', 'plugins.harvesters.junk.archive.form.harvesterUrlRequired'));
	}

	function getAdditionalArchiveFormFields() {
		return array('harvesterUrl');
	}
}

?>
