<?php

/**
 * @file classes/archive/ArchiveSettingsDAO.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package archive
 * @class ArchiveSettingsDAO
 *
 * Class for Archive Settings DAO.
 * Operations for retrieving and modifying archive settings.
 *
 */

// $Id$


class ArchiveSettingsDAO extends DAO {
	/**
	 * Constructor.
	 */
	function ArchiveSettingsDAO() {
		parent::DAO();
	}

	function &_getCache($archiveId) {
		static $settingCache;
		if (!isset($settingCache)) {
			$settingCache = array();
		}
		if (!isset($settingCache[$archiveId])) {
			import('lib.pkp.classes.cache.CacheManager');
			$cacheManager =& CacheManager::getManager();
			$settingCache[$archiveId] = $cacheManager->getCache(
				'archiveSettings', $archiveId,
				array($this, '_cacheMiss')
			);
		}
		return $settingCache[$archiveId];
	}

	/**
	 * Retrieve a archive setting value.
	 * @param $archiveId string
	 * @param $name
	 * @return mixed
	 */
	function getSetting($archiveId, $name) {
		$cache =& $this->_getCache($archiveId);
		return $cache->get($name);
	}

	function _cacheMiss(&$cache, $id) {
		$settings =& $this->getArchiveSettings($cache->getCacheId());
		if (!isset($settings[$id])) {
			// Make sure that even null values are cached
			$cache->setCache($id, null);
			return null;
		}
		return $settings[$id];
	}

	/**
	 * Retrieve and cache all settings for an archive.
	 * @param $archiveId int
	 * @return array
	 */
	function &getArchiveSettings($archiveId) {
		$archiveSettings[$archiveId] = array();

		$result =& $this->retrieve(
			'SELECT setting_name, setting_value, setting_type FROM archive_settings WHERE archive_id = ?', $archiveId
		);

		if ($result->RecordCount() == 0) {
			$returner = null;
			return $returner;

		} else {
			while (!$result->EOF) {
				$row =& $result->getRowAssoc(false);
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
				$archiveSettings[$archiveId][$row['setting_name']] = $value;
				$result->MoveNext();
			}
			$result->close();
			unset($result);

			$cache =& $this->_getCache($archiveId);
			$cache->setEntireCache($archiveSettings[$archiveId]);

			return $archiveSettings[$archiveId];
		}
	}

	/**
	 * Add/update a archive setting.
	 * @param $archiveId int
	 * @param $name string
	 * @param $value mixed
	 * @param $type string data type of the setting. If omitted, type will be guessed
	 */
	function updateSetting($archiveId, $name, $value, $type = null) {
		$cache =& $this->_getCache($archiveId);
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
			'SELECT COUNT(*) FROM archive_settings WHERE archive_id = ? AND setting_name = ?',
			array($archiveId, $name)
		);

		if ($result->fields[0] == 0) {
			$returner = $this->update(
				'INSERT INTO archive_settings
					(archive_id, setting_name, setting_value, setting_type)
					VALUES
					(?, ?, ?, ?)',
				array($archiveId, $name, $value, $type)
			);
		} else {
			$returner = $this->update(
				'UPDATE archive_settings SET
					setting_value = ?,
					setting_type = ?
					WHERE archive_id = ? AND setting_name = ?',
				array($value, $type, $archiveId, $name)
			);
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Delete a archive setting.
	 * @param $archiveId int
	 * @param $name string
	 */
	function deleteSetting($archiveId, $name) {
		$cache =& $this->_getCache($archiveId);
		$cache->setCache($name, null);

		return $this->update(
			'DELETE FROM archive_settings WHERE archive_id = ? AND setting_name = ?',
			array($archiveId, $name)
		);
	}

	/**
	 * Delete all settings for a archive.
	 * @param $archiveId string
	 */
	function deleteSettingsByArchiveId($archiveId) {
		$cache =& $this->_getCache($archiveId);
		$cache->flush();

		return $this->update(
				'DELETE FROM archive_settings WHERE archive_id = ?', $archiveId
		);
	}

	/**
	 * Used internally by installSettings to perform variable and translation replacements.
	 * @param $rawInput string contains text including variable and/or translate replacements.
	 * @param $paramArray array contains variables for replacement
	 * @returns string
	 */
	function _performReplacement($rawInput, $paramArray = array()) {
		$value = preg_replace_callback('{{translate key="([^"]+)"}}', '_installer_archive_regexp_callback', $rawInput);
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
			$childArray =& $element->getChildByName('array');
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
	 * Install archive settings from an XML file.
	 * @param $archiveId int id of archive for settings to apply to
	 * @param $filename string Name of XML file to parse and install
	 * @param $paramArray array Optional parameters for variable replacement in settings
	 */
	function installSettings($archiveId, $filename, $paramArray = array()) {
		$xmlParser = new XMLParser();
		$tree = $xmlParser->parse($filename);

		if (!$tree) {
			$xmlParser->destroy();
			return false;
		}

		foreach ($tree->getChildren() as $setting) {
			$nameNode =& $setting->getChildByName('name');
			$valueNode =& $setting->getChildByName('value');

			if (isset($nameNode) && isset($valueNode)) {
				$type = $setting->getAttribute('type');
				$name =& $nameNode->getValue();

				if ($type == 'object') {
					$arrayNode =& $valueNode->getChildByName('array');
					$value = $this->_buildObject($arrayNode, $paramArray);
				} else {
					$value = $this->_performReplacement($valueNode->getValue(), $paramArray);
				}

				// Replace translate calls with translated content
				$this->updateSetting($archiveId, $name, $value, $type);
			}
		}

		$xmlParser->destroy();
		$tree->destroy();
	}
}

/**
 * Used internally by archive setting installation code to perform translation function.
 */
function _installer_archive_regexp_callback($matches) {
	return __($matches[1]);
}

?>
