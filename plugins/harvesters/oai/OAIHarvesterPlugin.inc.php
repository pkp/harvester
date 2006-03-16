<?php

/**
 * OAIHarvesterPlugin.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 *
 * OAI Harvester plugin
 *
 * $Id$
 */

import('plugins.HarvesterPlugin');

define('OAI_INDEX_METHOD_LIST_RECORDS', 0x00001);
define('OAI_INDEX_METHOD_LIST_IDENTIFIERS', 0x00002);

class OAIHarvesterPlugin extends HarvesterPlugin {
	/**
	 * Register the plugin.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	function getName() {
		return 'OAIHarvesterPlugin';
	}

	/**
	 * Get the display name of this plugin's protocol.
	 * @return String
	 */
	function getProtocolDisplayName() {
		return Locale::translate('plugins.harvesters.oai.protocolName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.harvesters.oai.description');
	}

	function addArchiveFormChecks(&$form) {
		$form->addCheck(new FormValidator($form, 'harvesterUrl', 'required', 'plugins.harvesters.oai.archive.form.harvesterUrlRequired'));
		$form->addCheck(new FormValidatorInSet($form, 'oaiIndexMethod', 'required', 'plugins.harvesters.oai.archive.form.oaiIndexMethodRequired', array(OAI_INDEX_METHOD_LIST_RECORDS, OAI_INDEX_METHOD_LIST_IDENTIFIERS)));
	}

	function getAdditionalArchiveFormFields() {
		return array('harvesterUrl', 'oaiIndexMethod');
	}

	function displayArchiveForm(&$form, &$templateMgr) {
		$this->import('OAIHarvester');
		$this->import('OAIXMLHandler');

		parent::displayArchiveForm($form, $templateMgr);
		$templateMgr->assign('oaiIndexMethods', array(
			OAI_INDEX_METHOD_LIST_RECORDS => Locale::translate('plugins.harvesters.oai.archive.form.oaiIndexMethod.ListRecords'),
			OAI_INDEX_METHOD_LIST_IDENTIFIERS => Locale::translate('plugins.harvesters.oai.archive.form.oaiIndexMethod.ListIdentifiers')
		));

		// Build a list of supported metadata formats.
		import('schema.SchemaMap');
		$plugins =& PluginRegistry::loadCategory('schemas');
		$archive =& $form->_data['archive'];
		$oaiHarvester =& new OAIHarvester($archive);
		$metadataFormats = $oaiHarvester->getMetadataFormats($form->getData('harvesterUrl'));
		$supportedFormats = array();
		foreach ($metadataFormats as $format) {
			if (($pluginName = SchemaMap::getSchemaPluginName($this->getName(), $format)) && isset($plugins[$pluginName])) {
				$plugin =& $plugins[$pluginName];
				$supportedFormats[$pluginName] = $plugin->getSchemaDisplayName();
				unset($plugin);
			}
		}
		if ($archive) $templateMgr->assign('metadataFormat', $archive->getSchemaPluginName());
		$templateMgr->assign('metadataFormats', $supportedFormats);
	}

	function executeArchiveForm(&$form, &$archive) {
		$archive->setSchemaPluginName(Request::getUserVar('metadataFormat'));
	}

	/**
	 * Update the metadata index.
	 * @param $archive object
	 * @param $params array
	 */
	function updateIndex(&$archive, $params = array()) {
		$this->import('OAIHarvester');
		$this->import('OAIXMLHandler');

		PluginRegistry::loadCategory('schemas');

		$oaiHarvester =& new OAIHarvester($archive);
		$templateMgr =& TemplateManager::getManager();

		if (!$oaiHarvester->updateRecords($params)) {
			foreach ($oaiHarvester->getErrors() as $error) {
				$this->addError($error);
			}
			$result = false;
		} else {
			$archive->setLastIndexedDate(Core::getCurrentDate());
			$result = true;
		}
		$archive->updateRecordCount();
		return $result;
	}

	/**
	 * Describe the options for the command-line harvesting tool.
	 * @return String
	 */
	function describeOptions() {
		echo Locale::translate('plugins.harvesters.oai.toolUsage') . "\n";
	}

}

?>
