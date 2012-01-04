<?php

/**
 * @file classes/oai/harvester/OAIDAO.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIDAO
 * @ingroup oai_harvester
 * @see OAI
 *
 * @brief DAO operations for the Harvester OAI interface.
 */

// $Id$


import('lib.pkp.classes.oai.OAI');
import('classes.harvester.Harvester');

class OAIDAO extends DAO {
 	/** @var $oai ArchiveOAI parent OAI object */
 	var $oai;

 	/** Helper DAOs */
	var $archiveDao;
	var $recordDao;

 	/**
	 * Constructor.
	 */
	function OAIDAO() {
		parent::DAO();
		$this->archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$this->recordDao =& DAORegistry::getDAO('RecordDAO');

		PluginRegistry::loadCategory('schemas');
	}

	/**
	 * Set parent OAI object.
	 * @param ArchiveOAI
	 */
	function setOAI(&$oai) {
		$this->oai = $oai;
	}

	//
	// Records
	//

	/**
	 * Return the *nix timestamp of the earliest record.
	 * @return int
	 */
	function getEarliestDatestamp() {
		$result =& $this->retrieve(
			'SELECT MIN(r.datestamp)
			FROM records r, archives a
			WHERE r.archive_id = a.archive_id AND a.enabled = 1'
		);

		if (isset($result->fields[0])) {
			$timestamp = strtotime($this->datetimeFromDB($result->fields[0]));
		}
		if (!isset($timestamp) || $timestamp == -1) {
			$timestamp = 0;
		}

		$result->Close();
		unset($result);

		return $timestamp;
	}

	/**
	 * Check if a record ID specifies a record.
	 * @param $recordId int
	 * @return boolean
	 */
	function recordExists($recordId) {
		$result =& $this->retrieve(
			'SELECT	COUNT(*)
			FROM	archives a,
				records r
			WHERE	r.archive_id = a.archive_id AND
				a.enabled = 1 AND
				r.record_id = ?',
			array((int) $recordId)
		);

		$returner = $result->fields[0] == 1;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Return OAI record for specified record.
	 * @param $recordId int
	 * @return OAIRecord
	 */
	function &getRecord($recordId) {
		$result =& $this->retrieve(
			'SELECT	r.*, a.*
			FROM	records r,
				archives a
			WHERE	r.archive_id = a.archive_id AND
				a.enabled = 1 AND
				r.record_id = ?',
			array((int) $recordId)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$row =& $result->GetRowAssoc(false);
			$returner =& $this->_returnRecordFromRow($row);
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Return set of OAI records matching specified parameters.
	 * @param $metadataPrefix string
	 * @param $archiveId int
	 * @parma $from int timestamp
	 * @parma $until int timestamp
	 * @param $offset int
	 * @param $limit int
	 * @param $total int
	 * @return array OAIRecord
	 */
	function &getRecords($metadataPrefix, $archiveId, $from, $until, $offset, $limit, &$total) {
		$records = array();

		$params = array();
		if ($metadataPrefix != DUBLIN_CORE_METADATA_PREFIX) $params[] = $metadataPrefix;
		if (isset($archiveId)) $params[] = $archiveId;

		$result =& $this->retrieve(
			'SELECT	r.*,
				a.*
			FROM	records r,
				archives a' .
				($metadataPrefix != DUBLIN_CORE_METADATA_PREFIX ? ', schema_aliases sa' : '') . '
			WHERE	r.archive_id = a.archive_id AND ' .
				($metadataPrefix != DUBLIN_CORE_METADATA_PREFIX ? 'sa.schema_plugin_id = r.schema_plugin_id AND sa.alias = ? AND ':'') . '
				a.enabled = 1' .
				(isset($archiveId)?' AND a.archive_id = ?':'') . '
			ORDER BY a.archive_id',
			$params
		);

		$total = $result->RecordCount();

		$result->Move($offset);
		for ($count = 0; $count < $limit && !$result->EOF; $count++) {
			$row =& $result->GetRowAssoc(false);
			$records[] =& $this->_returnRecordFromRow($row);
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $records;
	}

	/**
	 * Return set of OAI identifiers matching specified parameters.
	 * @param $metadataPrefix string
	 * @param $archiveId int
	 * @parma $from int timestamp
	 * @parma $until int timestamp
	 * @param $offset int
	 * @param $limit int
	 * @param $total int
	 * @return array OAIIdentifier
	 */
	function &getIdentifiers($metadataPrefix, $archiveId, $from, $until, $offset, $limit, &$total) {
		
		$records = array();

		$params = array();
		if ($metadataPrefix != DUBLIN_CORE_METADATA_PREFIX) $params[] = $metadataPrefix;
		if (isset($archiveId)) $params[] = $archiveId;

		$result =& $this->retrieve(
			'SELECT	r.*,
				a.*
			FROM	records r,
				archives a
			WHERE	r.archive_id = a.archive_id AND ' .
				($metadataPrefix != DUBLIN_CORE_METADATA_PREFIX ? 'a.schema_plugin = ? AND ':'') . '
				a.enabled = 1' .
				(isset($archiveId)?' AND a.archive_id = ?':'') . '
			ORDER BY a.archive_id',
			$params
		);

		$total = $result->RecordCount();

		$result->Move($offset);
		for ($count = 0; $count < $limit && !$result->EOF; $count++) {
			$row =& $result->GetRowAssoc(false);
			$records[] =& $this->_returnIdentifierFromRow($row);
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $records;
	}

	function stripAssocArray($values) {
		foreach (array_keys($values) as $key) {
			$values[$key] = strip_tags($values[$key]);
		}
		return $values;
	}

	/**
	 * Return OAIRecord object from database row.
	 * @param $row array
	 * @return OAIRecord
	 */
	function &_returnRecordFromRow(&$row) {
		$oaiRecord = new OAIRecord();

		$record =& $this->recordDao->_returnRecordFromRow($row);
		$archive =& $this->archiveDao->_returnArchiveFromRow($row);

		$oaiRecord->identifier = $this->oai->recordIdToIdentifier($record->getRecordId());
		$oaiRecord->datestamp = OAIUtils::UTCDate(strtotime($this->datetimeFromDB($row['datestamp'])));
		$oaiRecord->sets = array($row['archive_id']);

		$oaiRecord->setData('archive', $archive);
		$oaiRecord->setData('record', $record);
		$oaiRecord->setData('schemaPluginName', $row['schema_plugin']);

		return $oaiRecord;
	}

	/**
	 * Return OAIIdentifier object from database row.
	 * @param $row array
	 * @return OAIIdentifier
	 */
	function &_returnIdentifierFromRow(&$row) {
		$oaiRecord = new OAIRecord();

		$oaiRecord->identifier = $this->oai->recordIdToIdentifier($row['record_id']);
		$oaiRecord->datestamp = OAIUtils::UTCDate(strtotime($this->datetimeFromDB($row['datestamp'])));
		$oaiRecord->sets = array($row['archive_id']);

		return $oaiRecord;
	}

	//
	// Resumption tokens
	//

	/**
	 * Clear stale resumption tokens.
	 */
	function clearTokens() {
		$this->update(
			'DELETE FROM oai_resumption_tokens WHERE expire < ?', time()
		);
	}

	/**
	 * Retrieve a resumption token.
	 * @return OAIResumptionToken
	 */
	function &getToken($tokenId) {
		$result =& $this->retrieve(
			'SELECT * FROM oai_resumption_tokens WHERE token = ?', $tokenId
		);

		if ($result->RecordCount() == 0) {
			$token = null;
		} else {
			$row =& $result->getRowAssoc(false);
			$token = new OAIResumptionToken($row['token'], $row['record_offset'], unserialize($row['params']), $row['expire']);
		}

		$result->Close();
		unset($result);

		return $token;
	}

	/**
	 * Insert an OAI resumption token, generating a new ID.
	 * @param $token OAIResumptionToken
	 * @return OAIResumptionToken
	 */
	function &insertToken(&$token) {
		do {
			// Generate unique token ID
			$token->id = md5(uniqid(mt_rand(), true));
			$result =& $this->retrieve(
				'SELECT COUNT(*) FROM oai_resumption_tokens WHERE token = ?',
				$token->id
			);
			$val = $result->fields[0];

			$result->Close();
			unset($result);
		} while($val != 0);

		$this->update(
			'INSERT INTO oai_resumption_tokens (token, record_offset, params, expire)
			VALUES
			(?, ?, ?, ?)',
			array($token->id, $token->offset, serialize($token->params), $token->expire)
		);

		return $token;
	}

	//
	// Sets
	//

	/**
	 * Return hierarchy of OAI sets.
	 * @param $offset int
	 * @param $total int
	 * @return array OAISet
	 */
	function &getSets($offset, &$total) {
		$archives =& $this->archiveDao->getArchives();
		$archives =& $archives->toArray();

		// FIXME Set descriptions
		$sets = array();
		foreach ($archives as $archive) {
			$title = $archive->getTitle();
			array_push($sets, new OAISet($title, $title, ''));
		}

		if ($offset != 0) {
			$sets = array_slice($sets, $offset);
		}

		return $sets;
	}
}

?>
