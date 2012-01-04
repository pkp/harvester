<?php

/**
 * @file classes/sortOrder/SortOrderDAO.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SortOrderDAO
 * @ingroup sort_order
 * @see SortOrder
 *
 * @brief Operations for retrieving and modifying SortOrder objects.
 */

// $Id$


import('classes.sortOrder.SortOrder');

class SortOrderDAO extends DAO {
	/**
	 * Retrieve a sort order by ID.
	 * @param $sortOrderId int
	 * @return SortOrder
	 */
	function &getSortOrder($sortOrderId) {
		$result =& $this->retrieve(
			'SELECT * FROM sort_orders WHERE sort_order_id = ?', $sortOrderId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnSortOrderFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve an iterator of sort orders.
	 * @param $rangeInfo object Optional range to fetch
	 * @return object DAOResultFactory containing matching sort orders
	 */
	function &getSortOrders($rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT * FROM sort_orders ORDER BY sort_order_id ASC',
			array(),
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnSortOrderFromRow');
		return $returner;
	}

	/**
	 * Get the list of localized field names for this table
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('name');
	}

	/**
	 * Internal function to return a sort order object from a row.
	 * @param $row array
	 * @return SortOrder
	 */
	function &_returnSortOrderFromRow(&$row) {
		$sortOrder = new SortOrder();
		$sortOrder->setSortOrderId($row['sort_order_id']);
		$sortOrder->setType($row['order_type']);
		$sortOrder->setIsClean($row['is_clean']);

		$this->getDataObjectSettings('sort_order_settings', 'sort_order_id', $row['sort_order_id'], $sortOrder);

		return $sortOrder;
	}

	/**
	 * Update the settings for this object
	 * @param $sortOrder object
	 */
	function updateLocaleFields(&$sortOrder) {
		$this->updateDataObjectSettings('sort_order_settings', $sortOrder, array(
			'sort_order_id' => $sortOrder->getSortOrderId()
		));
	}

	/**
	 * Insert a new SortOrder.
	 * @param $sortOrder SortOrder
	 * @return int 
	 */
	function insertSortOrder(&$sortOrder) {
		$this->update(
			'INSERT INTO sort_orders (order_type, is_clean) VALUES (?, ?)',
			array(
				(int) $sortOrder->getType(),
				$sortOrder->getIsClean()?1:0
			)
		);
		$sortOrder->setSortOrderId($this->getInsertSortOrderId());
		$this->updateLocaleFields($sortOrder);
		return $sortOrder->getSortOrderId();
	}

	/**
	 * Update an existing sort order.
	 * @param $sortOrder SortOrder
	 * @return boolean
	 */
	function updateSortOrder(&$sortOrder) {
		$returner = $this->update(
			'UPDATE	sort_orders
			SET	order_type = ?,
				is_clean = ?
			WHERE	sort_order_id = ?',
			array(
				(int) $sortOrder->getType(),
				$sortOrder->getIsClean()?1:0,
				(int) $sortOrder->getSortOrderId()
			)
		);
		$this->updateLocaleFields($sortOrder);
		return $returner;
	}

	/**
	 * Delete a sort order.
	 * @param $sortOrder SortOrder
	 * @return boolean
	 */
	function deleteSortOrder($sortOrder) {
		return $this->deleteSortOrderById($sortOrder->getSortOrderId());
	}

	/**
	 * Set the fields associated with a sort order.
	 * @param $sortOrderId int
	 * @param $fieldIds array Array of integers representing field IDs
	 */
	function setSortOrderFields($sortOrderId, $fieldIds) {
		$this->deleteSortOrderFieldsBySortOrder($sortOrderId);

		$fieldDao =& DAORegistry::getDAO('FieldDAO');
		foreach ($fieldIds as $fieldId) {
			$field =& $fieldDao->getFieldById($fieldId);
			$this->insertSortOrderField($sortOrderId, $field->getSchemaId(), $fieldId);
			unset($field);
		}
	}

	/**
	 * Delete a sort order's field associations by sort order ID.
	 * @param $sortOrderId int
	 * @return int
	 */
	function deleteSortOrderFieldsBySortOrder($sortOrderId) {
		return $this->update(
			'DELETE FROM sort_order_fields WHERE sort_order_id = ?',
			array((int) $sortOrderId)
		);
	}

	function flushRecordIndex($recordId) {
		$count = $this->update(
			'DELETE FROM sort_order_strings WHERE record_id = ?',
			array((int) $recordId)
		);
		$count += $this->update(
			'DELETE FROM sort_order_numbers WHERE record_id = ?',
			array((int) $recordId)
		);
		$count += $this->update(
			'DELETE FROM sort_order_dates WHERE record_id = ?',
			array((int) $recordId)
		);
		return $count;
	}

	/**
	 * Flush the index for a given sort order.
	 * @return int Number of affected index entries
	 */
	function flushSortOrderIndex($sortOrderId) {
		$count = $this->update(
			'DELETE FROM sort_order_strings WHERE sort_order_id = ?',
			array((int) $sortOrderId)
		);
		$count += $this->update(
			'DELETE FROM sort_order_numbers WHERE sort_order_id = ?',
			array((int) $sortOrderId)
		);
		$count += $this->update(
			'DELETE FROM sort_order_dates WHERE sort_order_id = ?',
			array((int) $sortOrderId)
		);
		return $count;
	}

	/**
	 * Delete a sort order by ID.
	 * @param $sortOrderId int
	 * @return boolean
	 */
	function deleteSortOrderById($sortOrderId) {
		$this->deleteSortOrderFieldsBySortOrder($sortOrderId);
		$this->flushSortOrderIndex($sortOrderId);
		$this->update(
			'DELETE FROM sort_order_settings WHERE sort_order_id = ?',
			array((int) $sortOrderId)
		);
		return $this->update(
			'DELETE FROM sort_orders WHERE sort_order_id = ?',
			array((int) $sortOrderId)
		);
	}

	/**
	 * Flush the entire index.
	 */
	function flush() {
		$this->update('DELETE FROM sort_order_strings');
		$this->update('DELETE FROM sort_order_numbers');
		$this->update('DELETE FROM sort_order_dates');
	}

	/**
	 * Insert a field/sort order association.
	 * @param $sortOrderId int
	 * @param $schemaPluginId int
	 * @param $fieldId int
	 */
	function insertSortOrderField($sortOrderId, $schemaPluginId, $fieldId) {
		return $this->update(
			'INSERT INTO sort_order_fields
				(sort_order_id, raw_field_id, schema_plugin_id)
			VALUES
				(?, ?, ?)',
			array(
				(int) $sortOrderId,
				(int) $fieldId,
				(int) $schemaPluginId
			)
		);
	}

	/**
	 * Get the ID of the last inserted sort order.
	 * @return int
	 */
	function getInsertSortOrderId() {
		return $this->getInsertId('sort_orders', 'sort_order_id');
	}

	/**
	 * Index a number for sorting.
	 * @param $recordId int
	 * @param $sortOrderId int
	 * @param $value int
	 */
	function insertNumber($recordId, $sortOrderId, $value) {
		return $this->update(
			'INSERT INTO sort_order_numbers
				(record_id, sort_order_id, value)
			VALUES
				(?, ?, ?)',
			array(
				(int) $recordId,
				(int) $sortOrderId,
				(int) $value
			)
		);
	}

	/**
	 * Index a string for sorting.
	 * @param $recordId int
	 * @param $sortOrderId int
	 * @param $value string
	 */
	function insertString($recordId, $sortOrderId, $value) {
		return $this->update(
			'INSERT INTO sort_order_strings
				(record_id, sort_order_id, value)
			VALUES
				(?, ?, ?)',
			array(
				(int) $recordId,
				(int) $sortOrderId,
				$value
			)
		);
	}

	/**
	 * Index a date for sorting.
	 * @param $recordId int
	 * @param $sortOrderId int
	 * @param $value date
	 */
	function insertDate($recordId, $sortOrderId, $value) {
		return $this->update(
			sprintf(
				'INSERT INTO sort_order_dates
					(record_id, sort_order_id, value)
				VALUES
					(?, ?, %s)',
				$this->datetimeToDB($value)
			),
			array(
				(int) $recordId,
				(int) $sortOrderId
			)
		);
	}
}

?>
