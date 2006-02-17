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
		$this->oaiUrl = $archive->getSetting('harvesterUrl');
	}

	/**
	 * Set the metadata format.
	 * @param $metadataFormat string
	 */
	function setMetadataFormat($metadataFormat) {
		$this->metadataFormat = $metadataFormat;
	}

	/**
	 * Get the metadata format.
	 */
	function getMetadataFormat() {
		return $this->metadataFormat;
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

	function updateRecords($lastUpdateTimestamp = null) {
		$this->fieldDao->enableCaching();

		$verb = $this->getHarvestingMethod();
		$parser =& new XMLParser();
		$xmlHandler =& new OAIXMLHandler($this, $verb);

		$parser->setHandler($xmlHandler);
		$result =& $parser->parse($this->oaiUrl . "?verb=$verb&metadataPrefix=" . urlencode($this->getMetadataFormat()));

		unset($parser);
		unset($xmlHandler);

		$this->fieldDao->disableCaching();

		return $this->getStatus() && $result;
	}

	/**
	 * Update a single record by identifier.
	 * @param $identifier string
	 * @return object Record
	 */
	function &updateRecord($identifier) {
		$verb = 'GetRecord';
		$parser =& new XMLParser();
		$xmlHandler =& new OAIXMLHandler($this, $verb);

		$parser->setHandler($xmlHandler);
		$result =& $parser->parse($this->oaiUrl . "?verb=$verb&identifier=" . urlencode($identifier) . '&metadataPrefix=' . urlencode($this->getMetadataFormat()));
		unset ($parser);

		unset($parser);
		unset($xmlHandler);

		return $result;
	}

	function handleResumptionToken($token) {
		$verb = $this->getHarvestingMethod();
		$parser =& new XMLParser();
		$xmlHandler =& new OAIXMLHandler($this, $verb);

		$parser->setHandler($xmlHandler);
		$result =& $parser->parse($this->oaiUrl . "?verb=$verb&resumptionToken=" . urlencode($token));

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

	function &getSchemaPlugin() {
		return SchemaMap::getSchemaPlugin(OAIHarvesterPlugin::getName(), $this->getMetadataFormat());
	}
}

?>
