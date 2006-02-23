<?php

/**
 * RecordDAO.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package record
 *
 * Class for Record DAO.
 * Operations for retrieving and modifying Record objects.
 *
 * $Id$
 */

import ('record.Record');

define('RECORD_SORT_NONE', 0x000001);
define('RECORD_SORT_DATE', 0x000002);

class RecordDAO extends DAO {

	/**
	 * Constructor.
	 */
	function RecordDAO() {
		parent::DAO();
	}
	
	/**
	 * Retrieve a record by ID.
	 * @param $recordId int
	 * @return Record
	 */
	function &getRecord($recordId) {
		$result = &$this->retrieve(
			'SELECT * FROM records WHERE record_id = ?', $recordId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnRecordFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);
		return $returner;
	}

	/**
	 * Get the entries for this record.
	 */
	function getEntries($recordId) {
		$result = &$this->retrieve(
			'SELECT e.*, f.name AS field_name FROM entries e, raw_fields f WHERE f.raw_field_id = e.raw_field_id AND record_id = ?', $recordId
		);

		$returner = array();
		while (!$result->EOF) {
			$row = &$result->getRowAssoc(false);

			$fieldName = $row['field_name'];
			if (!empty($returner[$fieldName])) {
				if (is_array($returner[$fieldName])) {
					array_push($returner[$fieldName], $row['value']);
				} else {
					$returner[$fieldName] = array(
						$returner[$fieldName],
						$row['value']
					);
				}
			} else {
				$returner[$fieldName] = $row['value'];
			}
			$result->MoveNext();
		}
		$result->Close();
		unset($result);

		return $returner;
	}

	function deleteEntriesByRecordId($recordId) {
		return $this->update('DELETE FROM entries WHERE record_id = ?', $recordId);
	}

	/**
	 * Retrieve a record by record identifier. 
	 * @param $identifer int
	 * @return Record
	 */
	function &getRecordByIdentifier($identifier) {
		$result = &$this->retrieve(
			'SELECT * FROM records WHERE identifier = ?', $identifier
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnRecordFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);
		return $returner;
	}
	
	/**
	 * Internal function to return a Record object from a row.
	 * @param $row array
	 * @return Record
	 */
	function &_returnRecordFromRow(&$row) {
		$record = &new Record();
		$record->setRecordId($row['record_id']);
		$record->setArchiveId($row['archive_id']);
		$record->setSchemaId($row['schema_plugin_id']);
		$record->setIdentifier($row['identifier']);
		$record->setDatestamp($row['datestamp']);
		
		HookRegistry::call('RecordDAO::_returnRecordFromRow', array(&$record, &$row));

		return $record;
	}

	/**
	 * Insert an entry for the given field of the given record, with
	 * the supplied type and value.
	 */
	function insertEntry($recordId, $fieldId, $value, $seq = 0) {
		$this->update('INSERT INTO entries (record_id, field_id, value, seq) VALUES (?, ?, ?, ?)', array($recordId, $fieldId, $value, $seq));
	}

	/**
	 * Insert a new record.
	 * @param $record Record
	 */	
	function insertRecord(&$record) {
		$this->update(
			sprintf('INSERT INTO records
				(archive_id, schema_plugin_id, identifier, datestamp)
				VALUES
				(?, ?, ?, %s)',
				$this->datetimeToDB($record->getDatestamp())
			),
			array(
				$record->getArchiveId(),
				$record->getSchemaId(),
				$record->getIdentifier()
			)
		);
		
		$record->setRecordId($this->getInsertRecordId());
		return $record->getRecordId();
	}
	
	/**
	 * Update an existing record.
	 * @param $record Record
	 */
	function updateRecord(&$record) {
		return $this->update(
			sprintf('UPDATE records
				SET
					archive_id = ?,
					schema_id = ?,
					identifier = ?,
					datestamp = %s
				WHERE record_id = ?',
				$this->datetimeToDB($record->getDatestamp())
			),
			array(
				$record->getArchiveId(),
				$record->getSchemaId(),
				$record->getIdentifier(),
				$record->getRecordId()
			)
		);
	}
	
	/**
	 * Delete a record, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $record Record
	 */
	function deleteRecord(&$record) {
		return $this->deleteRecordById($record->getRecordId());
	}

	/**
	 * Delete the records for a specified archive, INCLUDING ALL DEPENDENT ITEMS.
	 */
	function deleteRecordsByArchiveId($archiveId) {
		$records =& $this->getRecords($archiveId);
		while ($record =& $records->next()) {
			$this->deleteRecord($record);
		}
	}

	/**
	 * Delete a record by ID, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $recordId int
	 */
	function deleteRecordById($recordId) {
		$this->deleteEntriesByRecordId($recordId);
		return $this->update(
			'DELETE FROM records WHERE record_id = ?', $recordId
		);
	}
	
	/**
	 * Retrieve all records in an archive.
	 * @return DAOResultFactory containing matching records
	 */
	function &getRecords($archiveId = null, $sort = RECORD_SORT_NONE, $rangeInfo = null) {
		$params = null;
		if (isset($archiveId)) $params = $archiveId;

		switch ($sort) {
			case RECORD_SORT_NONE:
				$sort = '';
				break;
			case RECORD_SORT_DATE:
				$sort = 'ORDER BY datestamp DESC ';
				break;
			default:
				fatalError("Unknown sort order $sort!");
		}

		$result = &$this->retrieveRange(
			'SELECT * FROM records ' .
			(isset($archiveId)? 'WHERE archive_id = ? ':'') .
			$sort,
			$params, $rangeInfo
		);

		$returner = &new DAOResultFactory($result, $this, '_returnRecordFromRow');
		return $returner;
	}
	
	/**
	 * Get the ID of the last inserted record.
	 * @return int
	 */
	function getInsertRecordId() {
		return $this->getInsertId('records', 'record_id');
	}
	
	/**
	 * Retrieve a count of the records available.
	 * @param $archiveId Specific archive to query (optional)
	 * @return int
	 */
	function getRecordCount($archiveId = null) {
		$result = &$this->retrieve(
			'SELECT COUNT(*) AS count FROM records' . (isset($archiveId)?' WHERE archive_id = ?':''),
			isset($archiveId)?$archiveId:false
		);

		$count = 0;
		if ($result->RecordCount() != 0) {
			$row =& $result->GetRowAssoc(false);
			$count = $row['count'];
		}
		$result->Close();
		unset($result);
		return $count;
	}
}

?>
