<?php

/**
 * @file plugins/generic/zendSearch/ZendSearchPlugin.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ZendSearchPlugin
 * @ingroup plugins_generic_zendSearch
 *
 * @brief Zend Framework Search (PHP Lucene) implementation for the Harvester
 */

// $Id$


import('lib.pkp.classes.plugins.GenericPlugin');

define('SOLR_DTD_ID', null);
define('SOLR_DTD_URL', null);

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
			HookRegistry::register('Installer::postInstall',array(&$this, 'postInstallCallback'));
			if ($this->getEnabled()) {
				$this->addHelpData();
				// Include Zend Framework in include path
				if (!$this->isUsingSolr() && checkPhpVersion('5.0.0')) {
					ini_set('include_path', BASE_SYS_DIR . '/lib/pkp/lib/ZendFramework/library' . ENV_SEPARATOR . ini_get('include_path'));
					require_once('lib/pkp/lib/ZendFramework/library/Zend/Search/Lucene.php');
				}

				// Add DAOs
				$this->import('SearchFormElementDAO');
				$this->import('SearchFormElement');
				$searchFormElementDao = new SearchFormElementDAO();
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

				// Rebuild index
				HookRegistry::register('rebuildSearchIndex::flush', array(&$this, 'callbackFlush'));
				HookRegistry::register('rebuildSearchIndex::finish', array(&$this, 'callbackFinish'));
			}
			return true;
		}
		return false;
	}

	/**
	 * Return true iff the plugin should use an external Solr installation
	 * (as opposed to Zend Search)
	 */
	function isUsingSolr() {
		return ($this->getSetting('solrUrl') != ''?true:false);
	}

	/**
	 * Get the filename of the ADODB schema for this plugin.
	 */
	function getInstallSchemaFile() {
		return $this->getPluginPath() . '/' . 'schema.xml';
	}

	/**
	 * Get the display name of this plugin
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.generic.zendSearch.name');
	}

	/**
	 * Get the description of this plugin
	 * @return string
	 */
	function getDescription() {
		return __('plugins.generic.zendSearch.description');
	}

	/**
	 * Get a list of available management verbs for this plugin
	 * @return array
	 */
	function getManagementVerbs() {
		$verbs = array();
		if ($this->getEnabled()) {
			$verbs[] = array('adminSearchForm', __('plugins.generic.zendSearch.searchForm'));
			$verbs[] = array('adminSettings', __('plugins.generic.zendSearch.settings'));
		}
		return parent::getManagementVerbs($verbs);
	}

 	/*
 	 * Execute a management verb on this plugin
 	 * @param $verb string
 	 * @param $args array
	 * @param $message string Location for the plugin to put a result msg
 	 * @return boolean
 	 */
	function manage($verb, $args, &$message) {
		if (!parent::manage($verb, $args, $message) && $verb != 'enable') return false;
		switch ($verb) {
			case 'enable':
				if (!checkPhpVersion('5.0.0')) {
					if (!$this->isUsingSolr()) {
						Request::redirect('zendSearchAdmin', 'settings');
					}
				}
				return false;
			case 'adminSearchForm':
				Request::redirect('zendSearchAdmin', 'index');
				return false;
			case 'adminSettings':
				Request::redirect('zendSearchAdmin', 'settings');
				return false;
			default:
				// Unknown management verb
				assert(false);
				return false;
		}
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
		$output .= '<li><a href="' . Request::url('search', 'index') . '">' . __('navigation.search') . '</a></li>';
		return false;
	}

	/**
	 * Add the site management links
	 */
	function siteManagementCallback($hookName, $args) {
		$output =& $args[2];
		$output .= '<li>&#187;&nbsp;<a href="' . Request::url('admin', 'plugin', array('generic', $this->getName(), 'adminSearchForm')) . '">' . __('plugins.generic.zendSearch.searchForm') . '</a></li>';
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
				$blockPlugin = new ZendSearchBlockPlugin($this->getName());
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
				if (is_callable(array('ZendSearchHandler', $op))) {
					define('HANDLER_CLASS', 'ZendSearchHandler');
					define('ZEND_SEARCH_PLUGIN_NAME', $this->getName());
					return true;
				}
				break;
			case 'zendSearchAdmin':
				$this->import('ZendSearchAdminHandler');
				if (is_callable(array('ZendSearchAdminHandler', $op))) {
					define('HANDLER_CLASS', 'ZendSearchAdminHandler');
					define('ZEND_SEARCH_PLUGIN_NAME', $this->getName());
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

		$isUsingSolr = $this->isUsingSolr();

		if (!$isUsingSolr) {
			$doc = new Zend_Search_Lucene_Document();
		} else {
			import('lib.pkp.classes.xml.XMLCustomWriter');
			$doc =& XMLCustomWriter::createDocument('add', SOLR_DTD_ID, SOLR_DTD_URL);
			$addNode =& XMLCustomWriter::createElement($doc, 'add');
			XMLCustomWriter::appendChild($doc, $addNode);
			$docNode =& XMLCustomWriter::createElement($doc, 'doc');
			XMLCustomWriter::appendChild($addNode, $docNode);
		}

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
							if ($isUsingSolr) {
								$fieldNode =& XMLCustomWriter::createChildWithText($doc, $docNode, 'field', $fieldValue, false);
								if ($fieldNode) $fieldNode->setAttribute('name', $searchFormElement->getSymbolic());
								unset($fieldNode);
							} else {
								$doc->addField(Zend_Search_Lucene_Field::UnStored($searchFormElement->getSymbolic(), $fieldValue));
							}
							break;
						case SEARCH_FORM_ELEMENT_TYPE_SELECT:
							$fieldValue = $schemaPlugin->getFieldValue($record, $fieldName, SORT_ORDER_TYPE_STRING);
							if ($isUsingSolr) {
								$fieldNode =& XMLCustomWriter::createChildWithText($doc, $docNode, 'field', $fieldValue, false);
								if ($fieldNode) $fieldNode->setAttribute('name', $searchFormElement->getSymbolic());
								unset($fieldNode);
							} else {
								$doc->addField(Zend_Search_Lucene_Field::UnStored($searchFormElement->getSymbolic(), $fieldValue));
							}
							if (!$searchFormElementDao->searchFormElementOptionExists($searchFormElementId, $fieldValue)) {
								$searchFormElementDao->insertSearchFormElementOption($searchFormElementId, $fieldValue);
							}
							break;
						case SEARCH_FORM_ELEMENT_TYPE_DATE:
							$fieldValue = $schemaPlugin->getFieldValue($record, $fieldName, SORT_ORDER_TYPE_DATE);
							$fieldValue = strftime
('%Y-%m-%dT%H:%M:%SZ', $fieldValue);
							if ($fieldValue !== null) {
								// Don't index values that could not be parsed
								if ($isUsingSolr) {
									$fieldNode =& XMLCustomWriter::createChildWithText($doc, $docNode, 'field', $fieldValue, false);
									if ($fieldNode) $fieldNode->setAttribute('name', $searchFormElement->getSymbolic());
									unset($fieldNode);
								} else {
									$doc->addField(Zend_Search_Lucene_Field::Keyword($searchFormElement->getSymbolic(), $fieldValue));
								}
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
				if ($isUsingSolr) {
					$fieldNode =& XMLCustomWriter::createChildWithText($doc, $docNode, 'field', $fieldValue, false);
					if ($fieldNode) $fieldNode->setAttribute('name', 'text');
					unset($fieldNode);
				} else {
					$doc->addField(Zend_Search_Lucene_Field::UnStored('harvesterOther', $fieldValue));
				}
			}
		}
		if ($isUsingSolr) {
			foreach (array(
				'id' => $record->getRecordId(),
				'harvesterArchiveId' => $record->getArchiveId(),
				'harvesterIdentifier' => $record->getIdentifier()
			) as $name => $value) {
				$fieldNode =& XMLCustomWriter::createChildWithText($doc, $docNode, 'field', $value);
				if ($fieldNode) $fieldNode->setAttribute('name', $name);
				unset($fieldNode);
			}

			$this->solrQuery($doc);
		} else {
			$doc->addField(Zend_Search_Lucene_Field::Keyword('harvesterRecordId', $record->getRecordId()));
			$doc->addField(Zend_Search_Lucene_Field::Keyword('harvesterArchiveId', $record->getArchiveId()));
			$doc->addField(Zend_Search_Lucene_Field::Keyword('harvesterIdentifier', $record->getIdentifier()));

			$index =& $this->getIndex();
			$index->addDocument($doc);
		}

		return false;
	}

	function solrQuery(&$xmlDoc) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->getSetting('solrUrl') . '/update');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type:text/xml; charset=utf-8"));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, XMLCustomWriter::getXml($xmlDoc));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		if (curl_errno($ch)) {
			curl_close($ch);
			fatalError('CURL Error: ' . curl_error($ch));
		} else {
			curl_close($ch);
			$xmlParser = new XMLParser();
			$result = null;
			@$result =& $xmlParser->parseTextStruct($data, array("int"));
			if ($result) foreach ($result as $nodeSet) foreach ($nodeSet as $node) {
				if (isset($node['attributes']['name']) && $node['attributes']['name'] == 'status' && $node['value'] == 0) {
					return true;
				}
			}
			return false;
		}
	}

	function updateRecordCallback($hookName, $args) {
		if (!$this->isUsingSolr()) {
			// Zend Search: First delete the old indexing.
			// Not necessary with Solr.
			$this->deleteRecordCallback($hookName, $args);
		}

		// Then recreate it with the new record
		return $this->insertRecordCallback($hookName, $args);
	}

	function deleteRecordCallback($hookName, $args) {
		$record =& $args[0];

		if ($this->isUsingSolr()) fatalError('deleteRecordCallback unimplemented for Solr.');

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
		// Include Zend Framework in include path
		ini_set('include_path', BASE_SYS_DIR . '/lib/pkp/lib/ZendFramework/library' . ENV_SEPARATOR . ini_get('include_path'));
		if(checkPhpVersion('5.0.0')) {
			require_once('lib/pkp/lib/ZendFramework/library/Zend/Search/Lucene.php');
			// If the indexes do not exist, create them.
			$indexPath = $this->getIndexPath();
			if (!file_exists($indexPath)) {
				$index = Zend_Search_Lucene::create($indexPath);
			}
		} else {
			// Disable plugin until user sets a SOLR URL
			$this->setEnabled(false);
		}


		return false;
	}

	/**
	 * Flush the entire index prior to rebuilding it.
	 */
	function callbackFlush($hookName, $args) {
		if ($this->isUsingSolr()) {
			// FIXME: Implement flushing
		} else {
			// Flush the Lucene index
			$indexPath = $this->getIndexPath();
			$index = Zend_Search_Lucene::create($indexPath);
		}

		// Delete the field options
		$searchFormElementDao =& DAORegistry::getDAO('SearchFormElementDAO');
		$searchFormElements =& $searchFormElementDao->getSearchFormElements();
		while ($searchFormElement =& $searchFormElements->next()) {
			$searchFormElementDao->deleteSearchFormElementOptions(
				$searchFormElement->getSearchFormElementId()
			);
			unset($searchFormElement);
		}
	}

	/**
	 * Index rebuild cleanup: mark select options as clean.
	 */
	function callbackFinish($hookName, $args) {
		$searchFormElementDao =& DAORegistry::getDAO('SearchFormElementDAO');
		$searchFormElements =& $searchFormElementDao->getSearchFormElements();
		while ($searchFormElement =& $searchFormElements->next()) {
			$searchFormElement->setIsClean(true);
			$searchFormElementDao->updateSearchFormElement($searchFormElement);
			unset($searchFormElement);
		}

		if ($this->isUsingSolr()) {
			import('lib.pkp.classes.xml.XMLCustomWriter');
			$doc =& XMLCustomWriter::createDocument('commit', SOLR_DTD_ID, SOLR_DTD_URL);
			$docNode =& XMLCustomWriter::createElement($doc, 'commit');
			XMLCustomWriter::appendChild($doc, $docNode);
			$this->solrQuery($doc);
			unset($doc);

			$doc =& XMLCustomWriter::createDocument('optimize', SOLR_DTD_ID, SOLR_DTD_URL);
			$docNode =& XMLCustomWriter::createElement($doc, 'optimize');
			XMLCustomWriter::appendChild($doc, $docNode);
			$this->solrQuery($doc);
			unset($doc);
		} else {
			$index =& $this->getIndex();
			$index->optimize();
		}
	}
}

?>
