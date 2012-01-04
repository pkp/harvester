<?php

/**
 * @file RecordDAO.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package record
 * @class RecordDAO
 *
 * Class for Record DAO.
 * Operations for retrieving and modifying Record objects.
 *
 * $Id$
 */

import ('classes.record.Record');

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
		$result =& $this->retrieve(
			'SELECT * FROM records WHERE record_id = ?', $recordId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnRecordFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);
		return $returner;
	}

	/**
	 * Retrieve a record by ID.
	 * @param $recordId int
	 * @return Record
	 */
	function getRecordSchemaPluginName($recordId) {
		$result =& $this->retrieve(
			'SELECT	s.schema_plugin
			FROM	records r,
				schema_plugins s
			WHERE	r.record_id = ? AND
				r.schema_plugin_id = s.schema_plugin_id',
			array((int) $recordId)
		);

		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;
		$result->Close();
		unset($result);
		return $returner;
	}

	/**
	 * Retrieve records by archive.
	 * @param $recordId int
	 * @param $enabledOnly boolean
	 * @param $sortOrder object optional
	 * @param $rangeInfo object optional
	 * @return Record
	 */
	function &getRecords($archiveId, $enabledOnly = true, $sortOrder = null, $rangeInfo = null) {
		$params = array();
		if ($archiveId !== null) $params[] = (int) $archiveId;
		if ($sortOrder) {
			$params[] = (int) $sortOrder->getSortOrderId();
			switch ($sortOrder->getType()) {
				case SORT_ORDER_TYPE_NUMBER:
					$sortOrderFromSql = ', sort_order_numbers son';
					$sortOrderWhereSql = ' AND son.record_id = r.record_id AND son.sort_order_id = ?';
					$sortOrderOrderSql = ' ORDER BY son.value';
					break;
				case SORT_ORDER_TYPE_DATE:
					$sortOrderFromSql = ', sort_order_dates sod';
					$sortOrderWhereSql = ' AND sod.record_id = r.record_id AND sod.sort_order_id = ?';
					$sortOrderOrderSql = ' ORDER BY sod.value';
					break;
				case SORT_ORDER_TYPE_STRING:
					$sortOrderFromSql = ', sort_order_strings sos';
					$sortOrderWhereSql = ' AND sos.record_id = r.record_id AND sos.sort_order_id = ?';
					$sortOrderOrderSql = ' ORDER BY sos.value';
					break;
				default:
					fatalError('Unknown sort order type!');
			}
		} else {
			$sortOrderFromSql = $sortOrderWhereSql = $sortOrderOrderSql = '';
		}

		
		$result =& $this->retrieveRange(
			'SELECT	r.*
			FROM	records r,
				archives a' .
				$sortOrderFromSql . '
			WHERE	a.archive_id = r.archive_id' .
			($enabledOnly?' AND a.enabled = 1':'') .
			($archiveId !== null?' AND a.archive_id = ?':'') .
			$sortOrderWhereSql .
			$sortOrderOrderSql,
			$params,
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnRecordFromRow');
		return $returner;
	}

	/**
	 * Retrieve a record by record identifier. 
	 * @param $identifer int
	 * @return Record
	 */
	function &getRecordByIdentifier($identifier) {
		$result =& $this->retrieve(
			'SELECT * FROM records WHERE identifier = ?', $identifier
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnRecordFromRow($result->GetRowAssoc(false));
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
		$record = new Record();
		$record->setRecordId($row['record_id']);
		$record->setArchiveId($row['archive_id']);
		$record->setSchemaId($row['schema_plugin_id']);
		$record->setIdentifier($row['identifier']);
		$record->setDatestamp($row['datestamp']);
		$record->setContents($row['contents']);
		$record->setParsedContents(unserialize($row['parsed_contents']));

		HookRegistry::call('RecordDAO::_returnRecordFromRow', array(&$record, &$row));

		return $record;
	}

	/**
	 * Insert a new record.
	 * @param $record Record
	 */	
	function insertRecord(&$record) {
		$this->update(
			sprintf('INSERT INTO records
				(archive_id, schema_plugin_id, identifier, datestamp, contents, parsed_contents)
				VALUES
				(?, ?, ?, %s, ?, ?)',
				$this->datetimeToDB($record->getDatestamp())
			),
			array(
				$record->getArchiveId(),
				$record->getSchemaId(),
				$record->getIdentifier(),
				$record->getContents(),
				serialize($record->getParsedContents())
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
					schema_plugin_id = ?,
					identifier = ?,
					datestamp = %s,
					contents = ?,
					parsed_contents = ?
				WHERE record_id = ?',
				$this->datetimeToDB($record->getDatestamp())
			),
			array(
				$record->getArchiveId(),
				$record->getSchemaId(),
				$record->getIdentifier(),
				$record->getContents(),
				serialize($record->getParsedContents()),
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
		$this->update('DELETE FROM records WHERE archive_id = ?', $archiveId);
	}

	/**
	 * Delete a record by numeric record ID.
	 * @param $recordId int
	 */
	function deleteRecordById($recordId) {
		return $this->update(
			'DELETE FROM records WHERE record_id = ?',
			array((int) $recordId)
		);
	}

	/**
	 * Delete a record by string identifier.
	 * @param $identifier string
	 */
	function deleteRecordByIdentifier($identifier) {
		return $this->update(
			'DELETE FROM records WHERE identifier = ?',
			array((string) $identifier)
		);
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
		$result =& $this->retrieve(
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
