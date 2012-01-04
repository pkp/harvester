<?php

/**
 * @defgroup sort_order
 */
 
/**
 * @file classes/sortOrder/SortOrder.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SortOrder
 * @ingroup sort_order
 * @see SortOrderDAO
 *
 * @brief Basic class describing a sorting order.
 */

// $Id$


define('SORT_ORDER_TYPE_STRING',	0x00000001);
define('SORT_ORDER_TYPE_NUMBER',	0x00000002);
define('SORT_ORDER_TYPE_DATE',		0x00000003);

class SortOrder extends DataObject {
	//
	// Get/set methods
	//

	/**
	 * Get the ID of the sort order.
	 * @return int
	 */
	function getSortOrderId() {
		return $this->getData('sortOrderId');
	}

	/**
	 * Set the ID of the sort order.
	 * @param $sortOrderId int
	 */
	function setSortOrderId($sortOrderId) {
		return $this->setData('sortOrderId', $sortOrderId);
	}

	/**
	 * Get the sort order type.
	 * @return int
	 */
	function getType() {
		return $this->getData('type');
	}

	/**
	 * Set the sort order type.
	 * @param $type int
	 */
	function setType($type) {
		return $this->setData('type', $type);
	}

	/**
	 * Get the name of the sort order type
	 */
	function getSortOrderTypeName() {
		$options = SortOrder::getTypeOptions();
		return __($options[$this->getType()]);
	}

	/**
	 * Get the current set of allowed sort order type options as an
	 * associative array sortOrderType => localeKeyName
	 * @return array
	 */
	function getTypeOptions() {
		return array(
			SORT_ORDER_TYPE_STRING => 'admin.sortOrders.type.string',
			SORT_ORDER_TYPE_NUMBER => 'admin.sortOrders.type.number',
			SORT_ORDER_TYPE_DATE => 'admin.sortOrders.type.date',
		);
	}

	/**
	 * Get the "clean" flag for the sort order.
	 * @return boolean
	 */
	function getIsClean() {
		return $this->getData('isClean');
	}

	/**
	 * Set the "clean" flag for the sort order.
	 * @param $isClean boolean
	 */
	function setIsClean($isClean) {
		return $this->setData('isClean', $isClean);
	}

	/**
	 * Get the name of the sort order.
	 * @return string
	 */
	function getSortOrderName() {
		return $this->getLocalizedData('name');
	}

	/**
	 * Get the sort order name.
	 * @param $locale string
	 * @return string
	 */
	function getName($locale) {
		return $this->getData('name', $locale);
	}

	/**
	 * Set the sort order name.
	 * @param $name string
	 * @param $locale string
	 */
	function setName($name, $locale) {
		return $this->setData('name', $name);
	}
}

?>
