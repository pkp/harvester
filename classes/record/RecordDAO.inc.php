<?php

/**
 * @file RecordDAO.inc.php
 *
 * Copyright (c) 2005-2007 Alec Smecher and John Willinsky
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
					'value' => $row['value'],
					'parent_entry_id' => $row['parent_entry_id']
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
		switch ($this->getDriver()) {
			case 'mysql':
				$this->update('DELETE FROM entry_attributes USING entries, entry_attributes WHERE entries.record_id = ? AND entry_attributes.entry_id = entries.entry_id', $recordId);
				break;
			default:
				$result = &$this->retrieve(
					'SELECT entry_id FROM entries WHERE record_id = ?',
					$recordId
				);
				while (!$result->EOF) {
					$row = &$result->getRowAssoc(false);
					$this->update('DELETE FROM entry_attributes WHERE entry_id = ?', $row['entry_id']);
					$this->update('DELETE FROM entries WHERE entry_id = ?', $row['entry_id']);
					$result->MoveNext();
				}
				$result->Close();
				unset($result);

				break;
		}
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
	 * @param $parentEntryId int optional
	 * @return int ID of inserted entry
	 */
	function insertEntry($recordId, $fieldId, $value, $attributes = array(), $parentEntryId = null) {
		$this->update('INSERT INTO entries (record_id, raw_field_id, value, parent_entry_id) VALUES (?, ?, ?, ?)', array($recordId, $fieldId, $value, $parentEntryId));
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
		switch ($this->getDriver()) {
			case 'mysql':
				// Count on missing referential integrity for
				// cases where records can be deleted.
				// Using one or two DELETE FROM ... WHERE ...
				// queries is too slow (at least for 5.0.15).
				$this->update('DELETE FROM records WHERE archive_id = ?', $archiveId);
				$this->update('DELETE entries FROM entries LEFT JOIN records ON (entries.record_id = records.record_id) WHERE records.record_id IS NULL');
				$this->update('DELETE search_objects FROM search_objects LEFT JOIN records ON (search_objects.record_id = records.record_id) WHERE records.record_id IS NULL');
				$this->update('DELETE entry_attributes FROM entry_attributes LEFT JOIN entries ON (entry_attributes.entry_id = entries.entry_id) WHERE entries.entry_id IS NULL');
				$this->update('DELETE search_object_keywords FROM search_object_keywords LEFT JOIN search_objects ON (search_object_keywords.object_id = search_objects.object_id) WHERE search_objects.object_id IS NULL');
				break;
			case 'postgres':
				$this->update('DELETE FROM search_object_keywords USING search_objects, records WHERE records.archive_id = ? AND records.record_id = search_objects.record_id AND search_object_keywords.object_id = search_objects.object_id', $archiveId);
				$this->update('DELETE FROM search_objects USING records WHERE records.archive_id = ? AND records.record_id = search_objects.record_id', $archiveId);
				$this->update('DELETE FROM entry_attributes USING entries, records WHERE records.archive_id = ? AND records.record_id = entries.record_id AND entry_attributes.entry_id = entries.entry_id', $archiveId);
				$this->update('DELETE FROM entries USING records WHERE records.archive_id = ? AND records.record_id = entries.record_id', $archiveId);
				$this->update('DELETE FROM records WHERE archive_id = ?', $archiveId);
				break;
			default:
				$records =& $this->getRecords($archiveId);
				while ($record =& $records->next()) {
					$this->deleteRecord($record);
					unset($record);
				}
				break;
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
	 * @param $sort array ID to sort by; if archive specified, use field ID;
	 *                    Otherwise use crosswalk ID.
	 * @param $rangeInfo object optional
	 * @return DAOResultFactory containing matching records
	 */
	function &getRecords($archiveId = null, $sort = null, $rangeInfo = null) {
		$params = array();

		$fieldDao =& DAORegistry::getDAO('FieldDAO');
		$crosswalkDao =& DAORegistry::getDAO('CrosswalkDAO');

		$sortJoin = '';
		$orderBy = '';
		$orderSelect = '';

		if ($sort !== null) {
			if ($archiveId !== null) {
				$field =& $fieldDao->getFieldById($sort);
				if (
					$field &&
					($schemaPlugin =& $field->getSchemaPlugin()) &&
					in_array($field->getName(), $schemaPlugin->getSortFields())
				) $fieldIds = array($sort);
				$isDate = $field->getType() == FIELD_TYPE_DATE;
				unset($field);
			} else {
				$crosswalk =& $crosswalkDao->getCrosswalkById($sort);
				$fieldIds = $crosswalkDao->getSortableFieldIds($sort);
				$isDate = $crosswalk && ($crosswalk->getType() == FIELD_TYPE_DATE);
			}

			if (!empty($fieldIds)) {
				if (!$isDate) {
					$sortJoin = ' LEFT JOIN entries e ON (e.record_id = r.record_id AND (e.raw_field_id = ?';
					$params[] = array_shift($fieldIds);
					foreach ($fieldIds as $fieldId) {
						$sortJoin .= ' OR e.raw_field_id = ?';
						$params[] = $fieldId;
					}
					$sortJoin .= '))';
					$orderBy = 'e.value';
					$orderSelect = 'e.value';
				} else {
					$sortJoin = ' LEFT JOIN search_objects o ON (o.record_id = r.record_id AND (o.raw_field_id = ?';
					$params[] = array_shift($fieldIds);
					foreach ($fieldIds as $fieldId) {
						$sortJoin .= ' OR o.raw_field_id = ?';
						$params[] = $fieldId;
					}
					$sortJoin .= '))';
					$orderBy = 'o.object_time DESC';
					$orderSelect = 'o.object_time';
				}
			}
		}

		if (isset($archiveId)) $params[] = $archiveId;

		$result = &$this->retrieveRange(
			'SELECT DISTINCT r.*' . (empty($orderSelect)?'':', ' . $orderSelect) . ' FROM records r' . $sortJoin .
			(isset($archiveId)? ' WHERE r.archive_id = ? ':'') .
			(empty($orderBy)?'':" ORDER BY $orderBy"),
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

	/**
	 * Enumerate a list of entry options for the given field and archive IDs
	 * @param $fieldId int
	 * @param $archiveIds array optional
	 */
	function getFieldOptions($fieldId, $archiveIds = null) {
		$sql = 'SELECT DISTINCT value FROM entries e, records r WHERE e.raw_field_id = ? AND r.record_id = e.record_id';
		$params = array($fieldId);
		if (!empty($archiveIds)) {
			$sql .= ' AND (r.archive_id = ?';
			$params[] = array_shift($archiveIds);
			foreach ($archiveIds as $archiveId) {
				$sql .= ' OR r.archive_id = ?';
				$params[] = (int) $archiveId;
			}
			$sql .= ')';
		}

		$result = &$this->retrieveCached($sql, $params, 60 * 60 * 24);

		$returner = array();
		while (!$result->EOF) {
			$row = &$result->getRowAssoc(false);
			$returner[] = $row['value'];
			$result->MoveNext();
		}
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Enumerate a list of entry options for the given field and crosswalk IDs
	 * @param $crosswalkId int
	 * @param $archiveIds array optional
	 */
	function getCrosswalkOptions($crosswalkId, $archiveIds = null) {
		$sql = 'SELECT DISTINCT e.value FROM crosswalk_fields cf, entries e, records r WHERE cf.crosswalk_id = ? AND cf.raw_field_id = e.raw_field_id AND r.record_id = e.record_id';
		$params = array($crosswalkId);
		if (is_array($archiveIds)) {
			$sql .= ' AND (r.archive_id = ?';
			$params[] = array_shift($archiveIds);
			foreach ($archiveIds as $archiveId) {
				$sql .= ' OR r.archive_id = ?';
				$params[] = $archiveId;
			}
			$sql .= ')';
		}

		$result = &$this->retrieveCached($sql, $params, 60 * 60 * 24);

		$returner = array();
		while (!$result->EOF) {
			$row = &$result->getRowAssoc(false);
			$returner[] = $row['value'];
			$result->MoveNext();
		}
		$result->Close();
		unset($result);

		return $returner;
	}

}

?>
