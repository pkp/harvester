<?php

/**
 * @file rebuildSearchIndex.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package tools
 * @class rebuildSearchIndex
 *
 * CLI tool to rebuild the keyword search database.
 *
 * $Id$
 */

require(dirname(__FILE__) . '/includes/cliTool.inc.php');

import('search.SearchIndex');

class rebuildSearchIndex extends CommandLineTool {

	/**
	 * Print command usage information.
	 */
	function usage() {
		echo "Script to rebuild article search index\n"
			. "Usage: {$this->scriptName}\n";
	}

	/**
	 * Rebuild the search index for all articles in all journals.
	 */
	function execute() {
		SearchIndex::rebuildIndex(true);
	}

}

$tool = &new rebuildSearchIndex(isset($argv) ? $argv : array());
$tool->execute();
?>
