<?php

/**
 * OAIHarvester.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins
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
	function getMetadataFormat() {
		$archive =& $this->getArchive();
		$schemaPluginName = $archive->getSchemaPluginName();
		if (
			empty($schemaPluginName) ||
			($alias = SchemaMap::getSchemaAlias(OAIHarvesterPlugin::getName(), $schemaPluginName))==''
		) {
			return 'oai_dc';
		}
		return $alias;
	}

	/**
	 * Get a list of supported metadata formats for this archive.
	 * This is a static method.
	 * @return array
	 */
	function getMetadataFormats($harvesterUrl) {
		$parser =& new XMLParser();
		$xmlHandler =& new OAIXMLHandler($this, 'ListMetadataFormats');

		$parser->setHandler($xmlHandler);
		$result = null;
		@$result =& $parser->parse($this->addParameters($harvesterUrl, array(
			'verb' => 'ListMetadataFormats'
		)));

		unset($parser);
		unset($xmlHandler);
		
		if (empty($result)) return array('oai_dc');
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

	function updateRecords($params = array()) {
		$this->fieldDao->enableCaching();

		$verb = $this->getHarvestingMethod();
		$parser =& new XMLParser();
		$xmlHandler =& new OAIXMLHandler($this, $verb, $params);

		$parser->setHandler($xmlHandler);
		$result =& $parser->parse($this->addParameters($this->oaiUrl, array(
			'verb' => $verb,
			'metadataPrefix' => $this->getMetadataFormat()
		)));

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
