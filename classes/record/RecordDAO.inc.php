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
			'SELECT e.*, f.name AS field_name, a.name AS attribute_name, a.value AS attribute_value FROM raw_fields f, entries e LEFT JOIN entry_attributes a ON a.entry_id = e.entry_id WHERE f.raw_field_id = e.raw_field_id AND e.record_id = ? ORDER BY e.entry_id ASC', $recordId
		);

		$entryId = null;

		$returner = array();
		while (!$result->EOF) {
			$row = &$result->getRowAssoc(false);

			if ($entryId != $row['entry_id']) {
				$entryId = $row['entry_id'];

				$fieldName = $row['field_name'];
				$returner[$fieldName][$entryId] = array(
					'attributes' => array(),
					'value' => $row['value']
				);
			}
			if (!empty($row['attribute_name'])) {
				$returner[$row['field_name']][$row['entry_id']]['attributes'][$row['attribute_name']] = $row['attribute_value'];
			}
			$result->MoveNext();
		}
		$result->Close();
		unset($result);

		return $returner;
	}

	function deleteEntriesByRecordId($recordId) {
		$this->update('DELETE FROM entry_attributes USING entries, entry_attributes WHERE entries.record_id = ? AND entry_attributes.entry_id = entries.entry_id', $recordId);
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
	 * @param $recordId int
	 * @param $fieldId int
	 * @param $value string
	 * @param $attributes array optional
	 * @return int ID of inserted entry
	 */
	function insertEntry($recordId, $fieldId, $value, $attributes = array()) {
		$this->update('INSERT INTO entries (record_id, raw_field_id, value) VALUES (?, ?, ?)', array($recordId, $fieldId, $value));
		$entryId = $this->getInsertId('entries', 'entry_id');
		foreach ($attributes as $name => $value) {
			$this->insertEntryAttribute($entryId, $name, $value);
		}
		return $entryId;
	}

	/**
	 * Insert an entry attribute.
	 * @param $entryId int
	 * @param $value string
	 */
	function insertEntryAttribute($entryId, $name, $value) {
		$this->update('INSERT INTO entry_attributes (entry_id, name, value) VALUES (?, ?, ?)', array($entryId, $name, $value));
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
					schema_plugin_id = ?,
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
	 * @param $includeIndexing boolean Whether or not to delete indexing info, true by default
	 */
	function deleteRecordById($recordId, $includeIndexing = true) {
		if ($includeIndexing) {
			$searchDao =& DAORegistry::getDAO('SearchDAO');
			$searchDao->deleteRecordObjects($recordId);
		}
		$this->deleteEntriesByRecordId($recordId);
		return $this->update(
			'DELETE FROM records WHERE record_id = ?', $recordId
		);
	}
	
	/**
	 * Retrieve all records in an archive.
	 * @param $archiveId int ID of archive to browse; null to return all.
	 * @param $sort array List of IDs to sort by; if archive specified, they're field IDs.
	 *                    Otherwise they are crosswalk IDs.
	 * @param $rangeInfo object optional
	 * @return DAOResultFactory containing matching records
	 */
	function &getRecords($archiveId = null, $sort = array(), $rangeInfo = null) {
		$params = array();

		$fieldDao =& DAORegistry::getDAO('FieldDAO');
		$crosswalkDao =& DAORegistry::getDAO('CrosswalkDAO');

		$sortJoins = '';
		$sortOrders = array();
		$i = 0;
		if ($archiveId) foreach ($sort as $id) {
			// Sort using the provided field IDs

			if (!($field =& $fieldDao->getFieldById($id))) continue;
			if (!($schemaPlugin =& $field->getSchemaPlugin())) continue;
			if (!in_array($field->getName(), $schemaPlugin->getSortFields())) continue;
			switch ($schemaPlugin->getFieldType($field->getName())) {
				case FIELD_TYPE_DATE:
					$sortJoins .= ' LEFT JOIN search_objects o' . $i . ' ON (o' . $i . '.record_id = r.record_id AND o' . $i . '.raw_field_id = ?)';
					$params[] = (int) $id;
					$sortOrders[] = 'o' . $i . '.object_time';
					break;
				case FIELD_TYPE_STRING:
				default:
					// $sortJoins .= ' LEFT JOIN entries e' . $i . ' ON (e' . $i . '.record_id = r.record_id AND e' . $i . '.raw_field_id = ?)';
					$sortJoins .= ' LEFT JOIN search_objects o' . $i . ' ON (o' . $i . '.record_id = r.record_id AND o' . $i . '.raw_field_id = ?) LEFT JOIN search_object_keywords ok' . $i . ' ON (ok' . $i . '.object_id = o' . $i . '.object_id AND ok' . $i . '.pos = 0) LEFT JOIN search_keyword_list k' . $i . ' ON (k' . $i . '.keyword_id = ok' . $i . '.keyword_id)';
					$params[] = (int) $id;
					// $sortOrders[] = 'e' . $i . '.value';
					$sortOrders[] = 'k' . $i . '.keyword_text';
					break;
			}
			
			$i++;
			unset($schemaPlugin);
			unset($field);
		} else foreach ($sort as $id) {
			// Sort using the provided crosswalk IDs
			if (!($crosswalk =& $crosswalkDao->getCrosswalkById($id))) continue;
			if (!$crosswalk->getSortable()) continue;
			switch ($crosswalk->getType()) {
				case FIELD_TYPE_DATE:
					$sortJoins .= ' LEFT JOIN search_objects o' . $i . ' ON (o' . $i . '.record_id = r.record_id) LEFT JOIN crosswalk_fields cf' . $i . ' ON (cf' . $i . '.raw_field_id = o' . $i . '.raw_field_id AND cf' . $i . '.crosswalk_id = ?)';
					$params[] = (int) $id;
					$sortOrders[] = 'o' . $i . '.object_time';
					break;
				case FIELD_TYPE_STRING:
				default:
					// $sortJoins .= ' LEFT JOIN entries e' . $i . ' ON (e' . $i . '.record_id = r.record_id) LEFT JOIN crosswalk_fields cf' . $i . ' ON (cf' . $i . '.raw_field_id = e' . $i . '.raw_field_id AND cf' . $i . '.crosswalk_id = ?)';
					$sortJoins .= ' LEFT JOIN (search_objects o' . $i . ', crosswalk_fields cf' . $i . ') ON (o' . $i . '.record_id = r.record_id AND o' . $i . '.raw_field_id = cf' . $i . '.raw_field_id AND cf' . $i . '.crosswalk_id = ?) LEFT JOIN search_object_keywords ok' . $i . ' ON (ok' . $i . '.object_id = o' . $i . '.object_id AND ok' . $i . '.pos = 0) LEFT JOIN search_keyword_list k' . $i . ' ON (k' . $i . '.keyword_id = ok' . $i . '.keyword_id)';
					$params[] = (int) $id;
					// $sortOrders[] = 'e' . $i . '.value';
					$sortOrders[] = 'k' . $i . '.keyword_text';
					break;
			}

			$i++;
			unset($crosswalk);
		}

		if (isset($archiveId)) $params[] = $archiveId;

		$result = &$this->retrieveRange(
			'SELECT DISTINCT r.* FROM records r' . $sortJoins .
			(isset($archiveId)? ' WHERE archive_id = ? ':'') .
			(empty($sortOrders)?'':' ORDER BY ' . join(', ', $sortOrders)),
			empty($params)?false:(count($params)==1?array_shift($params):$params),
			$rangeInfo
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
