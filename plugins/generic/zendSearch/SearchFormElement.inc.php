<?php

/**
 * @file SearchFormElement.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_generic_zendSearch
 * @class SearchFormElement
 *
 * @brief SearchFormElement class.
 * Describes basic searchFormElement properties.
 *
 */

// $Id$


define('SEARCH_FORM_ELEMENT_TYPE_STRING',	0x00000001);
define('SEARCH_FORM_ELEMENT_TYPE_SELECT',	0x00000002);
define('SEARCH_FORM_ELEMENT_TYPE_DATE',		0x00000004);

class SearchFormElement extends DataObject {
	/**
	 * Constructor.
	 */
	function SearchFormElement() {
		parent::DataObject();
	}

	//
	// Get/set methods
	//

	/**
	 * Get ID of searchFormElement.
	 * @return int
	 */
	function getSearchFormElementId() {
		return $this->getData('searchFormElementId');
	}

	/**
	 * Set ID of searchFormElement.
	 * @param $searchFormElementId int
	 */
	function setSearchFormElementId($searchFormElementId) {
		return $this->setData('searchFormElementId', $searchFormElementId);
	}

	/**
	 * Get the name of the form element.
	 * @return string
	 */
	function getSearchFormElementTitle() {
		return $this->getLocalizedData('title');
	}

	/**
	 * Get the form element name.
	 * @param $locale string
	 * @return string
	 */
	function getTitle($locale) {
		return $this->getData('title', $locale);
	}

	/**
	 * Set the form element name.
	 * @param $title string
	 * @param $locale string
	 */
	function setTitle($title, $locale) {
		return $this->setData('title', $title);
	}

	function &getTypeMap() {
		static $typeMap;
		if (!isset($typeMap)) $typeMap = array(
			SEARCH_FORM_ELEMENT_TYPE_STRING => 'plugins.generic.zendSearch.formElement.type.string',
			SEARCH_FORM_ELEMENT_TYPE_SELECT => 'plugins.generic.zendSearch.formElement.type.select',
			SEARCH_FORM_ELEMENT_TYPE_DATE => 'plugins.generic.zendSearch.formElement.type.date',

		);
		return $typeMap;
	}

	function getTypeName() {
		$typeMap =& $this->getTypeMap();
		return $typeMap[$this->getType()];
	}

	/**
	 * Get type
	 * @return int SEARCH_FORM_ELEMENT_TYPE_...
	 */
	function getType() {
		return $this->getData('type');
	}

	/**
	 * Set type
	 * @param $type int SEARCH_FORM_ELEMENT_TYPE_...
	 */
	function setType($type) {
		return $this->setData('type', $type);
	}

	/**
	 * Get the list of options for a SEARCH_FORM_ELEMENT_TYPE_SELECT form element
	 * @return Object iterator
	 */
	function &getOptions() {
		$searchFormElementDao =& DAORegistry::getDAO('SearchFormElementDAO');
		$returner =& $searchFormElementDao->getSearchFormElementOptions(
			$this->getSearchFormElementId()
		);
		return $returner;
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
	 * Get symbolic name
	 * @return string
	 */
	function getSymbolic() {
		return $this->getData('symbolic');
	}

	/**
	 * Set symbolic name
	 * @param $symbolic string
	 */
	function setSymbolic($symbolic) {
		return $this->setData('symbolic', $symbolic);
	}

	/**
	 * Get range start
	 * @return string
	 */
	function getRangeStart() {
		return $this->getData('rangeStart');
	}

	/**
	 * Set range start
	 * @param $rangeStart string
	 */
	function setRangeStart($rangeStart) {
		return $this->setData('rangeStart', $rangeStart);
	}

	/**
	 * Get range end
	 * @return string
	 */
	function getRangeEnd() {
		return $this->getData('rangeEnd');
	}

	/**
	 * Set range end
	 * @param $rangeEnd string
	 */
	function setRangeEnd($rangeEnd) {
		return $this->setData('rangeEnd', $rangeEnd);
	}
}

?>
