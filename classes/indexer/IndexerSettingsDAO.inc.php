<?php

/**
 * IndexerSettingsDAO.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package indexer
 *
 * Class for Indexer Settings DAO.
 * Operations for retrieving and modifying indexer settings.
 *
 * $Id$
 */

class IndexerSettingsDAO extends DAO {

	/**
	 * Constructor.
	 */
	function IndexerSettingsDAO() {
		parent::DAO();
	}

	function &_getCache($indexerId) {
		static $settingCache;
		if (!isset($settingCache)) {
			$settingCache = array();
		}
		if (!isset($settingCache[$indexerId])) {
			import('cache.CacheManager');
			$cacheManager =& CacheManager::getManager();
			$settingCache[$indexerId] =& $cacheManager->getCache(
				'indexerSettings', $indexerId,
				array($this, '_cacheMiss')
			);
		}
		return $settingCache[$indexerId];
	}

	/**
	 * Retrieve an indexer setting value.
	 * @param $indexerId int
	 * @param $name
	 * @return mixed
	 */
	function getSetting($indexerId, $name) {
		$cache =& $this->_getCache($indexerId);
		return $cache->get($name);
	}

	function _cacheMiss(&$cache, $id) {
		$settings =& $this->getIndexerSettings($cache->getCacheId());
		if (!isset($settings[$id])) {
			// Make sure that even null values are cached
			$cache->setCache($id, null);
			return null;
		}
		return $settings[$id];
	}

	/**
	 * Retrieve and cache all settings for an indexer.
	 * @param $indexerId int
	 * @return array
	 */
	function &getIndexerSettings($indexerId) {
		$indexerSettings[$indexerId] = array();
		
		$result = &$this->retrieve(
			'SELECT setting_name, setting_value, setting_type FROM indexer_settings WHERE indexer_id = ?', $indexerId
		);
		
		if ($result->RecordCount() == 0) {
			$returner = null;
			return $returner;
			
		} else {
			while (!$result->EOF) {
				$row = &$result->getRowAssoc(false);
				switch ($row['setting_type']) {
					case 'bool':
						$value = (bool) $row['setting_value'];
						break;
					case 'int':
						$value = (int) $row['setting_value'];
						break;
					case 'float':
						$value = (float) $row['setting_value'];
						break;
					case 'object':
						$value = unserialize($row['setting_value']);
						break;
					case 'string':
					default:
						$value = $row['setting_value'];
						break;
				}
				$indexerSettings[$indexerId][$row['setting_name']] = $value;
				$result->MoveNext();
			}
			$result->close();
			unset($result);

			$cache =& $this->_getCache($indexerId);
			$cache->setEntireCache($indexerSettings[$indexerId]);

			return $indexerSettings[$indexerId];
		}
	}
	
	/**
	 * Add/update an indexer setting.
	 * @param $indexerId int
	 * @param $name string
	 * @param $value mixed
	 * @param $type string data type of the setting. If omitted, type will be guessed
	 */
	function updateSetting($indexerId, $name, $value, $type = null) {
		$cache =& $this->_getCache($indexerId);
		$cache->setCache($name, $value);
		
		if ($type == null) {
			switch (gettype($value)) {
				case 'boolean':
				case 'bool':
					$type = 'bool';
					break;
				case 'integer':
				case 'int':
					$type = 'int';
					break;
				case 'double':
				case 'float':
					$type = 'float';
					break;
				case 'array':
				case 'object':
					$type = 'object';
					break;
				case 'string':
				default:
					$type = 'string';
					break;
			}
		}
		
		if ($type == 'object') {
			$value = serialize($value);
			
		} else if ($type == 'bool') {
			$value = isset($value) && $value ? 1 : 0;
		}
		
		$result = $this->retrieve(
			'SELECT COUNT(*) FROM indexer_settings WHERE indexer_id = ? AND setting_name = ?',
			array($indexerId, $name)
		);
		
		if ($result->fields[0] == 0) {
			$returner = $this->update(
				'INSERT INTO indexer_settings
					(indexer_id, setting_name, setting_value, setting_type)
					VALUES
					(?, ?, ?, ?)',
				array($indexerId, $name, $value, $type)
			);
		} else {
			$returner = $this->update(
				'UPDATE indexer_settings SET
					setting_value = ?,
					setting_type = ?
					WHERE indexer_id = ? AND setting_name = ?',
				array($value, $type, $indexerId, $name)
			);
		}

		$result->Close();
		unset($result);

		return $returner;
	}
	
	/**
	 * Delete an indexer setting.
	 * @param $indexerId int
	 * @param $name string
	 */
	function deleteSetting($indexerId, $name) {
		$cache =& $this->_getCache($indexerId);
		$cache->setCache($name, null);
		
		return $this->update(
			'DELETE FROM indexer_settings WHERE indexer_id = ? AND setting_name = ?',
			array($indexerId, $name)
		);
	}
	
	/**
	 * Delete all settings for an indexer.
	 * @param $indexerId int
	 */
	function deleteSettingsByIndexerId($indexerId) {
		$cache =& $this->_getCache($indexerId);
		$cache->flush();
		
		return $this->update(
				'DELETE FROM indexer_settings WHERE indexer_id = ?', $indexerId
		);
	}

	/**
	 * Used internally by installSettings to perform variable and translation replacements.
	 * @param $rawInput string contains text including variable and/or translate replacements.
	 * @param $paramArray array contains variables for replacement
	 * @returns string
	 */
	function _performReplacement($rawInput, $paramArray = array()) {
		$value = preg_replace_callback('{{translate key="([^"]+)"}}', '_installer_indexer_regexp_callback', $rawInput);
		foreach ($paramArray as $pKey => $pValue) {
			$value = str_replace('{$' . $pKey . '}', $pValue, $value);
		}
		return $value;
	}

	/**
	 * Used internally by installSettings to recursively build nested arrays.
	 * Deals with translation and variable replacement calls.
	 * @param $node object XMLNode <array> tag
	 * @param $paramArray array Parameters to be replaced in key/value contents
	 */
	function &_buildObject (&$node, $paramArray = array()) {
		$value = array();
		foreach ($node->getChildren() as $element) {
			$key = $element->getAttribute('key');
			$childArray = &$element->getChildByName('array');
			if (isset($childArray)) {
				$content = $this->_buildObject($childArray, $paramArray);
			} else {
				$content = $this->_performReplacement($element->getValue(), $paramArray);
			}
			if (!empty($key)) {
				$key = $this->_performReplacement($key, $paramArray);
				$value[$key] = $content;
			} else $value[] = $content;
		}
		return $value;
	}

	/**
	 * Install indexer settings from an XML file.
	 * @param $indexerId id of indexer to apply settings to
	 * @param $filename string Name of XML file to parse and install
	 * @param $paramArray array Optional parameters for variable replacement in settings
	 */
	function installSettings($indexerId, $filename, $paramArray = array()) {
		$xmlParser = &new XMLParser();
		$tree = $xmlParser->parse($filename);

		if (!$tree) {
			$xmlParser->destroy();
			return false;
		}

		foreach ($tree->getChildren() as $setting) {
			$nameNode = &$setting->getChildByName('name');
			$valueNode = &$setting->getChildByName('value');

			if (isset($nameNode) && isset($valueNode)) {
				$type = $setting->getAttribute('type');
				$name = &$nameNode->getValue();

				if ($type == 'object') {
					$arrayNode = &$valueNode->getChildByName('array');
					$value = $this->_buildObject($arrayNode, $paramArray);
				} else {
					$value = $this->_performReplacement($valueNode->getValue(), $paramArray);
				}

				// Replace translate calls with translated content
				$this->updateSetting($indexerId, $name, $value, $type);
			}
		}

		$xmlParser->destroy();

	}
}

/**
 * Used internally by indexer setting installation code to perform translation function.
 */
function _installer_indexer_regexp_callback($matches) {
	return Locale::translate($matches[1]);
}

?>
