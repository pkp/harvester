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

	/** @var $oaiXmlHandler object */
	var $oaiXmlHandler;

	function OAIHarvester(&$archive) {
		parent::Harvester($archive);
		if ($archive) $this->oaiUrl = $archive->getSetting('harvesterUrl');
	}

	/**
	 * Set the metadata format.
	 * @param $metadataFormat string
	 */
	function setMetadataFormat($metadataFormat) {
		$archive =& $this->getArchive();
		$archive->setSchemaPluginName(
			SchemaMap::getSchemaPluginName(OAIHarvesterPlugin::getName(), $metadataFormat)
		);
	}

	/**
	 * Get the metadata format.
	 */
	function getMetadataFormat($default = 'oai_dc') {
		if (isset($this->metadataFormat)) return $this->metadataFormat;

		$archive =& $this->getArchive();
		$schemaPluginName = $archive->getSchemaPluginName();
		if (empty($schemaPluginName)) {
			return ($this->metadataFormat = $default);
		}

		$aliases = SchemaMap::getSchemaAliases(OAIHarvesterPlugin::getName(), $schemaPluginName);
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
		$parser =& new XMLParser();
		$xmlHandler =& new OAIXMLHandler($this, 'ListMetadataFormats');

		if (!$static) $harvesterUrl = $this->addParameters($harvesterUrl, array(
			'verb' => 'ListMetadataFormats'
		));

		$parser->setHandler($xmlHandler);
		$result = null;
		@$result =& $parser->parse($harvesterUrl);

		unset($parser);
		unset($xmlHandler);

		if (empty($result)) return array('oai_dc');
		return $result;
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
		if (is_array($result) && !empty($result['repositoryName'])) return true;
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
		$parser =& new XMLParser();
		$xmlHandler =& new OAIXMLHandler($this, 'Identify');

		if (!$static) $harvesterUrl = $this->addParameters($harvesterUrl, array(
			'verb' => 'Identify'
		));

		$parser->setHandler($xmlHandler);
		$result = null;
		@$result =& $parser->parse($harvesterUrl);

		unset($parser);
		unset($xmlHandler);

		return $result;
	}

	/**
	 * Get a list of available sets for this archive.
	 * This is a static method.
	 * @return array
	 */
	function getSets($harvesterUrl) {
		$parser =& new XMLParser();
		$xmlHandler =& new OAIXMLHandler($this, 'ListSets');

		$parser->setHandler($xmlHandler);
		$result = null;
		@$result =& $parser->parse($this->addParameters($harvesterUrl, array(
			'verb' => 'ListSets'
		)));

		unset($parser);
		unset($xmlHandler);

		if (empty($result)) return array();
		return $result;
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
				fatalError ("Unknown indexing method $indexingMethod!");
		}
	}

	/**
	 * Update the records for this archive.
	 * The $params variable is an associative array that can include
	 * "set", "from", "until", "skipIndexing", and "verbose" as keys.
	 * @param $params array
	 * @return boolean
	 */
	function updateRecords($params = array()) {
		$this->fieldDao->enableCaching();

		$verb = $this->getHarvestingMethod();
		$parser =& new XMLParser();
		$xmlHandler =& new OAIXMLHandler($this, $verb, $params);
		$archive =& $this->getArchive();

		$parser->setHandler($xmlHandler);

		if ($archive->getSetting('isStatic')) {
			$harvestUrl = $this->oaiUrl;
		} else {
			$harvestingParameters = array(
				'verb' => $verb,
				'metadataPrefix' => $this->getMetadataFormat()
			);
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
				if (isset($params[$name])) $harvestingParameters[$name] = $params[$name];
			}
			$harvestUrl = $this->addParameters($this->oaiUrl, $harvestingParameters);
		}
		if (isset($params['verbose'])) echo "Harvest URL: $harvestUrl\n";
		$result =& $parser->parse($harvestUrl);

		unset($parser);
		unset($xmlHandler);

		$this->fieldDao->disableCaching();

		return $this->getStatus() && $result;
	}

	/**
	 * Update a single record by identifier.
	 * @param $identifier string
	 * @param $params array
	 * @return object Record
	 */
	function &updateRecord($identifier, $params = array()) {
		$verb = 'GetRecord';
		$parser =& new XMLParser();
		$xmlHandler =& new OAIXMLHandler($this, $verb, $params);

		$parser->setHandler($xmlHandler);
		$result =& $parser->parse($this->addParameters($this->oaiUrl, array(
			'verb' => $verb,
			'identifier' => $identifier,
			'metadataPrefix' => $this->getMetadataFormat()
		)));
		unset ($parser);

		unset($parser);
		unset($xmlHandler);

		return $result;
	}

	/**
	 * Handle an incoming resumption token from an OAI data source.
	 * @param $token string
	 * @param $params array (see updateRecords)
	 * @param $recordOffset int
	 * @return boolean
	 */
	function handleResumptionToken($token, $params = array(), $recordOffset = 0) {
		$verb = $this->getHarvestingMethod();
		$parser =& new XMLParser();
		$xmlHandler =& new OAIXMLHandler($this, $verb, $params, $recordOffset);

		if (isset($params['callback'])) {
			call_user_func($params['callback'], "Handling resumption token \"$token\"");
		}

		$parser->setHandler($xmlHandler);
		$result =& $parser->parse($this->addParameters($this->oaiUrl, array(
			'verb' => $verb,
			'resumptionToken' => $token
		)));

		unset ($parser);
		unset($xmlHandler);

		return $result?true:false;
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
		return SchemaMap::getSchema(OAIHarvesterPlugin::getName(), $this->getMetadataFormat());
	}

	function &getSchemaPlugin() {
		return SchemaMap::getSchemaPlugin(OAIHarvesterPlugin::getName(), $this->getMetadataFormat());
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
