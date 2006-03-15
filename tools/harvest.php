<?php

/**
 * harvest.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package tools
 *
 * CLI tool to harvest an archive.
 *
 * $Id$
 */

require(dirname(__FILE__) . '/includes/cliTool.inc.php');

class harvest extends CommandLineTool {
	/** @var $archive object */
	var $archive;

	function harvest($argv = array()) {
		parent::CommandLineTool($argv);

		array_shift($argv); // Clear the tool name from argv

		$archiveId = (int) array_shift($argv);
		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$this->archive =& $archiveDao->getArchive($archiveId);
	}

	/**
	 * Print command usage information.
	 */
	function usage() {
		echo "Script to harvest an archive\n"
			. "Usage: {$this->scriptName} [archive ID]\n"
			. "If no archive ID is specified, a list will be displayed.\n";
	}
	
	/**
	 * Rebuild the search index for all articles in all journals.
	 */
	function execute() {
		if ($this->archive) {
			$recordDao =& DAORegistry::getDAO('RecordDAO');
			$archive =& $this->archive;
			
			echo 'Selected archive: ' . $archive->getTitle() . "\n";
			$recordCount = $recordDao->getRecordCount($archive->getArchiveId());
			echo 'Flushing metadata index for archive... ';
			$recordDao->deleteRecordsByArchiveId($archive->getArchiveId());
			echo $recordCount . " records deleted.\n";

			$fetchStartTime = time();

			echo "Fetching records...\n";
			$plugins =& PluginRegistry::loadCategory('harvesters');
			$pluginName = $archive->getHarvesterPluginName();
			if (!isset($plugins[$pluginName])) {
				echo "Unknown harvester plugin \"$pluginName\"!\n";
				return false;
			}

			$plugin = $plugins[$pluginName];
			$plugin->updateIndex($archive, array(&$this, 'statusCallback'));

			$fetchEndTime = time();
			$timeElapsed = $fetchEndTime - $fetchStartTime;
			$recordCount = $recordDao->getRecordCount($archive->getArchiveId());
			if ($timeElapsed > 0) $recordsPerSecond = $recordCount / $timeElapsed;
			else $recordsPerSecond = 0;
			$recordsPerSecond = number_format($recordsPerSecond, 2);

			echo "Finished; $recordCount records indexed in $timeElapsed seconds ($recordsPerSecond records per second).\n";
			return true;
		} else {
			// No archive was specified or the specified ID was invalid.
			// Display a list of archives.
			$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
			$archives =& $archiveDao->getArchives();
			echo "Archive List\n";
			echo "------------\n";
			while ($archive =& $archives->next()) {
				echo $archive->getArchiveId() . ': ' . $archive->getTitle() . "\n";
				unset($archive);
			}
			return false;
		}
	}

	function statusCallback($message) {
		echo "$message\n";
	}
}

$tool = &new harvest(isset($argv) ? $argv : array());
$tool->execute();
?>
