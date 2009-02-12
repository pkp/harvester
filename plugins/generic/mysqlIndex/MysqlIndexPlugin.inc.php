<?php

/**
 * @file plugins/generic/mysqlIndex/MysqlIndexPlugin.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MysqlIndexPlugin
 * @ingroup plugins_generic_mysqlIndex
 *
 * @brief MySQL search index implementation for the Harvester
 */

// $Id$


import('classes.plugins.GenericPlugin');

class MysqlIndexPlugin extends GenericPlugin {
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
			// HookRegistry::register('Installer::postInstall',array(&$this, 'postInstallCallback'));
			if ($this->getEnabled()) {
				$this->addHelpData();
				// Add DAOs
				$this->import('CrosswalkDAO');
				$this->import('Crosswalk');
				$this->import('Search');
				$this->import('SearchIndex');
				$this->import('SearchDAO');

				$crosswalkDao = new CrosswalkDAO();
				DAORegistry::registerDAO('CrosswalkDAO', $crosswalkDao);

				$searchDao = new SearchDAO();
				DAORegistry::registerDAO('SearchDAO', $searchDao);

				/**
				 * Set hooks
				 */

				// Record handling & harvesting
				HookRegistry::register('Harvester::insertRecord', array(&$this, 'insertRecordCallback'));
				HookRegistry::register('Harvester::updateRecord', array(&$this, 'updateRecordCallback'));
				HookRegistry::register('Harvester::deleteRecord', array(&$this, 'deleteRecordCallback'));

				// User interface
				HookRegistry::register('Templates::Common::Header::Navbar', array(&$this, 'navBarCallback'));
				HookRegistry::register('Template::Admin::Index::SiteManagement', array(&$this, 'siteManagementCallback'));
				HookRegistry::register('LoadHandler', array(&$this, 'loadHandlerCallback'));
				HookRegistry::register('PluginRegistry::loadCategory', array(&$this, 'callbackLoadCategory'));

				// Rebuild index
				HookRegistry::register('rebuildSearchIndex::flush', array(&$this, 'callbackFlush'));
			}
			return true;
		}
		return false;
	}

	/**
	 * Get the filename of the ADODB schema for this plugin.
	 */
	function getInstallSchemaFile() {
		return $this->getPluginPath() . '/' . 'schema.xml';
	}

	/**
	 * Get the symbolic name of this plugin
	 * @return string
	 */
	function getName() {
		return 'MysqlIndexPlugin';
	}

	/**
	 * Get the display name of this plugin
	 * @return string
	 */
	function getDisplayName() {
		return Locale::translate('plugins.generic.mysqlIndex.name');
	}

	/**
	 * Get the description of this plugin
	 * @return string
	 */
	function getDescription() {
		return Locale::translate('plugins.generic.mysqlIndex.description');
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
		if ($this->getEnabled()) {
			$verbs[] = array('adminCrosswalks', Locale::translate('plugins.generic.mysqlIndex.crosswalks'));
			$verbs[] = array('disable', Locale::translate('manager.plugins.disable'));
		} else {
			$verbs[] = array('enable', Locale::translate('manager.plugins.enable'));
		}
		return $verbs;
	}

 	/*
 	 * Execute a management verb on this plugin
 	 * @param $verb string
 	 * @param $args array
	 * @param $message string Location for the plugin to put a result msg
 	 * @return boolean
 	 */
	function manage($verb, $args, &$message) {
		switch ($verb) {
			case 'enable':
				$this->updateSetting('enabled', true);
				$message = Locale::translate('plugins.generic.mysqlIndex.enabled');
				break;
			case 'disable':
				$this->updateSetting('enabled', false);
				$message = Locale::translate('plugins.generic.mysqlIndex.disabled');
				break;
			case 'adminCrosswalks':
				Request::redirect('mysqlIndexAdmin', 'adminCrosswalks');
				break;
		}
		return false;
	}

	/**
	 * Add the search link to the header.
	 */
	function navBarCallback($hookName, $args) {
		$output =& $args[2];
		$output .= '<li><a href="' . Request::url('misearch', 'index') . '">' . Locale::translate('navigation.search') . '</a></li>';
		return false;
	}

	/**
	 * Add the site management links
	 */
	function siteManagementCallback($hookName, $args) {
		$output =& $args[2];
		$output .= '<li>&#187;&nbsp;<a href="' . Request::url('admin', 'plugin', array('generic', $this->getName(), 'adminCrosswalks')) . '">' . Locale::translate('plugins.generic.mysqlIndex.crosswalks') . '</a></li>';
		return false;
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
				$this->import('MysqlIndexBlockPlugin');
				$blockPlugin = new MysqlIndexBlockPlugin();
				$plugins[$blockPlugin->getSeq()][$blockPlugin->getPluginPath()] =& $blockPlugin;
				break;
		}
		return false;
	}

	function loadHandlerCallback($hookName, $args) {
		$page =& $args[0];
		$op =& $args[1];

		switch ($page) {
			case 'misearch':
				$this->import('MysqlIndexSearchHandler');
				$methods = array_map('strtolower', get_class_methods('MysqlIndexSearchHandler'));
				if (in_array(strtolower($op), $methods)) {
					define('HANDLER_CLASS', 'MysqlIndexSearchHandler');
					return true;
				}
				break;
			case 'mysqlIndexAdmin':
				$this->import('MysqlIndexAdminHandler');
				$methods = array_map('strtolower', get_class_methods('MysqlIndexAdminHandler'));
				if (in_array(strtolower($op), $methods)) {
					define('HANDLER_CLASS', 'MysqlIndexAdminHandler');
					return true;
				}
				break;
		}
		return false;
	}

	function insertRecordCallback($hookName, $args) {
		// Handle the record.
		$record =& $args[0];

		$schemaPlugin =& $record->getSchemaPlugin();
		$schemaPluginName = $schemaPlugin->getName();
		$fieldDao =& DAORegistry::getDAO('FieldDAO');
		foreach ($schemaPlugin->getFieldList() as $fieldName) {
			$field =& $fieldDao->buildField($fieldName, $schemaPluginName);
			$fieldValue = $schemaPlugin->getFieldValue($record, $fieldName, SORT_ORDER_TYPE_STRING);
			SearchIndex::updateTextIndex($record->getRecordId(), $field->getFieldId(), $fieldValue);
		}

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

		$searchDao =& DAORegistry::getDAO('SearchDAO');
		$searchDao->deleteRecordObjects($record->getRecordId());

		return false;
	}

/*	function postInstallCallback($hookName, $args) {
		// Include Zend Framework in include path
		ini_set('include_path', BASE_SYS_DIR . '/lib/pkp/lib/ZendFramework/library' . ENV_SEPARATOR . ini_get('include_path'));
		require_once('lib/pkp/lib/ZendFramework/library/Zend/Search/Lucene.php');

		// If the indexes do not exist, create them.
		$indexPath = $this->getIndexPath();
		if (!file_exists($indexPath)) {
			$index = Zend_Search_Lucene::create($indexPath);
		}
		return false;
	}*/

	/**
	 * Flush the entire index prior to rebuilding it.
	 */
	function callbackFlush($hookName, $args) {
		$searchDao =& DAORegistry::getDAO('SearchDAO');
		$searchDao->flushIndex();
	}

	/**
	 * Get the name of the settings file to be installed site-wide when
	 * Harvester is installed.
	 * @return string
	 */
	function getInstallSitePluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}
}

?>
