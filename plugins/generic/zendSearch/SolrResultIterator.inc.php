<?php

/**
 * @file plugins/generic/zendSearch/SolrResultIterator.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SolrResultIterator
 * @ingroup plugins_generic_zendSearch
 *
 * @brief Zend Search result iterator
 *
 */

// $Id$


import('core.ArrayItemIterator');

class SolrResultIterator extends ArrayItemIterator {
	/* @var $recordDao object */
	var $recordDao;

	function SolrResultIterator(&$theArray, $page=-1, $itemsPerPage=-1) {
		parent::ArrayItemIterator($theArray, $page, $itemsPerPage);
		$this->recordDao =& DAORegistry::getDAO('RecordDAO');
	}

	function &fromRangeInfo(&$theArray, &$theRange) {
		if ($theRange && $theRange->isValid()) {
			$theIterator = new SolrResultIterator($theArray, $theRange->getPage(), $theRange->getCount());
		} else {
			$theIterator = new SolrResultIterator($theArray);
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
