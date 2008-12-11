<?php

/**
 * @file plugins/generic/zendSearch/ZendSearchSettingsForm.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ZendSearchSettingsForm
 * @ingroup plugins_generic_zendSearch
 * @see ZendSearchSettingsForm
 *
 * @brief Form for administrator to create/edit search form.
 */

// $Id$


import('form.Form');

class ZendSearchSettingsForm extends Form {
	/**
	 * Constructor
	 */
	function ZendSearchSettingsForm() {
		$plugin =& PluginRegistry::getPlugin('generic', 'ZendSearchPlugin');
		parent::Form($plugin->getTemplatePath() . 'zendSearchSettingsForm.tpl');
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorUrl($this, 'solrUrl', 'optional', 'plugins.generic.zendSearch.solrUrl.invalid'));
	}

	/**
	 * Initialize form data from current search form element.
	 */
	function initData() {
		$plugin =& PluginRegistry::getPlugin('generic', 'ZendSearchPlugin');
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
		$plugin =& PluginRegistry::getPlugin('generic', 'ZendSearchPlugin');
		$plugin->updateSetting('solrUrl', $this->getData('solrUrl'));
	}
}

?>
