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
	 * @return array of results (associative arrays)
	 */
	function &getPhraseResults($phrase, $limit = 500, $cacheHours = 24) {
		if (empty($phrase)) {
			$results = false;
			$returner = &new DBRowIterator($results);
			return $returner;
		}
		
		$sqlFrom = '';
		$sqlWhere = '';
		
		for ($i = 0, $count = count($phrase); $i < $count; $i++) {
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

		$result = &$this->retrieveCached(
			'SELECT
				o.record_id AS record_id,
				o.raw_field_id AS raw_field_id,
				COUNT(*) AS count
			FROM
				search_objects o NATURAL JOIN ' . $sqlFrom . '
			WHERE ' . $sqlWhere . '
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
	 * @param $recordId int
	 * @param $fieldId int
	 * @return int the object ID
	 */
	function insertObject($recordId, $fieldId) {
		$result = &$this->retrieve(
			'SELECT object_id FROM search_objects WHERE record_id = ? AND raw_field_id = ?', array($recordId, $fieldId));
		if ($result->RecordCount() == 0) {
			$this->update(
				'INSERT INTO search_objects (record_id, raw_field_id) VALUES (?, ?)',
				array($recordId, $fieldId)
			);
			$objectId = $this->getInsertId('search_objects', 'object_id');
			
		} else {
			$objectId = $result->fields[0];
			$this->update(
				'DELETE FROM search_object_keywords WHERE object_id = ?',
				$objectId
			);
		}
		$result->Close();
		unset($result);
		
		return $objectId;
	}
	
	/**
	 * Index an occurrence of a keyword in an object.s
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
