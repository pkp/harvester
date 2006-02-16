<?php

/**
 * IndexerDAO.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package indexer
 *
 * Class for Indexer DAO.
 * Operations for retrieving and modifying Indexer objects.
 *
 * $Id$
 */

import ('indexer.Indexer');

class IndexerDAO extends DAO {
	/**
	 * Constructor.
	 */
	function IndexerDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve an indexer by ID.
	 * @param $indexerId int
	 * @return Indexer
	 */
	function &getIndexerById($indexerId) {

		$result = &$this->retrieve(
			'SELECT * FROM indexers WHERE indexer_id = ?', $indexerId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnIndexerFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);

		return $returner;
	}
	
	/**
	 * Internal function to return a Indexer object from a row.
	 * @param $row array
	 * @return Indexer
	 */
	function &_returnIndexerFromRow(&$row) {
		$indexer = &new Indexer();
		$indexer->setIndexerId($row['indexer_id']);
		$indexer->setIndexerPluginName($row['indexer_plugin']);
		$indexer->setSeq($row['seq']);
		
		HookRegistry::call('IndexerDAO::_returnIndexerFromRow', array(&$indexer, &$row));

		return $indexer;
	}

	/**
	 * Insert a new indexer.
	 * @param $indexer Indexer
	 */	
	function insertIndexer(&$indexer) {
		$this->update(
			'INSERT INTO indexers
				(indexer_plugin, seq)
				VALUES
				(?, ?)',
			array(
				$indexer->getIndexerPluginName(),
				$indexer->getSeq()
			)
		);
		
		$indexer->setIndexerId($this->getInsertIndexerId());
		return $indexer->getIndexerId();
	}

	/**
	 * Sequentially renumber indexers in their sequence order.
	 */
	function resequenceIndexers() {
		$result = &$this->retrieve(
			'SELECT indexer_id FROM indexers ORDER BY seq'
		);
		
		for ($i=1; !$result->EOF; $i++) {
			list($indexerId) = $result->fields;
			$this->update(
				'UPDATE indexers SET seq = ? WHERE indexer_id = ?',
				array(
					$i,
					$indexerId
				)
			);
			
			$result->moveNext();
		}

		$result->close();
		unset($result);
	}

	/**
	 * Update an existing indexer.
	 * @param $indexer Indexer
	 */
	function updateIndexer(&$indexer) {
		return $this->update(
			'UPDATE indexers
				SET
					indexer_plugin = ?,
					seq = ?
				WHERE indexer_id = ?',
			array(
				$indexer->getIndexerPluginName(),
				$indexer->getSeq(),
				$indexer->getIndexerId()
			)
		);
	}
	
	/**
	 * Delete an indexer, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $indexer Indexer
	 */
	function deleteIndexer(&$indexer) {
		return $this->deleteIndexerById($indexer->getIndexerId());
	}
	
	/**
	 * Delete an indexer by ID, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $indexerId int
	 */
	function deleteIndexerById($indexerId) {
		return $this->update(
			'DELETE FROM indexers WHERE indexer_id = ?', $indexerId
		);
	}
	
	/**
	 * Retrieve all indexers for a searchable field.
	 * @return DAOResultFactory containing matching searchable fields
	 */
	function &getIndexersBySearchableFieldId($searchableFieldId, $rangeInfo = null) {
		$result = &$this->retrieveRange(
			'SELECT * FROM indexers WHERE searchable_field_id = ? ORDER BY seq',
			$searchableFieldId, $rangeInfo
		);

		$returner = &new DAOResultFactory($result, $this, '_returnIndexerFromRow');
		return $returner;
	}
	
	/**
	 * Get the ID of the last inserted indexer.
	 * @return int
	 */
	function getInsertIndexerId() {
		return $this->getInsertId('indexers', 'indexer_id');
	}
	
}

?>
