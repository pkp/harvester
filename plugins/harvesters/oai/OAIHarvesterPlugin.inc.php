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
		parent::displayArchiveForm($form, $templateMgr);
		$templateMgr->assign('oaiIndexMethods', array(
			OAI_INDEX_METHOD_LIST_RECORDS => Locale::translate('plugins.harvesters.oai.archive.form.oaiIndexMethod.ListRecords'),
			OAI_INDEX_METHOD_LIST_IDENTIFIERS => Locale::translate('plugins.harvesters.oai.archive.form.oaiIndexMethod.ListIdentifiers')
		));
	}

	function getInstallSchemaFile() {
		return $this->getPluginPath() . '/schema.xml';
	}

	/**
	 * Update the metadata index.
	 * @param $archive object
	 */
	function updateIndex(&$archive) {
		$this->import('OAIHarvester');
		$this->import('OAIXMLHandler');

		PluginRegistry::loadCategory('schemas');

		// FIXME: Should specify metadata format.
		$oaiHarvester =& new OAIHarvester($archive);
		$oaiHarvester->setMetadataFormat('oai_dc');

		$templateMgr =& TemplateManager::getManager();

		if (!$oaiHarvester->updateRecords()) {
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

}

?>
