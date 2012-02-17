<?php

/**
 * @defgroup oai_harvester
 */
 
/**
 * @file classes/oai/harvester/ArchiveOAI.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArchiveOAI
 * @ingroup oai_harvester
 * @see OAIDAO
 *
 * @brief Harvester-specific OAI interface.
 * Designed to support an OAI interface
 *
 */

// $Id$


import('lib.pkp.classes.oai.OAI');
import('classes.oai.harvester.OAIDAO');

class ArchiveOAI extends OAI {
	/** @var $site Site associated site object */
	var $site;

	/** @var $dao OAIDAO DAO for retrieving OAI records/tokens from database */
	var $dao;


	/**
	 * @see OAI#OAI
	 */
	function ArchiveOAI($config) {
		parent::OAI($config);

		$this->site =& Request::getSite();
		$this->dao =& DAORegistry::getDAO('OAIDAO');
		$this->dao->setOAI($this);
	}

	/**
	 * Return a list of ignorable GET parameters.
	 * @return array
	 */
	function getNonPathInfoParams() {
		return array();
	}

	/**
	 * Convert record ID to OAI identifier.
	 * @param $recordId int
	 * @return string
	 */
	function recordIdToIdentifier($recordId) {
		return 'oai:' . $this->config->repositoryId . ':' . 'record/' . $recordId;
	}

	/**
	 * Convert OAI identifier to record ID.
	 * @param $identifier string
	 * @return int
	 */
	function identifierToRecordId($identifier) {
		$prefix = 'oai:' . $this->config->repositoryId . ':' . 'record/';
		if (strstr($identifier, $prefix)) {
			return (int) str_replace($prefix, '', $identifier);
		} else {
			return false;
		}
	}

	/**
	 * Get the archive ID corresponding to a set specifier.
	 * @return int
	 */	
	function setSpecToArchiveId($setSpec, $archiveId = null) {
		$tmpArray = split(':', $setSpec);
		if (count($tmpArray) == 1) {
			$setSpec = array_shift($tmpArray);
			$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
			$archive =& $archiveDao->getArchiveByTitle($setSpec);
			if ($archive) return $archive->getArchiveId();
		}
		return null;
	}

	/**
	 * Returns formatted metadata response in specified format.
	 * @param $format string
	 * @param $metadata OAIMetadata
	 * @return string
	 */
	function &formatMetadata($format, $record) {
		$schemaPluginName = $record->getData('schemaPluginName');
		$plugin =& PluginRegistry::getPlugin('schemas', $schemaPluginName);
		$formatClass = $plugin->getFormatClass();
		$metadata = call_user_func_array(array($formatClass, 'toXml'), array(&$record, $format));
		return $metadata;
	}

	//
	// OAI interface functions
	//

	/**
	 * @see OAI#repositoryInfo
	 */
	function &repositoryInfo() {
		$info = new OAIRepository();

		$info->repositoryName = $this->site->getLocalizedTitle();
		$info->adminEmail = $this->site->getLocalizedContactEmail();
		$info->sampleIdentifier = $this->recordIdToIdentifier(1);
		$info->earliestDatestamp = $this->dao->getEarliestDatestamp();

		return $info;
	}

	/**
	 * @see OAI#validIdentifier
	 */
	function validIdentifier($identifier) {
		return $this->identifierToRecordId($identifier) !== false;
	}

	/**
	 * @see OAI#identifierExists
	 */
	function identifierExists($identifier) {
		$recordExists = false;
		$recordId = $this->identifierToRecordId($identifier);
		if ($recordId) {
			$recordExists = $this->dao->recordExists($recordId);
		}
		return $recordExists;
	}

	/**
	 * @see OAI#metadataFormats
	 */
	function &metadataFormats($namesOnly = false, $identifier = null) {
		// Get a full list of metadata formats implemented in the system
		$metadataFormats =& parent::metadataFormats($namesOnly, $identifier);

		// ...and if we need to, check that this record is able to do it
		if ($identifier !== null) {
			$recordId = $this->identifierToRecordId($identifier);
			$recordDao =& DAORegistry::getDAO('RecordDAO');
			$schemaPluginName = $recordDao->getRecordSchemaPluginName($recordId);
		}

		return $metadataFormats;
	}

	/**
	 * @see OAI#record
	 */
	function &record($identifier) {
		$recordId = $this->identifierToRecordId($identifier);
		if ($recordId) {
			$record =& $this->dao->getRecord($recordId);
		}
		if (!isset($record)) {
			$record = false;
		}
		return $record;		
	}

	/**
	 * @see OAI#records
	 */
	function &records($metadataPrefix, $from, $until, $set, $offset, $limit, &$total) {
		$archiveId = null;
		if (isset($set)) {
			$archiveId = $this->setSpecToArchiveId($set);
		}
		$records =& $this->dao->getRecords($metadataPrefix, $archiveId, $from, $until, $offset, $limit, $total);
		return $records;
	}

	/**
	 * @see OAI#identifiers
	 */
	function &identifiers($metadataPrefix, $from, $until, $set, $offset, $limit, &$total) {
		$archiveId = null;
		if (isset($set)) {
			$archiveId = $this->setSpecToArchiveId($set);
		}
		$records =& $this->dao->getIdentifiers($metadataPrefix, $archiveId, $from, $until, $offset, $limit, $total);
		return $records;
	}

	/**
	 * @see OAI#sets
	 */
	function &sets($offset, &$total) {
		$sets =& $this->dao->getSets($offset, $total);
		return $sets;
	}

	/**
	 * @see OAI#resumptionToken
	 */
	function &resumptionToken($tokenId) {
		$this->dao->clearTokens();
		$token = $this->dao->getToken($tokenId);
		if (!isset($token)) {
			$token = false;
		}
		return $token;
	}

	/**
	 * @see OAI#saveResumptionToken
	 */
	function &saveResumptionToken($offset, $params) {
		$token = new OAIResumptionToken(null, $offset, $params, time() + $this->config->tokenLifetime);
		$this->dao->insertToken($token);
		return $token;
	}
}

?>
