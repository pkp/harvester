<?php

/**
 * @file harvest.php
 *
 * Copyright (c) 2005-2006 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package tools
 * @class harvest
 *
 * CLI tool to harvest an archive.
 *
 * $Id$
 */

require(dirname(__FILE__) . '/includes/cliTool.inc.php');

class harvest extends CommandLineTool {
	/** @var $firstParam mixed */
	var $firstParam;

	/** @var $archive object */
	var $archive;

	/** @var $params array */
	var $params;

	function harvest($argv = array()) {
		parent::CommandLineTool($argv);

		array_shift($argv); // Clear the tool name from argv

		$this->firstParam = array_shift($argv);
		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$this->archive =& $archiveDao->getArchive((int) $this->firstParam);

		// Set the various flags for the parser, if supported.
		$this->params = array();

		foreach ($argv as $arg) switch ($arg) {
			case 'verbose':
				$this->params['callback'] = array(&$this, 'statusCallback');
				break;
			default:
				if (($i = strpos($arg, '=')) !== false) {
					// Treat the parameter like a name=value pair
					$this->params[substr($arg, 0, $i)] = substr($arg, $i+1);
				} else {
					// Treat the parameter like a boolean.
					$this->params[$arg] = true;
				}
				break;
		}
	}

	/**
	 * Print command usage information.
	 */
	function usage() {
		echo "Script to harvest an archive\n"
			. "Usage: {$this->scriptName} [archive ID] [flags]\n"
			. "If the specified archive ID is \"list\", a list will be displayed.\n"
			. "Flags include:\n"
			. "\tverbose: Display status information during the harvest.\n"
			. "\tflush: Flush the contents of the archive before harvesting.\n"
			. "\tusage: Display additional usage information for the particular archive\n"
			. "\tskipIndexing: Skip flushing and creation of search indexing\n"
			. "Additional flags for each harvester can be listed using:\n"
			. "\t{$this->scriptName} [archive ID] usage\n";
	}
	
	/**
	 * Rebuild the search index for all articles in all journals.
	 */
	function execute() {
		if ($this->archive) {
			$recordDao =& DAORegistry::getDAO('RecordDAO');
			$archive =& $this->archive;

			// Get the archive plugin
			$plugins =& PluginRegistry::loadCategory('harvesters');
			$pluginName = $archive->getHarvesterPluginName();
			if (!isset($plugins[$pluginName])) {
				echo "Unknown harvester plugin \"$pluginName\"!\n";
				return false;
			}
			$plugin = $plugins[$pluginName];

			if (isset($this->params['usage'])) {
				$this->usage();
				$plugin->describeOptions();
				return true;
			}
			
			echo 'Selected archive: ' . $archive->getTitle() . "\n";
			$recordCount = $recordDao->getRecordCount($archive->getArchiveId());
			if (isset($this->params['flush'])) {
				echo 'Flushing metadata index for archive... ';
				$recordDao->deleteRecordsByArchiveId(
					$archive->getArchiveId(),
					!isset($this->params['skipIndexing'])
				);
				echo $recordCount . " records deleted.\n";
			} else {
				echo "Not flushing an existing $recordCount records.\n";
			}

			$fetchStartTime = time();

			echo "Fetching records...\n";
			$plugin->updateIndex($archive, $this->params);

			$fetchEndTime = time();
			$timeElapsed = $fetchEndTime - $fetchStartTime;
			$recordCount = $recordDao->getRecordCount($archive->getArchiveId());
			if ($timeElapsed > 0) $recordsPerSecond = $recordCount / $timeElapsed;
			else $recordsPerSecond = 0;
			$recordsPerSecond = number_format($recordsPerSecond, 2);

			echo "Finished; $recordCount records indexed in $timeElapsed seconds ($recordsPerSecond records per second).\n";
			if ($errors = $plugin->getErrors()) {
				echo "Errors/Warnings:\n";
				foreach ($errors as $error) {
					echo "\t$error\n";
				}
				return false;
			}
			return true;
		} else {
			if ($this->firstParam == '' || $this->firstParam === 'help') {
				$this->usage();
				return true;
			}

			// No archive was specified or the specified ID was invalid.
			// Display a list of archives.
			$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
			$recordDao =& DAORegistry::getDAO('RecordDAO');
			$archives =& $archiveDao->getArchives();
			echo "Archive List\n";
			echo "------------\n";
			while ($archive =& $archives->next()) {
				$recordCount = $recordDao->getRecordCount($archive->getArchiveId());
				echo $archive->getArchiveId() . ': ' . $archive->getTitle() . " ($recordCount records)\n";
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
