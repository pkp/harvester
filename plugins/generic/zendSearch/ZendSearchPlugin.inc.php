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

				// Add DAOs
				$this->import('SearchFormElementDAO');
				$this->import('SearchFormElement');
				$searchFormElementDao =& new SearchFormElementDao();
				DAORegistry::registerDAO('SearchFormElementDAO', $searchFormElementDao);

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
		if ($this->getEnabled()) {
			$verbs[] = array('adminSearchForm', Locale::translate('plugins.generic.zendSearch.searchForm'));
			$verbs[] = array('disable', Locale::translate('manager.plugins.disable'));
		} else {
			$verbs[] = array('enable', Locale::translate('manager.plugins.enable'));
		}
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
			case 'adminSearchForm':
				Request::redirect('zendSearchAdmin', 'index');
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
	 * Add the search link to the header.
	 */
	function navBarCallback($hookName, $args) {
		$output =& $args[2];
		$output .= '<li><a href="' . Request::url('search', 'index') . '">' . Locale::translate('navigation.search') . '</a></li>';
		return false;
	}

	/**
	 * Add the site management links
	 */
	function siteManagementCallback($hookName, $args) {
		$output =& $args[2];
		$output .= '<li>&#187;&nbsp;<a href="' . Request::url('admin', 'plugin', array('generic', $this->getName(), 'adminSearchForm')) . '">' . Locale::translate('plugins.generic.zendSearch.searchForm') . '</a></li>';
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

		switch ($page) {
			case 'search':
				$this->import('ZendSearchHandler');
				if (method_exists('ZendSearchHandler', $op)) {
					define('HANDLER_CLASS', 'ZendSearchHandler');
					return true;
				}
				break;
			case 'zendSearchAdmin':
				$this->import('ZendSearchAdminHandler');
				if (method_exists('ZendSearchAdminHandler', $op)) {
					define('HANDLER_CLASS', 'ZendSearchAdminHandler');
					return true;
				}
				break;
		}
		return false;
	}

	function insertRecordCallback($hookName, $args) {
		// Load a cached list of search form elements for which we will index.
		static $searchFormElementCache;
		static $fieldsToSearchFormElements;
		$searchFormElementDao =& DAORegistry::getDAO('SearchFormElementDAO');
		if (!isset($searchFormElementCache)) {
			$searchFormElements =& $searchFormElementDao->getSearchFormElements();
			while ($searchFormElement =& $searchFormElements->next()) {
				$searchFormElementId = $searchFormElement->getSearchFormElementId();
				$searchFormElementCache[$searchFormElementId] =& $searchFormElement;
				$searchFormElementFields =& $searchFormElementDao->getFieldsBySearchFormElement($searchFormElementId);
				while ($field =& $searchFormElementFields->next()) {
					$fieldsToSearchFormElements[$field->getFieldId()][] = $searchFormElementId;
					unset($field);
				}
				unset($searchFormElement, $searchFormElementFields);
			}
			unset($searchFormElements);
		}

		// Now handle the record.
		$record =& $args[0];

		$doc = new Zend_Search_Lucene_Document();

		$schemaPlugin =& $record->getSchemaPlugin();
		$schemaPluginName = $schemaPlugin->getName();
		$fieldDao =& DAORegistry::getDAO('FieldDAO');
		foreach ($schemaPlugin->getFieldList() as $fieldName) {
			$field =& $fieldDao->buildField($fieldName, $schemaPluginName);
			if (isset($fieldsToSearchFormElements[$field->getFieldId()])) {
				// This field belongs to one or more search form elements;
				// make sure it is indexed against that element.
				foreach ($fieldsToSearchFormElements[$field->getFieldId()] as $searchFormElementId) {
					$searchFormElement =& $searchFormElementCache[$searchFormElementId];
					switch ($searchFormElement->getType()) {
						case SEARCH_FORM_ELEMENT_TYPE_STRING:
							$fieldValue = $schemaPlugin->getFieldValue($record, $fieldName, SORT_ORDER_TYPE_STRING);
							$doc->addField(Zend_Search_Lucene_Field::UnStored($searchFormElement->getSymbolic(), $fieldValue));
							break;
						case SEARCH_FORM_ELEMENT_TYPE_SELECT:
							$fieldValue = $schemaPlugin->getFieldValue($record, $fieldName, SORT_ORDER_TYPE_STRING);
							$doc->addField(Zend_Search_Lucene_Field::UnStored($searchFormElement->getSymbolic(), $fieldValue));
							if (!$searchFormElementDao->searchFormElementOptionExists($searchFormElementId, $fieldValue)) {
								$searchFormElementDao->insertSearchFormElementOption($searchFormElementId, $fieldValue);
							}
							break;
						case SEARCH_FORM_ELEMENT_TYPE_DATE:
							$fieldValue = $schemaPlugin->getFieldValue($record, $fieldName, SORT_ORDER_TYPE_DATE);
							if ($fieldValue !== null) {
								// Don't index values that could not be parsed
								$doc->addField(Zend_Search_Lucene_Field::Keyword($searchFormElement->getSymbolic(), $fieldValue));
							}
							break;
						default:
							fatalError('Unknown search form element type!');
					}
					unset($searchFormElement);
				}
			} else {
				// This is not a search form element field; dump it under
				// "other" so that it is still indexed.
				$fieldValue = $schemaPlugin->getFieldValue($record, $fieldName, SORT_ORDER_TYPE_STRING);
				$doc->addField(Zend_Search_Lucene_Field::UnStored('harvesterOther', $fieldValue));
			}
		}
		$doc->addField(Zend_Search_Lucene_Field::Keyword('harvesterRecordId', $record->getRecordId()));
		$doc->addField(Zend_Search_Lucene_Field::Keyword('harvesterArchiveId', $record->getArchiveId()));
		$doc->addField(Zend_Search_Lucene_Field::Keyword('harvesterIdentifier', $record->getIdentifier()));

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
