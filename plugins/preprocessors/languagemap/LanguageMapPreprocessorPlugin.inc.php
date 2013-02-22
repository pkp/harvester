<?php

/**
 * @file LanguageMapPreprocessorPlugin.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.preprocessors.languagemap
 * @class LanguageMapPreprocessorPlugin
 *
 */

import('classes.plugins.PreprocessorPlugin');

define('LANGUAGE_MAP_FILE', 'mapping.xml');

class LanguageMapPreprocessorPlugin extends PreprocessorPlugin {
	/** @var $languageCrosswalkFieldNames object */
	var $languageCrosswalkFieldNames;

	/** @var $mappingCache */
	var $mappingCache;

	/**
	 * Constructor
	 */
	function LanguageMapPreprocessorPlugin() {
		parent::PreprocessorPlugin();
	}

	/**
	 * Register the plugin.
	 * @param $category string
	 * @param $path string
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->languageCrosswalkFieldNames = array();
		if ($success && $this->isEnabled()) {
			// Fetch the list of field IDs that the language
			// crosswalk uses; we will map all languages mentioned
			// in these fields.
			$crosswalkDao =& DAORegistry::getDAO('CrosswalkDAO');
			$languageCrosswalk =& $crosswalkDao->getCrosswalkByPublicCrosswalkId('language');
			if ($languageCrosswalk) {
				$fields =& $languageCrosswalk->getFields();
				while ($field =& $fields->next()) {
					$this->languageCrosswalkFieldNames[$field->getSchemaId()][] = $field->getName();
					unset($field);
				}
			}
		}
		$this->addLocaleData();
		return $success;
	}

	/**
	 * Language mappings are cached using the file cache system.
	 * _getMapCache returns the cache object responsible for this.
	 * @return object
	 */
	function &_getMapCache() {
		static $cache;
		if (!isset($cache)) {
			import('lib.pkp.classes.cache.CacheManager');
			$cacheManager =& CacheManager::getManager();
			$cache = $cacheManager->getFileCache(
				$this->getName(), 'mapping',
				array(&$this, '_mapCacheMiss')
			);

			// Check to see if the cache is outdated.
			$cacheTime = $cache->getCacheTime();
			if ($cacheTime !== null && $cacheTime < filemtime($this->getPluginPath() . '/' . LANGUAGE_MAP_FILE)) {
				$cache->flush();
			}
		}
		return $cache;
	}

	/**
	 * This function is called when the cache cannot be loaded;
	 * in this case, re-generate the cache from XML.
	 * @param $cache object
	 * @param $id string (fixed to "mapping")
	 */
	function _mapCacheMiss(&$cache, $id) {
		static $mappings;
		if (!isset($mappings)) {
			// Load the mapping list.
			$xmlDao = new XMLDAO();
			$data = $xmlDao->parseStruct($this->getPluginPath() . '/' . LANGUAGE_MAP_FILE, array('mapping'));

			if (isset($data['mapping'])) {
				foreach ($data['mapping'] as $mapping) {
					$mappings[$mapping['attributes']['from']] = $mapping['attributes']['to'];
				}
			}
			$cache->setEntireCache($mappings);
		}
		return null;
	}

	/**
	 * If $value is found in the mapping, return the mapped value; otherwise
	 * return $value untouched. The comparison ignores case and white-
	 * space.
	 * @param $value string
	 * @return string
	 */
	function mapLanguage($value) {
		$cache =& $this->_getMapCache();
		if ($newValue = $cache->get(String::strtolower(trim($value)))) {
			return $newValue;
		}
		return $value;
	}

	/**
	 * Return the unique name of this plugin.
	 */
	function getName() {
		return 'LanguageMapPreprocessorPlugin';
	}

	/**
	 * Get a display name for the plugin.
	 */
	function getDisplayName() {
		return __('plugins.preprocessors.languagemap.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.preprocessors.languagemap.description');
	}

	/**
	 * Preprocess a record.
	 * @param $record Record Record object ready for insertion
	 * @param $archive Archive
 	 * @param $schema Schema
	 * @return boolean Hook callback status
	 */
	function preprocessRecord(&$record, &$archive, &$schema) {
		if (isset($this->languageCrosswalkFieldNames[$schema->getSchemaId()])) {
			$doc = new DOMDocument();
			$doc->loadXML($record->getContents());
			foreach($this->languageCrosswalkFieldNames[$schema->getSchemaId()] as $fieldName) {
				foreach ($doc->getElementsByTagName($fieldName) as $element) {
					$element->nodeValue = $this->mapLanguage($archive, $element->nodeValue);
				}
			}
			$record->setContents($doc->saveXML());
		}
		return false;
	}

}

?>
