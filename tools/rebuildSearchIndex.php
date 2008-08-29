<?php

/**
 * @file tools/rebuildSearchIndex.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class rebuildSearchIndex
 * @ingroup tools
 *
 * @brief CLI tool to rebuild the keyword search database.
 *
 */

// $Id$


define('INDEX_FILE_LOCATION', dirname(dirname(__FILE__)) . '/index.php');
require(dirname(dirname(__FILE__)) . '/lib/pkp/classes/cliTool/CliTool.inc.php');

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
		if (empty($this->argv)) return $this->usage();
		while (true) {
			$cmd = array_shift($this->argv);
			if (!$cmd) exit;
			switch ($cmd) {
				case '--reparse':
					die('implement');
					break;
				case '--sort-order':
					$sortOrderId = array_shift($this->argv);
					$this->reindexSortOrder($sortOrderId);
					break;
				default:
					fatalError("Unknown command \"$cmd\"!");
					break;
			}
		}
	}

}

$tool = &new rebuildSearchIndex(isset($argv) ? $argv : array());
$tool->execute();
?>
