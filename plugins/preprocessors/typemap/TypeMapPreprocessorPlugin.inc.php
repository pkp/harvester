<?php

/**
 * @file TypeMapPreprocessorPlugin.inc.php
 *
 * @package plugins.preprocessors.typemap
 * @class TypeMapPreprocessorPlugin
 *
 */


import('classes.plugins.PreprocessorPlugin');

class TypeMapPreprocessorPlugin extends PreprocessorPlugin {
	/** @var $typeCrosswalkFieldNames object */
	var $typeCrosswalkFieldNames;

	/** @var $mappingCache */
	var $mappingCache;

	/**
	 * Constructor
	 */
	function TypeMapPreprocessorPlugin() {
		parent::PreprocessorPlugin();
	}

	/**
	 * Register the plugin.
	 * @param $category string
	 * @param $path string
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->typeCrosswalkFieldNames = array();
		if ($success && $this->isEnabled()) {
			// Fetch the list of field IDs that the type
			// crosswalk uses; we will map all languages mentioned
			// in these fields.
			$crosswalkDao =& DAORegistry::getDAO('CrosswalkDAO');
			$typeCrosswalk =& $crosswalkDao->getCrosswalkByPublicCrosswalkId('type');
			if ($typeCrosswalk) {
				$fields =& $typeCrosswalk->getFields();
				while ($field =& $fields->next()) {
					$this->typeCrosswalkFieldNames[$field->getSchemaId()][] = $field->getName();
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
			$typemapFile = $this->getPluginPath() . '/typemap-' . ((int) $archive->getArchiveId()) . '.xml';
			if (file_exists($typemapFile) && $cacheTime !== null && $cacheTime < filemtime($typemapFile)) {
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
			$typemapFile = $this->getPluginPath() . '/typemap-' . ((int) $cache->cacheId) . '.xml';
			if (file_exists($typemapFile)) {
				$data = $xmlDao->parseStruct($typemapFile, array('mapping'));
			} else {
				$data = array();
			}

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
	 * Preprocess a record.
	 * @param $record Record Record object ready for insertion
	 * @param $archive Archive
 	 * @param $schema Schema
	 * @return boolean Hook callback status
	 */
	function preprocessRecord(&$record, &$archive, &$schema) {
		if (isset($this->typeCrosswalkFieldNames[$schema->getSchemaId()])) {
			$doc = new DOMDocument();
			$doc->loadXML($record->getContents());
			foreach($this->typeCrosswalkFieldNames[$schema->getSchemaId()] as $fieldName) {
				foreach ($doc->getElementsByTagName($fieldName) as $element) {
					$element->nodeValue = $this->mapType($archive, $element->nodeValue);
				}
			}
			$record->setContents($doc->saveXML());
		}
		return false;
	}

}

?>
