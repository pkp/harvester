<?php

/**
 * @file plugins/generic/zendSearch/ZendSearchSettingsForm.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @class ZendSearchSettingsForm
 * @ingroup plugins_generic_zendSearch
 * @see ZendSearchSettingsForm
 *
 * @brief Form for administrator to create/edit search form.
 */



import('lib.pkp.classes.form.Form');

class ZendSearchSettingsForm extends Form {
	/** @var $parentPluginName string Name of parent plugin */
	var $parentPluginName;

	/**
	 * Constructor
	 */
	function ZendSearchSettingsForm($parentPluginName) {
		$this->parentPluginName = $parentPluginName;
		$plugin =& PluginRegistry::getPlugin('generic', $parentPluginName);
		parent::Form($plugin->getTemplatePath() . 'zendSearchSettingsForm.tpl');
		
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorUrl($this, 'solrUrl', 'optional', 'plugins.generic.zendSearch.solrUrl.invalid'));
	}

	/**
	 * Initialize form data from current search form element.
	 */
	function initData() {
		$plugin =& PluginRegistry::getPlugin('generic', $this->parentPluginName);
		$this->_data = array(
			'solrUrl' => $plugin->getSetting('solrUrl')
		);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('solrUrl'));
	}

	/**
	 * Save sort order. 
	 */
	function execute() {
		$plugin =& PluginRegistry::getPlugin('generic', $this->parentPluginName);
		$plugin->updateSetting('solrUrl', $this->getData('solrUrl'));
	}
}

?>
