<?php

/**
 * @file SearchDAO.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package search
 * @class SearchDAO
 *
 * DAO class for search index.
 *
 */

// $Id$


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
		$result =& $this->retrieve(
			'SELECT keyword_id FROM search_keyword_list WHERE keyword_text = ?',
			$keyword
		);
		if($result->RecordCount() == 0) {
			if ($this->update(
				'INSERT INTO search_keyword_list (keyword_text) VALUES (?)',
				$keyword,
				true,
				false // Do not die on error
			)) {
				$keywordId = $this->getInsertId('search_keyword_list', 'keyword_id');
			} else {
				$keywordId = null; // Error with this keyword (see #2324)
			}

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
			import('lib.pkp.classes.db.DBRowIterator');
			$returner = new DBRowIterator($results);
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

		$result =& $this->retrieveCached(
			'SELECT
				o.record_id AS record_id,
				MAX(o.raw_field_id) AS raw_field_id,
				COUNT(*) AS count
			FROM
				records r LEFT JOIN archives a ON (r.archive_id = a.archive_id), search_objects o NATURAL JOIN ' . $sqlFrom . '
			WHERE ' . $sqlWhere . $archiveLimitSql . '
				AND r.record_id = o.record_id AND a.enabled = 1
			GROUP BY o.record_id
			ORDER BY count DESC
			LIMIT ' . $limit,
			$params,
			3600 * $cacheHours // Cache for 24 hours
		);

		import('lib.pkp.classes.db.DBRowIterator');
		$returner = new DBRowIterator($result);
		return $returner;
	}

	/**
	 * Delete all search objects for a record.
	 * @param $recordId int
	 * @param $fieldId int optional
	 */
	function deleteRecordObjects($recordId, $fieldId = null) {
		switch ($this->getDriver()) {
			case 'mysql':
				$this->update(
					'DELETE search_objects, search_object_keywords FROM search_objects LEFT JOIN search_object_keywords ON (search_objects.object_id = search_object_keywords.object_id) WHERE search_objects.record_id = ?' . ($fieldId?' AND search_objects.raw_field_id = ?':''),
					$fieldId?array($recordId, $fieldId):$recordId
				);
				break;
			default:
				$result =& $this->retrieve(
					'SELECT object_id FROM search_objects WHERE record_id = ?' . ($fieldId?' AND raw_field_id = ?':''),
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
				break;
		}
	}

	/**
	 * Add a record object to the index (if already exists, indexed keywords are cleared).
	 * Each search object can contain a single date entry; this is an optimization to
	 * prevent having a separate indexed table to contain dates. If a date is specified
	 * here, the object will be guaranteed to have that date regardless of whether it was
	 * created or already existed.
	 * @param $recordId int
	 * @param $fieldId int
	 * @param $pos int Reference to int to receive the starting position for insert
	 * @param $date string optional
	 * @param $flush boolean Whether or not to flush values of an existing object
	 * @return int the object ID
	 */
	function insertObject($recordId, $fieldId, &$pos, $date = null, $flush = true) {
		$result =& $this->retrieve(
			'SELECT o.object_id, COALESCE(max(k.pos), 0) AS pos FROM search_objects o LEFT JOIN search_object_keywords k ON (k.object_id = o.object_id) WHERE o.record_id = ? AND o.raw_field_id = ? GROUP BY o.object_id', array($recordId, $fieldId));
		if ($result->RecordCount() == 0) {
			$this->update(
				'INSERT INTO search_objects (record_id, raw_field_id, object_time) VALUES (?, ?, ' . $this->dateToDB($date) . ')',
				array(
					$recordId,
					$fieldId
				)
			);
			$objectId = $this->getInsertId('search_objects', 'object_id');
			$pos = 0;
		} else {
			$objectId = $result->fields[0];
			if ($flush) {
				$this->update(
					'DELETE FROM search_object_keywords WHERE object_id = ?',
					$objectId
				);
				$pos = 0;
			} else {
				// Start positions after a gap after the last entry
				$pos = $result->fields[1] + 2;
			}

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
	 * @return $keywordId
	 */
	function insertObjectKeyword($objectId, $keyword, $position) {
		$keywordId = $this->insertKeyword($keyword);
		if ($keywordId === null) return null; // Bug #2324
		$this->update(
			'INSERT INTO search_object_keywords (object_id, keyword_id, pos) VALUES (?, ?, ?)',
			array($objectId, $keywordId, $position)
		);
		return $keywordId;
	}

	function flushIndex() {
		$this->update('DELETE FROM search_object_keywords');
		$this->update('DELETE FROM search_objects');
		$this->update('DELETE FROM search_keyword_list');
	}
}

?>
