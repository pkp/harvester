<?php

/**
 * @file plugins/generic/zendSearch/SolrResultIterator.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SolrResultIterator
 * @ingroup plugins_generic_zendSearch
 *
 * @brief Zend Search result iterator
 *
 */

// $Id$


import('lib.pkp.classes.core.VirtualArrayIterator');

class SolrResultIterator extends VirtualArrayIterator {
	/* @var $recordDao object */
	var $recordDao;

	function SolrResultIterator(&$theArray, $totalItems, $page=-1, $itemsPerPage=-1) {
		parent::VirtualArrayIterator($theArray, $totalItems, $page, $itemsPerPage);
		$this->recordDao =& DAORegistry::getDAO('RecordDAO');
	}

	function &fromRangeInfo(&$theArray, $totalItems, &$theRange) {
		if ($theRange && $theRange->isValid()) {
			$theIterator = new SolrResultIterator($theArray, $totalItems, $theRange->getPage(), $theRange->getCount());
		} else {
			$theIterator = new SolrResultIterator($theArray, $totalItems);
		}
		return $theIterator;
	}

	/**
	 * Return the next item in the iterator.
	 * @return object Record
	 */
	function &next() {
		$recordId = parent::next();
		$returner =& $this->recordDao->getRecord($recordId);
		return $returner;
	}
}

?>
