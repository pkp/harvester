<?php

/**
 * @file OAIHarvester.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.harvesters.oai
 * @class OAIHarvester
 *
 * OAI Harvester
 *
 * $Id$
 */

import('harvester.Harvester');

class OAIHarvester extends Harvester {
	/** @var $oaiUrl string */
	var $oaiUrl;

	/** @var $metadataFormat string */
	var $metadataFormat;

	/** @var $responseDate int Timestamp */
	var $responseDate;

	function OAIHarvester(&$archive) {
		parent::Harvester($archive);
		if ($archive) $this->oaiUrl = $archive->getSetting('harvesterUrl');
	}

	/**
	 * If necessary, wait until a minimum amount of time has passed
	 * since the last request.
	 */
	function throttle() {
		$delay = (int) Config::getVar('general', 'throttling_delay');
		$lastRequest =& Registry::get('lastThrottledRequest', true, 0);
		$timeDiff = $delay + $lastRequest - time();
		if ($timeDiff > 0) {
			sleep ($timeDiff);
		}
		$lastRequest = time();
	}

	/**
	 * Set the metadata format.
	 * @param $metadataFormat string
	 */
	function setMetadataFormat($metadataFormat) {
		$archive =& $this->getArchive();
		$schemaDao =& DAORegistry::getDAO('SchemaDAO');
		$aliases = $schemaDao->getSchemaAliases();
		$archive->setSchemaPluginName($aliases[$metadataFormat]);
		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$archiveDao->updateArchive($archive);
	}

	/**
	 * Get the metadata format.
	 */
	function getMetadataFormat($default = DUBLIN_CORE_METADATA_PREFIX) {
		if (isset($this->metadataFormat)) return $this->metadataFormat;

		$archive =& $this->getArchive();
		$schemaPluginName = $archive->getSchemaPluginName();
		if (empty($schemaPluginName)) {
			return ($this->metadataFormat = $default);
		}

		$schemaDao =& DAORegistry::getDAO('SchemaDAO');
		$aliases = array_keys($schemaDao->getSchemaAliases($schemaPluginName));
		$supportedFormats = OAIHarvester::getMetadataFormats($archive->getSetting('harvesterUrl'), $archive->getSetting('isStatic'));
		// Return the first common format between the aliases for this
		// plugin and the archive's supported formats.
		$this->metadataFormat = array_shift(array_intersect($aliases, $supportedFormats));
		if (empty($this->metadataFormat)) $this->metadataFormat = $default;
		return $this->metadataFormat;
	}

	/**
	 * Get a list of supported metadata formats for this archive.
	 * This is a static method.
	 * @param $harvesterUrl string
	 * @param $static boolean optional
	 * @return array
	 */
	function getMetadataFormats($harvesterUrl, $static = false) {
		$returner = array(DUBLIN_CORE_METADATA_PREFIX); // Assume DC as minimum
		if (!$harvesterUrl) return $returner;


		if (!$static) $harvesterUrl = $this->addParameters($harvesterUrl, array(
			'verb' => 'ListMetadataFormats'
		));

		$this->throttle();
		$parser = new XMLParser();
		$result =& $parser->parse($harvesterUrl);
		if (!$parser->getStatus()) {
			foreach ($parser->getErrors() as $error) {
				$this->addError($error);
			}
			return false;
		}

		if ($errorNode =& $result->getChildByName('error')) {
			$this->addError($errorNode->getValue());
			return false;
		}

		$listMetadataFormatsNode =& $result->getChildByName('ListMetadataFormats');
		foreach ($listMetadataFormatsNode->getChildren() as $node) {
			$prefixNode =& $node->getChildByName('metadataPrefix');
			if ($prefixNode) $returner[] = $prefixNode->getValue();
			unset($prefixNode);
		}
		return $returner;
	}

	/**
	 * Ensure that the supplied harvester URL is valid.
	 * This is a static method.
	 * @param $harvesterUrl string
	 * @param $static boolean optional
	 * @return boolean
	 */
	function validateHarvesterURL($harvesterUrl, $static = false) {
		$result = $this->getMetadata($harvesterUrl, $static);
		if (is_array($result) && !empty($result['title'])) return true;
		return false;
	}

	/**
	 * Fetch the archive's self-descriptive metadata.
	 * This is a static method.
	 * @param $harvesterUrl string
	 * @param $static boolean optional
	 * @return array
	 */
	function getMetadata($harvesterUrl, $static = false) {
		if (!$harvesterUrl) return false;
		if (!$static) $harvesterUrl = $this->addParameters($harvesterUrl, array(
			'verb' => 'Identify'
		));

		$this->throttle();
		$parser = new XMLParser();
		$result =& $parser->parse($harvesterUrl);
		if (!$parser->getStatus()) {
			foreach ($parser->getErrors() as $error) {
				$this->addError($error);
			}
			return false;
		}

		if ($errorNode =& $result->getChildByName('error')) {
			$this->addError ($errorNode->getValue());
			return false;
		}

		$returner = array();
		$identifyNode =& $result->getChildByName('Identify');

		$repositoryNameNode =& $identifyNode->getChildByName('repositoryName') && $returner['title'] = $repositoryNameNode->getValue();
		$adminEmailNode =& $identifyNode->getChildByName('adminEmail') && $returner['adminEmail'] = $adminEmailNode->getValue();
		$descriptionNode =& $identifyNode->getChildByName('description') && $returner['description'] = $descriptionNode->getValue();

		return $returner;
	}

	/**
	 * Get a list of available sets for this archive.
	 * This is a static method.
	 * @return array
	 */
	function getSets($harvesterUrl) {
		$harvesterUrl = $this->addParameters($harvesterUrl, array(
			'verb' => 'ListSets'
		));

		$this->throttle();
		$parser = new XMLParser();
		$result =& $parser->parse($harvesterUrl);
		if (!$parser->getStatus()) {
			foreach ($parser->getErrors() as $error) {
				$this->addError($error);
			}
			return false;
		}

		if ($errorNode =& $result->getChildByName('error')) {
			$this->addError ($errorNode->getValue());
			return false;
		}

		$returner = array();
		$listSetsNode =& $result->getChildByName('ListSets');
		foreach ($listSetsNode->getChildren() as $node) {
			$setSpecNode =& $node->getChildByName('setSpec');
			$setNameNode =& $node->getChildByName('setName');
			if ($setSpecNode && $setNameNode) 
				$returner[$setSpecNode->getValue()] = $setNameNode->getValue();
			unset($setSpecNode, $setNameNode);
		}
		return $returner;
	}

	function setResponseDate($responseDate) {
		$this->responseDate = $responseDate;
	}

	function getResponseDate() {
		return $this->responseDate;
	}

	/**
	 * Return the current harvesting method for this archive, i.e.
	 * either OAI_INDEX_METHOD_LIST_RECORDS or
	 * OAI_INDEX_METHOD_LIST_IDENTIFIERS.
	 * @return string
	 */
	function getHarvestingMethod() {
		$archive =& $this->getArchive();
		$indexingMethod = $archive->getSetting('oaiIndexMethod');
		switch ($indexingMethod) {
			case OAI_INDEX_METHOD_LIST_RECORDS:
				return 'ListRecords';
			case OAI_INDEX_METHOD_LIST_IDENTIFIERS:
				return 'ListIdentifiers';
			default:
				$this->addError ("Unknown indexing method $indexingMethod!");
				return false;
		}
	}

	/**
	 * Update the records for this archive.
	 * The $params variable is an associative array that can include
	 * "set", "from", "until", "skipIndexing", and "verbose" as keys.
	 * @param $params array
	 * @return int Number or records, or false iff error condition
	 */
	function updateRecords($params = array(), $resumptionToken = null, $recordOffset = 0) {
		$verb = $this->getHarvestingMethod();
		$parser = new XMLParser();
		$archive =& $this->getArchive();

		if ($archive->getSetting('isStatic')) {
			$harvestUrl = $this->oaiUrl;
		} else {
			$harvestingParams = array();
			$harvestingParams['verb'] = $verb;
			if ($resumptionToken !== null) {
				$harvestingParams['resumptionToken'] = $resumptionToken;
			} else {
				$harvestingParams['metadataPrefix'] = $this->getMetadataFormat();
				foreach (array('from', 'until') as $name) {
					if (isset($params[$name]) && $params[$name] == 'now') {
						$params[$name] = date('Y-m-d');
					} else if (isset($params[$name]) && $params[$name] == 'last') {
						$lastHarvested = $this->archive->getLastIndexedDate();
						if (empty($lastHarvested)) {
							unset($params[$name]);
						} else {
							$params[$name] = date('Y-m-d', strtotime($lastHarvested));
						}
					}
				}
				foreach (array('set', 'from', 'until') as $name) {
					if (isset($params[$name])) $harvestingParams[$name] = $params[$name];
				}
			}
			$harvestUrl = $this->addParameters($this->oaiUrl, $harvestingParams);
		}
		if (isset($params['verbose'])) echo "Harvest URL: $harvestUrl\n";
		$this->throttle();
		$result =& $parser->parse($harvestUrl);
		if (!$parser->getStatus()) {
			foreach ($parser->getErrors() as $error) {
				$this->addError($error);
			}
			if (!$result) return false;
		}

		unset($parser);
		unset($xmlHandler);

		if ($errorNode =& $result->getChildByName('error')) {
			$this->addError ($errorNode->getValue());
			return false;
		}

		$token = null;

		$verbNode =& $result->getChildByName($verb);
		foreach ($verbNode->getChildren() as $node) {
			switch ($node->getName()) {
				case 'header':
					$identifierNode =& $node->getChildByName('identifier');
					$this->updateRecord($identifierNode->getValue(), $params);
					unset($identifierNode);
					$recordOffset++;
					break;
				case 'record':
					$this->handleRecordNode($node);
					$recordOffset++;
					break;
				case 'resumptionToken':
					$token = $node->getValue();
					break;
				default:
					$this->addError('Unknown node ' . $node->getName());
			}
		}

		if ($token) {
			unset($verbNode, $result, $node); // Free memory
			return $this->updateRecords($params, $token, $recordOffset);
		}
		if (!$this->getStatus()) return false; // Error
		return $recordOffset;
	}

	function handleRecordNode(&$node) {
		$headerNode =& $node->getChildByName('header');
		$identifierNode =& $headerNode->getChildByName('identifier');
		$identifier = $identifierNode->getValue();

		$metadataContainerNode =& $node->getChildByName('metadata');
		if (!$metadataContainerNode) {
			// This is a deleted record.
			if (isset($params['verbose'])) echo "Deleted record: $identifier\n";
			return $this->_deleteRecordByIdentifier($identifier);
		}
		$metadataNode = array_shift($metadataContainerNode->getChildren());

		$record =& $this->getRecordByIdentifier($identifier);
		if (!$record) {
			// This is a new record.
			return $this->_insertRecord($identifier, $metadataNode->toXml());
		} else {
			return $this->_updateRecord($record, $metadataNode->toXml());
		}
	}

	/**
	 * Update a single record by identifier.
	 * @param $identifier string
	 * @param $params array
	 * @return object Record
	 */
	function &updateRecord($identifier, $params = array()) {
		$verb = 'GetRecord';
		$this->throttle();
		$parser = new XMLParser();
		$result =& $parser->parse($this->addParameters($this->oaiUrl, array(
			'verb' => $verb,
			'identifier' => $identifier,
			'metadataPrefix' => $this->getMetadataFormat()
		)));
		if (!$parser->getStatus()) {
			foreach ($parser->getErrors() as $error) {
				$this->addError($error);
			}
			if (!$result) return false;
		}
		unset($parser);

		if ($errorNode =& $result->getChildByName('error')) {
			$this->addError ($errorNode->getValue());
		}

		$verbNode =& $result->getChildByName($verb);
		$recordNode =& $result->getChildByName('record');
		$this->handleRecordNode($recordNode);

		return $result;
	}

	/**
	 * Return a UTC-formatted datestamp from the specified UNIX timestamp.
	 * @param $timestamp int *nix timestamp (if not used, the current time is used)
	 * @param $includeTime boolean include both the time and date
	 * @return string UTC datestamp
	 */
	function UTCDate($timestamp = 0, $includeTime = true) {
		$format = "Y-m-d";
		if($includeTime) {
			$format .= "\TH:i:s\Z";
		}

		if($timestamp == 0) {
			return gmdate($format);

		} else {
			return gmdate($format, $timestamp);
		}
	}

	/**
	 * Returns a UNIX timestamp from a UTC-formatted datestamp.
	 * Returns null if datestamp is invalid
	 * @param $date string UTC datestamp
	 * @return int timestamp
	 */
	function UTCtoTimestamp($date, $checkGranularity = true) {
		// FIXME Has limited range (see http://php.net/strtotime)
		if (preg_match("/^\d\d\d\d\-\d\d\-\d\d$/", $date)) {
			// Match date
			$time = strtotime("$date UTC");
			return ($time != -1) ? $time : 'invalid';

		} else if (preg_match("/^(\d\d\d\d\-\d\d\-\d\d)T(\d\d:\d\d:\d\d)Z$/", $date, $matches)) {
			// Match datetime
			// FIXME
			$date = "$matches[1] $matches[2]";
			$time = strtotime("$date UTC");
			return ($time != -1) ? $time : 'invalid';
		} else {
			return null;
		}
	}

	function &getSchema() {
		$schemaDao =& DAORegistry::getDAO('SchemaDAO');
		$aliases = $schemaDao->getSchemaAliases();
		$metadataFormat = $this->getMetadataFormat();
		$returner = null;
		if (!isset($aliases[$metadataFormat])) return $returner;

		$returner = $schemaDao->buildSchema($aliases[$metadataFormat]);
		return $returner;
	}

	function &getSchemaPlugin() {
		$schemaDao =& DAORegistry::getDAO('SchemaDAO');
		$aliases = $schemaDao->getSchemaAliases();
		$metadataFormat = $this->getMetadataFormat();
		$returner = null;
		if (!isset($aliases[$metadataFormat])) return $returner;

		$pluginName = $aliases[$metadataFormat];

		$plugins =& PluginRegistry::loadCategory('schemas');
		if (!isset($plugins[$pluginName])) return $returner;
		$returner =& $plugins[$pluginName];
		return $returner;
	}

	/**
	 * Add parameters to the given GET URL.
	 * @param $url string
	 * @param $params associative array
	 * @return string
	 */
	function addParameters($url, $params) {
		if (strpos($url, '?') !== false && !empty($params)) {
			$separator = '&';
		} else {
			$separator = '?';
		}
		foreach ($params as $name => $value) {
			$url .= $separator . urlencode($name) . '=' . urlencode($value);
			$separator = '&';
		}
		return $url;
	}
}

?>
