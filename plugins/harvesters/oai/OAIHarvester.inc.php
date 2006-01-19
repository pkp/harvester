<?php

/**
 * OAIHarvester.inc.php
 *
 * Copyright (c) 2005 The Public Knowledge Project
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

	/** @var $request string */
	var $request;

	/** @var $requestParams array */
	var $requestParams;

	/** @var $oaiXmlHandler object */
	var $oaiXmlHandler;

	function OAIHarvester(&$archive) {
		parent::Harvester($archive);
		$this->oaiUrl = $archive->getSetting('harvesterUrl');

		$this->oaiXmlHandler =& new OAIXMLHandler($this);
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

	function setRequest($request) {
		$this->request = $request;
	}

	function getRequest() {
		return $this->request;
	}

	function setRequestParams($requestParams) {
		$this->requestParams = $requestParams;
	}

	function getRequestParams() {
		return $this->requestParams;
	}

	function getHarvestingMethod() {
		return 'ListRecords';
	}

	function &getXmlHandler() {
		return $this->oaiXmlHandler;
	}

	function updateRecords($lastUpdateTimestamp = null) {
		$this->fieldDao->enableCaching();

		$parser =& new XMLParser();
		$parser->setHandler($this->getXmlHandler());
		$parser->parse($this->oaiUrl . '?verb=' . urlencode($this->getHarvestingMethod()) . '&metadataPrefix=' . urlencode($this->getMetadataFormat()));
		$this->fieldDao->disableCaching();
		return $this->getStatus();
	}

	function handleResumptionToken($token) {
		// This is called from within e.g. updateRecords, so no
		// further setup is required. (i.e. fieldDao will already
		// be caching-enabled.)
		$parser =& new XMLParser();
		$parser->setHandler($this->getXmlHandler());
		$parser->parse($this->oaiUrl . '?verb=' . urlencode($this->getHarvestingMethod()) . '&resumptionToken=' . urlencode($token));
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
	
}

?>
