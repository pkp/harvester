<?php
/**
 * @file plugins/generic/customBlockManager/SettingsForm.inc.php
 *
 * Copyright (c) 2003-2015 John Willinsky. For full terms see the file docs/COPYING.
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsForm
 *
 * @brief Form for site admins to add or delete sidebar blocks
 *
 */

import('lib.pkp.classes.form.Form');

class SettingsForm extends Form {
	/** @var $plugin object */
	var $plugin;

	/** $var $errors string */
	var $errors;

	/**
	 * Constructor
	 */
	function SettingsForm(&$plugin) {

		parent::Form($plugin->getTemplatePath() . 'settingsForm.tpl');
		$this->plugin =& $plugin;

	}

	/**
	 * Initialize form data from the plugin settings to the form
	 */
	function initData() {
		$plugin =& $this->plugin;

		$templateMgr =& TemplateManager::getManager();

		$blocks = $plugin->getSetting('blocks');

		if ( !is_array($blocks) ) {
			$this->setData('blocks', array());
		} else {
			$this->setData('blocks', $blocks);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(
			array(
				'blocks',
				'deletedBlocks'
			)
		);
	}

	/**
	 * Update the plugin settings
	 */
	function execute() {
		$plugin =& $this->plugin;

		$pluginSettingsDAO =& DAORegistry::getDAO('PluginSettingsDAO');

		$deletedBlocks = explode(':',$this->getData('deletedBlocks'));
		foreach ($deletedBlocks as $deletedBlock) {
			$pluginSettingsDAO->deleteSetting($deletedBlock, 'enabled');
			$pluginSettingsDAO->deleteSetting($deletedBlock, 'seq');
			$pluginSettingsDAO->deleteSetting($deletedBlock, 'context');
			$pluginSettingsDAO->deleteSetting($deletedBlock, 'blockContent');
		}

		// Sort the blocks in alphabetical order
		$blocks = $this->getData('blocks');
		ksort($blocks);

		// Remove any blank entries that made it into the array
		foreach ($blocks as $key => $value) {
			if (is_null($value) || trim($value)=="") {
				unset($blocks[$key]);
			}
		}

		// Update blocks
		$plugin->updateSetting('blocks', $blocks);
		$this->setData('blocks',$blocks);
	}
}

?>
