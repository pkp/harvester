<?php

/**
 * @file plugins/generic/zendSearch/ZendSearchResultIterator.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ZendSearchResultIterator
 * @ingroup plugins_generic_zendSearch
 *
 * @brief Zend Search result iterator
 *
 */

// $Id$


import('lib.pkp.classes.core.ArrayItemIterator');

class ZendSearchResultIterator extends ArrayItemIterator {
	/* @var $recordDao object */
	var $recordDao;

	function ZendSearchResultIterator(&$theArray, $page=-1, $itemsPerPage=-1) {
		parent::ArrayItemIterator($theArray, $page, $itemsPerPage);
		$this->recordDao =& DAORegistry::getDAO('RecordDAO');
	}

	function &fromRangeInfo(&$theArray, &$theRange) {
		if ($theRange && $theRange->isValid()) {
			$theIterator = new ZendSearchResultIterator($theArray, $theRange->getPage(), $theRange->getCount());
		} else {
			$theIterator = new ZendSearchResultIterator($theArray);
		}
		return $theIterator;
	}

	/**
	 * Return the next item in the iterator.
	 * @return object Record
	 */
	function &next() {
		$result =& parent::next();
		$doc = $result->getDocument();
		$recordId = $doc->getFieldValue('harvesterRecordId');
		unset($result, $doc);

		$returner =& $this->recordDao->getRecord($recordId);
		return $returner;
	}
}

?>
