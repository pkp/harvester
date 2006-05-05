<?php

/**
 * LanguageMapPreprocessorPlugin.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
 *
 * Test preprocessor plugin
 *
 * $Id$
 */

import('plugins.PreprocessorPlugin');

define('LANGUAGE_MAP_FILE', 'mapping.xml');

class LanguageMapPreprocessorPlugin extends PreprocessorPlugin {
	/** @var $languageCrosswalk object */
	var $languageCrosswalkFieldIds;

	/** @var $mappingCache */
	var $mappingCache;

	/**
	 * Register the plugin.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->languageCrosswalkFieldIds = null;
		if ($success && $this->isEnabled()) {
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

	function &_getMapCache() {
		static $cache;
		if (!isset($cache)) {
			import('cache.CacheManager');
			$cacheManager =& CacheManager::getManager();
			$cache =& $cacheManager->getFileCache(
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

	function _mapCacheMiss(&$cache, $id) {
		static $mappings;
		if (!isset($mappings)) {
			// Load the mapping list.
			$xmlDao =& new XMLDAO();
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

	function mapLanguage($value) {
		$cache =& $this->_getMapCache();
		if ($newValue = $cache->get(String::strtolower(trim($value)))) {
			return $newValue;
		}
		return $value;
	}

	function getName() {
		return 'LanguageMapPreprocessorPlugin';
	}

	/**
	 * Get a display name for the plugin.
	 */
	function getDisplayName() {
		return Locale::translate('plugins.preprocessors.languagemap.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.preprocessors.languagemap.description');
	}

	function preprocessEntry(&$archive, &$record, &$field, &$value, &$attributes) {
		if (is_array($this->languageCrosswalkFieldIds) && in_array($field->getFieldId(), $this->languageCrosswalkFieldIds)) {
			$value = $this->mapLanguage($value);
		}
		return false;
	}

	function getManagementVerbs() {
		if ($this->isEnabled()) return array(
			array('disable', Locale::translate('common.disable'))
		);
		else return array(
			array('enable', Locale::translate('common.enable'))
		);
	}

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

	function isEnabled() {
		return $this->getSetting('enabled');
	}
}

?>
