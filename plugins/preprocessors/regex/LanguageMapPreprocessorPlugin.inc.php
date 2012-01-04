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
 * Test preprocessor plugin
 *
 * $Id$
 */

import('classes.plugins.PreprocessorPlugin');

define('LANGUAGE_MAP_FILE', 'mapping.xml');

class LanguageMapPreprocessorPlugin extends PreprocessorPlugin {
	/** @var $languageCrosswalk object */
	var $languageCrosswalkFieldIds;

	/** @var $mappingCache */
	var $mappingCache;

	/**
	 * Register the plugin.
	 * @param $category string
	 * @param $path string
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->languageCrosswalkFieldIds = null;
		if ($success && $this->isEnabled()) {
			// Fetch the list of field IDs that the language
			// crosswalk uses; we will map all languages mentioned
			// in these fields.
			$this->languageCrosswalkFieldIds = array();
			$crosswalkDao =& DAORegistry::getDAO('CrosswalkDAO');
			$languageCrosswalk =& $crosswalkDao->getCrosswalkByPublicCrosswalkId('language');
			if ($languageCrosswalk) {
				$fields =& $languageCrosswalk->getFields();
				while ($field =& $fields->next()) {
					$this->languageCrosswalkFieldIds[] = $field->getFieldId();
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
	 * This callback implements the actual map and is called before an
	 * entry is inserted.
	 * @param $archive object
	 * @param $record object
	 * @param $field object
	 * @param $value string
	 * @param $attributes array
	 * @return boolean
	 */
	function preprocessEntry(&$archive, &$record, &$field, &$value, &$attributes) {
		if (is_array($this->languageCrosswalkFieldIds) && in_array($field->getFieldId(), $this->languageCrosswalkFieldIds)) {
			$value = $this->mapLanguage($value);
		}
		return false;
	}

	/**
	 * Return the set of management verbs supported by this plugin for the
	 * administration interface.
	 * @return array
	 */
	function getManagementVerbs() {
		if ($this->isEnabled()) return array(
			array('disable', __('common.disable'))
		);
		else return array(
			array('enable', __('common.enable'))
		);
	}

	/**
	 * Perform a management function on this plugin.
	 * @param $verb string
	 * @param $params array
	 */
	function manage($verb, $params) {
		switch ($verb) {
			case 'enable':
				$this->updateSetting('enabled', true);
				break;
			case 'disable':
				$this->updateSetting('enabled', false);
				break;
		}
		Request::redirect('admin', 'plugins');
	}

	/**
	 * Determine whether or not this plugin is currently enabled.
	 * @return boolean
	 */
	function isEnabled() {
		return $this->getSetting('enabled');
	}
}

?>
