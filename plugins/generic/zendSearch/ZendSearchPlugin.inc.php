<?php

/**
 * @file plugins/generic/zendSearch/ZendSearchPlugin.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ZendSearchPlugin
 * @ingroup plugins_generic_zendSearch
 *
 * @brief Zend Framework Search (PHP Lucene) implementation for the Harvester
 */

// $Id$


import('classes.plugins.GenericPlugin');

class ZendSearchPlugin extends GenericPlugin {
	/** @var $index object */
	var $index;
	 
	/**
	 * Register the plugin, if enabled
	 * @param $category string
	 * @param $path string
	 * @return boolean
	 */
	function register($category, $path) {
		if (parent::register($category, $path)) {
			$this->addLocaleData();
			HookRegistry::register('Installer::postInstall',array(&$this, 'postInstallCallback'));
			if ($this->getEnabled()) {
				// Include Zend Framework in include path
				ini_set('include_path', BASE_SYS_DIR . '/lib/pkp/lib/ZendFramework/library' . ENV_SEPARATOR . ini_get('include_path'));
				require_once('lib/pkp/lib/ZendFramework/library/Zend/Search/Lucene.php');

				// Set hooks
				HookRegistry::register('Installer::postInstall', array(&$this, 'postInstallCallback'));
				HookRegistry::register('Harvester::insertRecord', array(&$this, 'insertRecordCallback'));
				HookRegistry::register('Harvester::updateRecord', array(&$this, 'updateRecordCallback'));
				HookRegistry::register('Harvester::deleteRecord', array(&$this, 'deleteRecordCallback'));
				HookRegistry::register('LoadHandler', array(&$this, 'loadHandlerCallback'));
				HookRegistry::register('PluginRegistry::loadCategory', array(&$this, 'callbackLoadCategory'));
			}
			return true;
		}
		return false;
	}

	/**
	 * Get the symbolic name of this plugin
	 * @return string
	 */
	function getName() {
		return 'ZendSearchPlugin';
	}

	/**
	 * Get the display name of this plugin
	 * @return string
	 */
	function getDisplayName() {
		return Locale::translate('plugins.generic.zendSearch.name');
	}

	/**
	 * Get the description of this plugin
	 * @return string
	 */
	function getDescription() {
		return Locale::translate('plugins.generic.zendSearch.description');
	}

	/**
	 * Check whether or not this plugin is enabled
	 * @return boolean
	 */
	function getEnabled() {
		return $this->getSetting('enabled');
	}

	/**
	 * Get a list of available management verbs for this plugin
	 * @return array
	 */
	function getManagementVerbs() {
		$verbs = array();
		$verbs[] = array(
			($this->getEnabled()?'disable':'enable'),
			Locale::translate($this->getEnabled()?'manager.plugins.disable':'manager.plugins.enable')
		);
		return $verbs;
	}

	/**
	 * Execute a management verb on this plugin
	 * @param $verb string
	 * @param $args array
	 * @return boolean
	 */
	function manage($verb, $args) {
		switch ($verb) {
			case 'enable':
				$this->updateSetting('enabled', true);
				break;
			case 'disable':
				$this->updateSetting('enabled', false);
				break;
		}
		return false;
	}

	/**
	 * Get the index object
	 * @return object
	 */
	function &getIndex() {
		if (!isset($this->index)) {
			$indexPath = $this->getIndexPath();
			$this->index = Zend_Search_Lucene::open($indexPath);
		}
		return $this->index;
	}

	/**
	 * Register as a block plugin, even though this is a generic plugin.
	 * This will allow the plugin to behave as a block plugin, i.e. to
	 * have layout tasks performed on it.
	 * @param $hookName string
	 * @param $args array
	 */
	function callbackLoadCategory($hookName, $args) {
		$category =& $args[0];
		$plugins =& $args[1];
		switch ($category) {
			case 'blocks':
				$this->import('ZendSearchBlockPlugin');
				$blockPlugin =& new ZendSearchBlockPlugin();
				$plugins[$blockPlugin->getSeq()][$blockPlugin->getPluginPath()] =& $blockPlugin;
				break;
		}
		return false;
	}

	/**
	 * Get the path to the index storage
	 * @return string
	 */
	function getIndexPath() {
		return CacheManager::getFileCachePath() . DIRECTORY_SEPARATOR . 'recordsIndex';
	}

	function loadHandlerCallback($hookName, $args) {
		$page =& $args[0];
		$op =& $args[1];

		if ($page != 'search') return false;

		$this->import('ZendSearchHandler');
		if (!method_exists('ZendSearchHandler', $op)) return false;

		define('HANDLER_CLASS', 'ZendSearchHandler');
		return true;
	}

	function insertRecordCallback($hookName, $args) {
		$record =& $args[0];

		$doc = new Zend_Search_Lucene_Document();

		$schemaPlugin =& $record->getSchemaPlugin();
		$schemaPluginName = $schemaPlugin->getName();
		foreach ($schemaPlugin->getFieldList() as $fieldName) {
			$doc->addField(Zend_Search_Lucene_Field::UnStored($schemaPluginName . '-' . $fieldName, $schemaPlugin->getFieldValue($record, $fieldName, SORT_ORDER_TYPE_STRING)));
		}
		$doc->addField(Zend_Search_Lucene_Field::Keyword('recordId', $record->getRecordId()));
		$doc->addField(Zend_Search_Lucene_Field::Keyword('archiveId', $record->getArchiveId()));
		$doc->addField(Zend_Search_Lucene_Field::Keyword('identifier', $record->getIdentifier()));

		$index =& $this->getIndex();
		$index->addDocument($doc);

		return false;
	}

	function updateRecordCallback($hookName, $args) {
		// First delete the old indexing
		$this->deleteRecordCallback($hookName, $args);

		// Then recreate it with the new record
		return $this->insertRecordCallback($hookName, $args);
	}

	function deleteRecordCallback($hookName, $args) {
		$record =& $args[0];

		$index =& $this->getIndex();
		$term = new Zend_Search_Lucene_Index_Term($record->getIdentifier(), 'identifier');
		$query = new Zend_Search_Lucene_Search_Query_Term($term);
		$hits = $index->find($query);
		foreach ($hits as $hit) {
			// Should only be 1
			$index->delete($hit->id);
		}

		return false;
	}

	function postInstallCallback($hookName, $args) {
		// If the indexes do not exist, create them.
		$indexPath = $this->getIndexPath();
		if (!file_exists($indexPath)) {
			$index = Zend_Search_Lucene::create($indexPath);
		}
		return false;
	}
}

?>
