<?php

/**
 * JunkHarvesterPlugin.inc.php
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 *
 * Junk Harvester plugin
 *
 * $Id$
 */

import('plugins.HarvesterPlugin');

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
		return __CLASS__;
	}

	/**
	 * Get the display name of this plugin's protocol.
	 * @return String
	 */
	function getProtocolDisplayName() {
		return Locale::translate('plugins.harvesters.junk.protocolName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.harvesters.junk.description');
	}

	function addArchiveFormChecks(&$form) {
		$form->addCheck(new FormValidator($form, 'junkUrl', 'required', 'plugins.harvesters.junk.archive.form.junkUrlRequired'));
	}

	function getAdditionalArchiveFormFields() {
		return array('junkUrl');
	}
}

?>
