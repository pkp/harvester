<?php

/**
 * @file OAIHarvesterPlugin.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.harvesters.oai
 * @class OAIHarvesterPlugin
 *
 * OAI Harvester plugin
 *
 * $Id$
 */

import('classes.plugins.HarvesterPlugin');

define('OAI_INDEX_METHOD_LIST_RECORDS', 0x00001);
define('OAI_INDEX_METHOD_LIST_IDENTIFIERS', 0x00002);

class OAIHarvesterPlugin extends HarvesterPlugin {
	/**
	 * Register the plugin.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		if ($success) {
			HookRegistry::register('TinyMCEPlugin::getEnableFields', array(&$this, 'addDescriptionField'));
		}
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
		return __('plugins.harvesters.oai.protocolName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.harvesters.oai.description');
	}

	function addArchiveFormChecks(&$form) {
		$this->import('OAIHarvester');
		$oaiHarvester = new OAIHarvester($this->archive);

		$form->addCheck(new FormValidator($form, 'harvesterUrl', 'required', 'plugins.harvesters.oai.archive.form.harvesterUrlRequired'));
		$form->addCheck(new FormValidatorInSet($form, 'oaiIndexMethod', 'optional', 'plugins.harvesters.oai.archive.form.oaiIndexMethodRequired', array(OAI_INDEX_METHOD_LIST_RECORDS, OAI_INDEX_METHOD_LIST_IDENTIFIERS)));
		$form->addCheck(new FormValidatorCustom($form, 'harvesterUrl', 'required', 'plugins.harvester.oai.archive.form.harvesterUrlInvalid', array(&$oaiHarvester, 'validateHarvesterURL'), array(Request::getUserVar('isStatic'))));
		$form->addCheck(new FormValidatorEmail($form, 'adminEmail', Validation::isSiteAdmin()?'optional':'required', 'plugins.harvesters.oai.archive.form.adminEmailInvalid'));
		$form->addCheck(new FormValidatorCustom($form, 'harvesterUrl', 'required', 'plugins.harvester.oai.archive.form.harvesterUrlDuplicate', array(&$this, 'duplicateHarvesterUrlDoesNotExist'), array(Request::getUserVar('archiveId'))));
	}

	/**
	 * Add the description field to the TinyMCE list. Used with the
	 * "fetch archive metadata" button on the archive form.
	 */
	function addDescriptionField($hookName, $args) {
		$fields =& $args[1];
		if (!in_array('description', $fields)) $fields[] = 'description';
		return false;
	}

	function duplicateHarvesterUrlDoesNotExist($harvesterUrl, $archiveId) {
		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$result =& $archiveDao->retrieve(
			'SELECT a.archive_id FROM archives a, archive_settings s WHERE s.setting_name = ? AND s.archive_id = a.archive_id AND s.setting_value = ? AND a.archive_id <> ?',
			array('harvesterUrl', trim($harvesterUrl), (int) $archiveId)
		);
		$returner = ($result->RecordCount() == 0);
		unset($result);
		return $returner;
	}

	function getAdditionalArchiveFormFields() {
		return array('harvesterUrl', 'oaiIndexMethod', 'adminEmail', 'isStatic');
	}

	function displayArchiveForm(&$form, &$templateMgr) {
		$this->import('OAIHarvester');

		parent::displayArchiveForm($form, $templateMgr);
		$templateMgr->assign('oaiIndexMethods', array(
			OAI_INDEX_METHOD_LIST_RECORDS => __('plugins.harvesters.oai.archive.form.oaiIndexMethod.ListRecords'),
			OAI_INDEX_METHOD_LIST_IDENTIFIERS => __('plugins.harvesters.oai.archive.form.oaiIndexMethod.ListIdentifiers')
		));

		// Build a list of supported metadata formats.
		$schemaDao =& DAORegistry::getDAO('SchemaDAO');
		$aliases =& $schemaDao->getSchemaAliases();
		
		$plugins =& PluginRegistry::loadCategory('schemas');
		$archive =& $form->_data['archive'];
		$oaiHarvester = new OAIHarvester($archive);
		$metadataFormats = $oaiHarvester->getMetadataFormats($form->getData('harvesterUrl'), $form->getData('isStatic'));
		$supportedFormats = array();
		if (is_array($metadataFormats)) foreach ($metadataFormats as $format) {
			if (isset($aliases[$format]) && isset($plugins[$aliases[$format]])) {
				$pluginName = $aliases[$format];
				$plugin =& $plugins[$pluginName];
				$supportedFormats[$pluginName] = $plugin->getSchemaDisplayName();
				unset($plugin);
			}
		}
		if ($archive) $templateMgr->assign('metadataFormat', $archive->getSchemaPluginName());

		$templateMgr->assign('metadataFormats', $supportedFormats);
	}

	function executeArchiveForm(&$form, &$archive) {
		// Save the schema plugin info
		$archive->setSchemaPluginName(Request::getUserVar('metadataFormat'));
		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$archiveDao->updateArchive($archive);

		// Save the OAI indexing method
		if ($form->getData('oaiIndexMethod') == '') $archive->updateSetting('oaiIndexMethod', OAI_INDEX_METHOD_LIST_RECORDS);
	}

	/**
	 * Update the metadata index.
	 * @param $archive object
	 * @param $params array
	 */
	function updateIndex(&$archive, $params = array()) {
		$this->import('OAIHarvester');

		PluginRegistry::loadCategory('schemas');

		$oaiHarvester = new OAIHarvester($archive);

		// If necessary, save the set list.
		if (isset($params['set'])) {
			if (!is_array($params['set'])) $params['set'] = array($params['set']);
			$archive->updateSetting('defaultSets', $params['set']);
		}

		// If the useLastSets param was specified, fetch the last set
		// list from archive settings.
		if (isset($params['useLastSets']) && $params['useLastSets']) {
			$defaultSets = $archive->getSetting('defaultSets');
			if (isset($defaultSets)) $params['set'] = $defaultSets;
		}

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
		echo __('plugins.harvesters.oai.toolUsage') . "\n";
	}

	function displayManagementPage(&$archive) {
		$templateMgr =& TemplateManager::getManager();

		$this->import('OAIHarvester');

		$oaiHarvester = new OAIHarvester($archive);
		$availableSets = $oaiHarvester->getSets($archive->getSetting('harvesterUrl'));
		$defaultSets = $archive->getSetting('defaultSets');
		if (isset($defaultSets) && is_array($defaultSets)) $templateMgr->assign('defaultSets', $defaultSets);

		$templateMgr->assign('availableSets', $availableSets);

		$templateMgr->assign('numRecords', $archive->updateRecordCount());
		$templateMgr->assign('lastIndexed', $archive->getLastIndexedDate());
		$templateMgr->assign('title', $archive->getTitle());
		$templateMgr->assign('archiveId', $archive->getArchiveId());
		$templateMgr->assign_by_ref('archive', $archive);
		$templateMgr->display($this->getTemplatePath() . 'management.tpl');
	}

	function allowSubmitterManagement($verb, $args) {
		switch ($verb) {
			case 'fetchArchiveInfo': return true;
		}
		return false;
	}

	function manage($verb, $args) {
		switch ($verb) {
			case 'fetchArchiveInfo':
				// The user has requested that the archive form be filled out given
				// the OAI URL.
				$harvesterUrl = Request::getUserVar('harvesterUrl');
				$archiveId = (int) array_shift($args);

				$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
				$archive =& $archiveDao->getArchive($archiveId);

				$this->import('OAIHarvester');

				$oaiHarvester = new OAIHarvester($archive);
				$metadata = $oaiHarvester->getMetadata($harvesterUrl, Request::getUserVar('isStatic'));

				import('classes.admin.form.ArchiveForm');
				$archiveForm = new ArchiveForm($archiveId);
				$archiveForm->initData();
				$archiveForm->readInputData();

				$metadataMap = array(
					'repositoryName' => 'title',
					'adminEmail' => 'adminEmail',
					'description' => 'description'
				);

				if ($metadata === false) {
					foreach ($oaiHarvester->getErrors() as $error) {
						$archiveForm->addError('harvesterUrl', $error);
					}
				} else foreach ($metadata as $name => $value) {
					$archiveForm->setData($name, $value);
				}

				$archiveForm->display();

				return true;
		}
		return HarvesterPlugin::manage($verb, $args);
	}

	/**
	 * Get the harvest update parameters from the Request object.
	 * @param $archive object
	 * @return array
	 */
	function readUpdateParams(&$archive) {
		$this->import('OAIHarvester');

		$returner = array();
		$set = Request::getUserVar('set');
		if (count($set) == 1 && $set[0] == '') $set = null;

		$returner['set'] = $set;

		$dateFrom = Request::getUserDateVar('from', 1, 1);
		$dateTo = Request::getUserDateVar('until', 32, 12, null, 23, 59, 59);
		if (!empty($dateFrom)) $returner['from'] = OAIHarvester::UTCDate($dateFrom);
		if (!empty($dateTo)) $returner['until'] = OAIHarvester::UTCDate($dateTo);

		return $returner;
	}

	/**
	 * Get the harvester object for this plugin
	 */
	function getHarvester(&$archive) {
		$this->import('OAIHarvester');
		return new OAIHarvester($archive);
	}
}

?>
