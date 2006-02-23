<?php

/**
 * CrosswalkDAO.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package field
 *
 * Class for Crosswalk DAO.
 * Operations for retrieving and modifying Crosswalk objects.
 *
 * $Id$
 */

import ('field.Crosswalk');

class CrosswalkDAO extends DAO {
	/**
	 * Constructor.
	 */
	function CrosswalkDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve a crosswalk by ID.
	 * @param $crosswalkId int
	 * @return Crosswalk
	 */
	function &getCrosswalkById($crosswalkId) {

		$result = &$this->retrieve(
			'SELECT * FROM crosswalks WHERE crosswalk_id = ?', $crosswalkId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnCrosswalkFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);

		return $returner;
	}
	
	/**
	 * Internal function to return a Crosswalk object from a row.
	 * @param $row array
	 * @return Crosswalk
	 */
	function &_returnCrosswalkFromRow(&$row) {
		$crosswalk = &new Crosswalk();
		$crosswalk->setCrosswalkId($row['crosswalk_id']);
		$crosswalk->setName($row['name']);
		$crosswalk->setDescription($row['description']);
		$crosswalk->setSeq($row['seq']);
		
		HookRegistry::call('CrosswalkDAO::_returnCrosswalkFromRow', array(&$crosswalk, &$row));

		return $crosswalk;
	}

	/**
	 * Insert a new crosswalk.
	 * @param $crosswalk Crosswalk
	 */	
	function insertCrosswalk(&$crosswalk) {
		$this->update(
			'INSERT INTO crosswalks
				(name, description, seq)
				VALUES
				(?, ?, ?)',
			array(
				$crosswalk->getName(),
				$crosswalk->getDescription(),
				$crosswalk->getSeq()
			)
		);
		
		$crosswalk->setCrosswalkId($this->getInsertCrosswalkId());
		return $crosswalk->getCrosswalkId();
	}

	/**
	 * Sequentially renumber crosswalks in their sequence order.
	 */
	function resequenceCrosswalks() {
		$result = &$this->retrieve(
			'SELECT crosswalk_id FROM crosswalks ORDER BY seq'
		);
		
		for ($i=1; !$result->EOF; $i++) {
			list($crosswalkId) = $result->fields;
			$this->update(
				'UPDATE crosswalks SET seq = ? WHERE crosswalk_id = ?',
				array(
					$i,
					$crosswalkId
				)
			);
			
			$result->moveNext();
		}

		$result->close();
		unset($result);
	}

	/**
	 * Update an existing field.
	 * @param $crosswalk Crosswalk
	 */
	function updateCrosswalk(&$crosswalk) {
		return $this->update(
			'UPDATE crosswalks
				SET
					name = ?,
					description = ?,
					seq = ?
				WHERE crosswalk_id = ?',
			array(
				$crosswalk->getName(),
				$crosswalk->getDescription(),
				$crosswalk->getSeq(),
				$crosswalk->getCrosswalkId()
			)
		);
	}
	
	/**
	 * Delete a crosswalk, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $crosswalk Crosswalk
	 */
	function deleteCrosswalk(&$crosswalk) {
		return $this->deleteCrosswalkById($crosswalk->getCrosswalkId());
	}
	
	/**
	 * Delete a crosswalk by ID, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $crosswalkId int
	 */
	function deleteCrosswalkById($crosswalkId) {
		$this->deleteCrosswalkFieldsByCrosswalkId($crosswalkId);
		return $this->update(
			'DELETE FROM crosswalks WHERE crosswalk_id = ?', $crosswalkId
		);
	}
	
	/**
	 * Retrieve all crosswalks.
	 * @return DAOResultFactory containing matching crosswalks
	 */
	function &getCrosswalks($rangeInfo = null) {
		$result = &$this->retrieveRange(
			'SELECT * FROM crosswalks ORDER BY seq',
			false, $rangeInfo
		);

		$returner = &new DAOResultFactory($result, $this, '_returnCrosswalkFromRow');
		return $returner;
	}
	
	/**
	 * Get the ID of the last inserted crosswalk.
	 * @return int
	 */
	function getInsertCrosswalkId() {
		return $this->getInsertId('crosswalks', 'crosswalk_id');
	}
	
	/**
	 * Retrieve all field IDs for a crosswalk.
	 * @return DAOResultFactory containing matching crosswalks
	 */
	function &getFieldsByCrosswalkId($crosswalkId, $rangeInfo = null) {
		$fieldDao =& DAORegistry::getDAO('FieldDAO');

		$result = &$this->retrieveRange(
			'SELECT f.* FROM raw_fields f, crosswalk_fields c WHERE f.raw_field_id = c.raw_field_id AND c.crosswalk_id = ?',
			$crosswalkId, $rangeInfo
		);

		$returner = &new DAOResultFactory($result, $fieldDao, '_returnFieldFromRow');
		return $returner;
	}
	
	/**
	 * Delete crosswalk fields by crosswalk ID, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $crosswalkId int
	 */
	function deleteCrosswalkFieldsByCrosswalkId($crosswalkId) {
		return $this->update(
			'DELETE FROM crosswalk_fields WHERE crosswalk_id = ?', $crosswalkId
		);
	}
	
	/**
	 * Delete crosswalk fields by crosswalk ID, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $crosswalkId int
	 */
	function insertCrosswalkField($crosswalkId, $fieldId) {
		return $this->update(
			'INSERT INTO crosswalk_fields(crosswalk_id, raw_field_id) VALUES (?, ?)', array($crosswalkId, $fieldId)
		);
	}
	
}

?>
