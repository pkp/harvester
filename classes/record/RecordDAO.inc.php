<?php

/**
 * RecordDAO.inc.php
 *
 * Copyright (c) 2005 The Public Knowledge Project
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
	 * @param $getEntries boolean
	 * @return Record
	 */
	function &getRecord($recordId, $getEntries = false) {
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

	function getEntries($recordId) {
		$result = &$this->retrieve(
			'SELECT e.*, f.type AS field_type FROM entries e, fields f WHERE f.field_id = e.field_id AND record_id = ?', $recordId
		);

		$returner = array();
		while (!$result->EOF) {
			$row = &$result->getRowAssoc(false);
			$value = null;
			switch ($row['field_type']) {
				case 'bool':
					$value = $row['value']?true:false;
					break;
				case 'int':
				case 'float':
				case 'string':
				case 'date':
					$value = $row['value'];
					break;
				case 'object':
					$value = unserialize($row['value']);
					break;
				default:
					fatalError('Unknown field type ' . $row['field_type']);
					break;
			}

			$fieldId = $row['field_id'];
			if (!empty($result[$field_id])) {
				if (is_array($result[$field_id])) {
					array_push($result[$field_id], $value);
				} else {
					$result[$field_id] = array(
						$result[$field_id],
						$value
					);
				}
			} else {
				$result[$field_id] = $value;
			}
			
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
		$record->setIdentifier($row['identifier']);
		$record->setDatestamp($row['datestamp']);
		
		HookRegistry::call('RecordDAO::_returnRecordFromRow', array(&$record, &$row));

		return $record;
	}

	/**
	 * Insert an entry for the given field of the given record, with
	 * the supplied type and value.
	 */
	function insertEntry($recordId, $fieldId, $type, $value) {
		$isDate = false;
		switch ($type) {
			case 'bool':
			case 'int':
			case 'float':
			case 'string':
				// Do nothing.
				break;
			case 'date':
				// Special case: Handled below.`
				$isDate = true;
				$value = $this->datetimeToDB($value);
				break;
			case 'object':
				$value = serialize($value);
				break;
			default:
				fatalError("Unknown type $type!");
				break;
		}

		if ($isDate) {
			$this->update(
				sprintf('INSERT INTO entries (record_id, field_id, value) VALUES (?, ?, %s)', $value),
				array($recordId, $fieldId)
			);
		} else {
			$this->update('INSERT INTO entries (record_id, field_id, value) VALUES (?, ?, ?)', array($recordId, $fieldId, $value));
		}
	}

	/**
	 * Insert a new record.
	 * @param $record Record
	 */	
	function insertRecord(&$record) {
		$this->update(
			sprintf('INSERT INTO records
				(archive_id, identifier, datestamp)
				VALUES
				(?, ?, %s)',
				$this->datetimeToDB($record->getDatestamp())
			),
			array(
				$record->getArchiveId(),
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
					identifier = ?,
					datestamp = %s
				WHERE record_id = ?',
				$this->datetimeToDB($record->getDatestamp())
			),
			array(
				$record->getArchiveId(),
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
	 * Delete a record by ID, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $recordId int
	 */
	function deleteRecordById($recordId) {
		return $this->update(
			'DELETE FROM records WHERE record_id = ?', $recordId
		);
	}
	
	/**
	 * Retrieve all records in an archive.
	 * @return DAOResultFactory containing matching records
	 */
	function &getRecordsByArchiveId($archiveId, $rangeInfo = null) {
		$result = &$this->retrieveRange(
			'SELECT * FROM records WHERE archive_id = ?',
			$archiveId, $rangeInfo
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
	 * @return int
	 */
	function getRecordCount() {
		$result = &$this->retrieve('SELECT COUNT(*) AS count FROM records');

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
