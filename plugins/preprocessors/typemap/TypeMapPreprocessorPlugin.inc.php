<?php

/**
 * @file LanguageMapPreprocessorPlugin.inc.php
 *
 * @package plugins.preprocessors.languagemap
 * @class LanguageMapPreprocessorPlugin
 *
 * Based on OJS 2.0's LanguageMapPreprocessorPlugin.inc.php 
 *
 *
**/


import('classes.plugins.PreprocessorPlugin');

class TypeMapPreprocessorPlugin extends PreprocessorPlugin {
	/** @var $languageCrosswalk object */
	var $typeCrosswalkFieldIds;

	/** @var $mappingCache */
	var $mappingCache;

	/**
	 * Register the plugin.
	 * @param $category string
	 * @param $path string
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->typeCrosswalkFieldIds = null;
		if ($success && $this->isEnabled()) {
			// Fetch the list of field IDs that the language
			// crosswalk uses; we will map all languages mentioned
			// in these fields.
			$this->typeCrosswalkFieldIds = array();
			$crosswalkDao =& DAORegistry::getDAO('CrosswalkDAO');
			$typeCrosswalk =& $crosswalkDao->getCrosswalkByPublicCrosswalkId('type');
			if ($typeCrosswalk) {
				$fields =& $typeCrosswalk->getFields();
				while ($field =& $fields->next()) {
					$this->typeCrosswalkFieldIds[] = $field->getFieldId();
					unset($field);
				}
			}
		}
		$this->addLocaleData();
		return $success;
	}

	/**
	 * Type mappings are cached using the file cache system.
	 * _getMapCache returns the cache object responsible for this.
	 * @return object
	 */
	function &_getMapCache(&$archive) {
		static $cache;
		if (!isset($cache)) {
			import('lib.pkp.classes.cache.CacheManager');
			$cacheManager =& CacheManager::getManager();
			$cache = $cacheManager->getFileCache(
				$this->getName(), $archive->getArchiveId(),
				array(&$this, '_mapCacheMiss')
			);

			// Check to see if the cache is outdated.
			$cacheTime = $cache->getCacheTime();
			$typemap_file = 'typemap-' . $archive->getArchiveId() . '.xml';
			if ($cacheTime !== null && $cacheTime < filemtime($this->getPluginPath() . '/' . $typemap_file)) {
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
			// $cache->cacheId has the same value as the archive ID (but ask Alec to verify this is always
			// true in this context)
			$typemap_file = "typemap-" . $cache->cacheId . ".xml";
			$data = $xmlDao->parseStruct($this->getPluginPath() . '/' . $typemap_file, array('mapping'));

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
	 * return $value untouched. The comparison ignores white space.
	 * @param $value string
	 * @return string
	 */
	function mapType(&$archive, $value) {
		$cache =& $this->_getMapCache($archive);
		if ($newValue = $cache->get(trim($value))) {
			return $newValue;
		}
		return $value;
	}

	/**
	 * Return the unique name of this plugin.
	 */
	function getName() {
		return 'TypeMapPreprocessorPlugin';
	}

	/**
	 * Get a display name for the plugin.
	 */
	function getDisplayName() {
		return __('plugins.preprocessors.typemap.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.preprocessors.typemap.description');
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
		if (is_array($this->typeCrosswalkFieldIds) && in_array($field->getFieldId(), $this->typeCrosswalkFieldIds)) {
			$value = $this->mapType($archive, $value);
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
