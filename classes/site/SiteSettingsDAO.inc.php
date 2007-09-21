<?php

/**
 * @file SiteSettingsDAO.inc.php
 *
 * Copyright (c) 2005-2007 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package site
 * @class SiteSettingsDAO
 *
 * Class for Site Settings DAO.
 * Operations for retrieving and modifying site settings.
 *
 * $Id$
 */

class SiteSettingsDAO extends DAO {
	/**
	 * Constructor.
	 */
	function SiteSettingsDAO() {
		parent::DAO();
	}

	function &_getCache() {
		static $settingCache;
		if (!isset($settingCache)) {
			import('cache.CacheManager');
			$cacheManager =& CacheManager::getManager();
			$settingCache = $cacheManager->getCache(
				'settings', 'site',
				array($this, '_cacheMiss')
			);
		}
		return $settingCache;
	}

	/**
	 * Retrieve a site setting value.
	 * @param $name
	 * @return mixed
	 */
	function getSetting($name) {
		$cache =& $this->_getCache();
		return $cache->get($name);
	}

	function _cacheMiss(&$cache, $id) {
		$settings =& $this->getSiteSettings();
		if (!isset($settings[$id])) {
			// Make sure that even null values are cached
			$cache->setCache($id, null);
			return null;
		}
		return $settings[$id];
	}

	/**
	 * Retrieve and cache all settings for a site.
	 * @return array
	 */
	function &getSiteSettings() {
		$siteSettings = array();

		$result = &$this->retrieve(
			'SELECT setting_name, setting_value, setting_type FROM site_settings'
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
				$siteSettings[$row['setting_name']] = $value;
				$result->MoveNext();
			}
			$result->close();
			unset($result);

			$cache =& $this->_getCache();
			$cache->setEntireCache($siteSettings);

			return $siteSettings;
		}
	}

	/**
	 * Add/update a site setting.
	 * @param $name string
	 * @param $value mixed
	 * @param $type string data type of the setting. If omitted, type will be guessed
	 */
	function updateSetting($name, $value, $type = null) {
		$cache =& $this->_getCache();
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
			'SELECT COUNT(*) FROM site_settings WHERE setting_name = ?',
			$name
		);

		if ($result->fields[0] == 0) {
			$returner = $this->update(
				'INSERT INTO site_settings
					(setting_name, setting_value, setting_type)
					VALUES
					(?, ?, ?)',
				array($name, $value, $type)
			);
		} else {
			$returner = $this->update(
				'UPDATE site_settings SET
					setting_value = ?,
					setting_type = ?
					WHERE setting_name = ?',
				array($value, $type, $name)
			);
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Delete a site setting.
	 * @param $name string
	 */
	function deleteSetting($name) {
		$cache =& $this->_getCache();
		$cache->setCache($name, null);

		return $this->update(
			'DELETE FROM site_settings WHERE setting_name = ?',
			$name
		);
	}

	/**
	 * Used internally by installSettings to perform variable and translation replacements.
	 * @param $rawInput string contains text including variable and/or translate replacements.
	 * @param $paramArray array contains variables for replacement
	 * @returns string
	 */
	function _performReplacement($rawInput, $paramArray = array()) {
		$value = preg_replace_callback('{{translate key="([^"]+)"}}', '_installer_regexp_callback', $rawInput);
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
	 * Install site settings from an XML file.
	 * @param $filename string Name of XML file to parse and install
	 * @param $paramArray array Optional parameters for variable replacement in settings
	 */
	function installSettings($filename, $paramArray = array()) {
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
				$this->updateSetting($name, $value, $type);
			}
		}

		$xmlParser->destroy();

	}
}

/**
 * Used internally by site setting installation code to perform translation function.
 */
function _installer_regexp_callback($matches) {
	return Locale::translate($matches[1]);
}

?>
