<?php

/**
 * SearchDAO.inc.php
 *
 * Copyright (c) 2004-2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package search
 *
 * DAO class for search index.
 *
 * $Id$
 */

import('search.Search');

class SearchDAO extends DAO {

	/**
	 * Constructor.
	 */
	function SearchDAO() {
		parent::DAO();
	}
	
	/**
	 * Add a word to the keyword list (if it doesn't already exist).
	 * @param $keyword string
	 * @return int the keyword ID
	 */
	function insertKeyword($keyword) {
		static $searchKeywordIds = array();
		if (isset($searchKeywordIds[$keyword])) return $searchKeywordIds[$keyword];
		$result = &$this->retrieve(
			'SELECT keyword_id FROM search_keyword_list WHERE keyword_text = ?',
			$keyword
		);
		if($result->RecordCount() == 0) {
			$this->update(
				'INSERT INTO search_keyword_list (keyword_text) VALUES (?)',
				$keyword
			);
			$keywordId = $this->getInsertId('search_keyword_list', 'keyword_id');
			
		} else {
			$keywordId = $result->fields[0];
		}
		
		$searchKeywordIds[$keyword] = $keywordId;

		$result->Close();
		unset($result);
		
		return $keywordId;
	}
	
	/**
	 * Retrieve the top results for a phrases with the given
	 * limit (default 500 results).
	 * @param $phrase string
	 * @param $archiveIds array of IDs
	 * @param $type string,
	 * @param $id int,
	 * @param $limit int optional
	 * @param $cacheHours int optional
	 * @return array of results (associative arrays)
	 */
	function &getPhraseResults($phrase, $dates, $archiveIds, $type, $id, $limit = 500, $cacheHours = 24) {
		if (empty($phrase)) {
			$results = false;
			$returner = &new DBRowIterator($results);
			return $returner;
		}

		$sqlFrom = '';
		$sqlWhere = '';
		
		if (is_array($phrase)) for ($i = 0, $count = count($phrase); $i < $count; $i++) {
			if (!empty($sqlFrom)) {
				$sqlFrom .= ', ';
				$sqlWhere .= ' AND ';
			}

			$sqlFrom .= 'search_object_keywords o'.$i.' NATURAL JOIN search_keyword_list k'.$i;
			if (strstr($phrase[$i], '%') === false) $sqlWhere .= 'k'.$i.'.keyword_text = ?';
			else $sqlWhere .= 'k'.$i.'.keyword_text LIKE ?';
			if ($i > 0) $sqlWhere .= ' AND o0.object_id = o'.$i.'.object_id AND o0.pos+'.$i.' = o'.$i.'.pos';
			$params[] = $phrase[$i];

		}

		switch ($type) {
			case 'field':
				$sqlWhere .= ' AND o.raw_field_id = ?';
				$params[] = $id;
				break;
			case 'crosswalk':
				$sqlFrom .= ', crosswalk_fields cf';
				$sqlWhere .= ' AND cf.raw_field_id = o.raw_field_id AND cf.crosswalk_id = ?';
				$params[] = $id;
				break;
			case 'all':
				break;
			default:
				fatalError("Unknown search condition type \"$type!\"");
		}

		// Add the date restrictions to the query
		$i=0;
		foreach ($dates as $dateType => $typeEntry) {
			foreach ($typeEntry as $id => $entry) {
				if (!empty($entry[0]) || !empty($entry[1])) {
					$sqlFrom .= ", search_objects do$i";
					$sqlWhere .= ' AND o.record_id = do' . $i . '.record_id';
					if (!empty($entry[0])) $sqlWhere .= " AND do$i.object_time >= " . $this->dateToDB($entry[0]);
					if (!empty($entry[1])) $sqlWhere .= " AND do$i.object_time <= " . $this->dateToDB($entry[1]);
					switch ($dateType) {
						case 'field':
							$sqlWhere .= " AND do$i.raw_field_id = ?";
							$params[] = $id;
							break;
						case 'crosswalk':
							$sqlFrom .= ", crosswalk_fields dcf$i";
							$sqlWhere .= ' AND dcf' . $i . '.raw_field_id = do' . $i . '.raw_field_id AND dcf' . $i . '.crosswalk_id = ?';
							$params[] = $id;
							break;
						default:
							fatalError("Unknown date restriction type \"$dateType\"!");
					}
					$i++;
				}
			}
		}
		$archiveLimitSql = '';
		if (is_array($archiveIds)) foreach ($archiveIds as $archiveId) {
			if ($archiveId == (int) $archiveId) {
				if (empty($archiveLimitSql)) {
					$archiveLimitSql .= ' AND (';
				} else {
					$archiveLimitSql .= ' OR ';
				}
				$archiveLimitSql .= 'r.archive_id = ?';
				$params[] = $archiveId;
			}
		}
		if (!empty($archiveLimitSql)) {
			$archiveLimitSql .= ')';
		}

		$result = &$this->retrieveCached(
			'SELECT
				o.record_id AS record_id,
				o.raw_field_id AS raw_field_id,
				COUNT(*) AS count
			FROM
				records r, search_objects o NATURAL JOIN ' . $sqlFrom . '
			WHERE ' . $sqlWhere . $archiveLimitSql . '
				AND r.record_id = o.record_id
			GROUP BY o.record_id
			ORDER BY count DESC
			LIMIT ' . $limit,
			$params,
			3600 * $cacheHours // Cache for 24 hours
		);

		$returner = &new DBRowIterator($result);
		return $returner;
	}
	
	/**
	 * Delete all search objects for a record.
	 * @param $recordId int
	 * @param $fieldId int optional
	 */
	function deleteRecordObjects($recordId, $fieldId = null) {
		$sql = 'SELECT object_id FROM search_objects WHERE record_id = ?' . ($fieldId?' AND raw_field_id = ?':'');
		$result = &$this->retrieve(
			$sql,
			$fieldId?
				array($recordId,$fieldId):
				$recordId
		);
		while (!$result->EOF) {
			$objectId = $result->fields[0];
			$this->update('DELETE FROM search_object_keywords WHERE object_id = ?', $objectId);
			$this->update('DELETE FROM search_objects WHERE object_id = ?', $objectId);
			$result->MoveNext();
		}
		$result->Close();
		unset($result);
	}
	
	/**
	 * Add a record object to the index (if already exists, indexed keywords are cleared).
	 * Each search object can contain a single date entry; this is an optimization to
	 * prevent having a separate indexed table to contain dates. If a date is specified
	 * here, the object will be guaranteed to have that date regardless of whether it was
	 * created or already existed.
	 * @param $recordId int
	 * @param $fieldId int
	 * @param $date string optional
	 * @return int the object ID
	 */
	function insertObject($recordId, $fieldId, $date = null) {
		$result = &$this->retrieve(
			'SELECT object_id FROM search_objects WHERE record_id = ? AND raw_field_id = ?', array($recordId, $fieldId));
		if ($result->RecordCount() == 0) {
			$this->update(
				'INSERT INTO search_objects (record_id, raw_field_id, object_time) VALUES (?, ?, ' . $this->dateToDB($date) . ')',
				array(
					$recordId,
					$fieldId
				)
			);
			$objectId = $this->getInsertId('search_objects', 'object_id');
			
		} else {
			$objectId = $result->fields[0];
			$this->update(
				'DELETE FROM search_object_keywords WHERE object_id = ?',
				$objectId
			);
			// Make sure the date is accurate
			$this->update(
				'UPDATE search_objects SET object_time = ' . $this->dateToDB($date) . ' WHERE object_id = ?',
				$objectId
			);
		}
		$result->Close();
		unset($result);
		
		return $objectId;
	}
	
	/**
	 * Index an occurrence of a keyword in an object.
	 * @param $objectId int
	 * @param $keyword string
	 * @param $position int
	 * @return $keyword
	 */
	function insertObjectKeyword($objectId, $keyword, $position) {
		// FIXME Cache recently retrieved keywords?
		$keywordId = $this->insertKeyword($keyword);
		$this->update(
			'INSERT INTO search_object_keywords (object_id, keyword_id, pos) VALUES (?, ?, ?)',
			array($objectId, $keywordId, $position)
		);
	}
}

?>
