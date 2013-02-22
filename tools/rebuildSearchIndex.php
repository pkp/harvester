<?php

/**
 * @file tools/rebuildSearchIndex.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class rebuildSearchIndex
 * @ingroup tools
 *
 * @brief CLI tool to rebuild the keyword search database.
 *
 */

// $Id$


require(dirname(__FILE__) . '/bootstrap.inc.php');

class rebuildSearchIndex extends CommandLineTool {

	/**
	 * Print command usage information.
	 */
	function usage() {
		echo "Script to rebuild article search index\n"
			. "Usage: {$this->scriptName} [options]\n"
			. "where the supported options include...\n"
			. "\t--reparse: Re-parse the source data into parsed data fields\n"
			. "\t--sort-order [sortOrderId]: Re-index the specified sort order\n";
	}

	/**
	 * Rebuild the sort order index for a given sort order.
	 * @param $sortOrderId int
	 */
	function reindexSortOrder($sortOrderId) {
		$sortOrderDao =& DAORegistry::getDAO('SortOrderDAO');
		$sortOrder = $sortOrderDao->getSortOrder($sortOrderId);

		if (!$sortOrder) return false;

		// Flag the index as dirty in case of error while indexing
		$sortOrder->setIsClean(false);
		$sortOrderDao->updateSortOrder($sortOrder);

		$sortOrderDao->flushSortOrderIndex($sortOrderId);

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$recordDao =& DAORegistry::getDAO('RecordDAO');
		$archives =& $archiveDao->getArchives();
		while ($archive =& $archives->next()) {
			$records =& $recordDao->getRecords($archive->getArchiveId());
			$harvester =& $archive->getHarvester();
			while ($record =& $records->next()) {
				$harvester->indexRecordSortingForSortOrder($record, $sortOrder);
				unset($record);
			}
			unset($archive, $records);
		}

		// Flag the index as clean.
		$sortOrder->setIsClean(true);
		$sortOrderDao->updateSortOrder($sortOrder);
	}

	/**
	 * Rebuild the search indexes.
	 */
	function execute() {
		echo 'Flushing sort order index... ';
		$sortOrderDao =& DAORegistry::getDAO('SortOrderDAO');
		$sortOrderDao->flush();
		echo "Done.\n";

		echo 'Performing other index flush tasks... ';
		HookRegistry::call('rebuildSearchIndex::flush');
		echo "Done.\n";

		echo "Indexing records...\n";
		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$recordDao =& DAORegistry::getDAO('RecordDAO');
		$archives =& $archiveDao->getArchives();
		while ($archive =& $archives->next()) {
			echo ' ' . $archive->getTitle() . '... ';
			$harvester =& $archive->getHarvester();
			$records =& $recordDao->getRecords($archive->getArchiveId());
			while ($record =& $records->next()) {
				$harvester->indexRecordSorting($record);
				HookRegistry::call('Harvester::indexRecord', array(&$record));
				unset($record);
			}
			unset($archive, $records, $harvester);
			echo "Done.\n";
		}

		echo 'Marking sort orders clean... ';
		$sortOrders =& $sortOrderDao->getSortOrders();
		while ($sortOrder =& $sortOrders->next()) {
			$sortOrder->setIsClean(true);
			$sortOrderDao->updateSortOrder($sortOrder);
		}
		unset($sortOrders);
		echo "Done.\n";

		echo 'Performing other cleanup tasks... ';
		HookRegistry::call('rebuildSearchIndex::finish');
		echo "Done.\n";
	}

}

$tool = new rebuildSearchIndex(isset($argv) ? $argv : array());
$tool->execute();
?>
