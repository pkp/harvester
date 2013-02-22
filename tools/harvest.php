<?php

/**
 * @file tools/harvest.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class harvest
 * @ingroup tools
 *
 * @brief CLI tool to harvest an archive.
 *
 */

// $Id$


require(dirname(__FILE__) . '/bootstrap.inc.php');

class harvest extends CommandLineTool {
	/** @var $firstParam mixed */
	var $firstParam;

	/** @var $archives array */
	var $archives;

	/** @var $params array */
	var $params;

	function harvest($argv = array()) {
		parent::CommandLineTool($argv);

		array_shift($argv); // Clear the tool name from argv

		$this->firstParam = array_shift($argv);
		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		if ($this->firstParam === 'all') {
			$this->archives =& $archiveDao->getArchives();
		}
		else {
			$archive =& $archiveDao->getArchive((int) $this->firstParam, false);
			if ($archive) {
				$archives = array(&$archive);
				import('lib.pkp.classes.core.ArrayItemIterator');
				$this->archives = new ArrayItemIterator($archives);
			} else {
				$this->archives = null; // Invalid ID specified
			}
		}

		// Set the various flags for the parser, if supported.
		$this->params = array();

		foreach ($argv as $arg) switch ($arg) {
			case 'verbose':
				$this->params['callback'] = array(&$this, 'statusCallback');
			default:
				if (($i = strpos($arg, '=')) !== false) {
					// Treat the parameter like a name=value pair
					$paramName = substr($arg, 0, $i);
					$paramValue = substr($arg, $i+1);
					if (!isset($this->params[$paramName])) {
						$this->params[$paramName] = $paramValue;
					} else {
						if (is_array($this->params[$paramName])) $this->params[$paramName][] = $paramValue;
						else $this->params[$paramName] = array($this->params[$paramName], $paramValue);
					}
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
			. "If the specified archive ID is \"all\", all archives will be harvested.\n"
			. "Flags include:\n"
			. "\tverbose: Display status information during the harvest.\n"
			. "\tflush: Flush the contents of the archive before harvesting.\n"
			. "\tusage: Display additional usage information for the particular archive\n"
			. "Additional flags for each harvester can be listed using:\n"
			. "\t{$this->scriptName} [archive ID] usage\n\n"
			. "For example, to update all records using the OAI harvester:\n"
			. "\t{$this->scriptName} all from=last\n";
	}

	/**
	 * Rebuild the search index for all articles in all journals.
	 */
	function execute() {
		@set_time_limit(0);
		$hadErrors = false;
		if ($this->archives) while ($archive =& $this->archives->next()) {
			$recordDao =& DAORegistry::getDAO('RecordDAO');

			// Get the archive plugin
			PluginRegistry::loadCategory('preprocessors');
			PluginRegistry::loadCategory('postprocessors');
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
			$oldRecordCount = $recordDao->getRecordCount($archive->getArchiveId());
			if (isset($this->params['flush'])) {
				echo 'Flushing metadata index for archive... ';
				$recordDao->deleteRecordsByArchiveId(
					$archive->getArchiveId(),
					!isset($this->params['skipIndexing'])
				);
				echo $oldRecordCount . " records deleted.\n";
				$oldRecordCount = 0;
			}

			$fetchStartTime = time();

			echo "Fetching records...\n";
			$plugin->updateIndex($archive, $this->params);

			$fetchEndTime = time();
			$timeElapsed = $fetchEndTime - $fetchStartTime;
			$recordCount = $recordDao->getRecordCount($archive->getArchiveId());
			$harvestedRecords = $recordCount - $oldRecordCount;
			if ($timeElapsed > 0) $recordsPerSecond = $harvestedRecords / $timeElapsed;
			else $recordsPerSecond = 0;
			$recordsPerSecond = number_format($recordsPerSecond, 2);

			echo "Finished:\n";
			echo "\t$harvestedRecords records indexed\n";
			echo "\t$timeElapsed seconds elapsed\n";
			echo "\t$recordsPerSecond records per second\n";
			echo "\t$oldRecordCount records kept from past harvests\n";
			echo "\t$recordCount records total.\n";
			if ($errors = $plugin->getErrors()) {
				echo "Errors/Warnings:\n";
				foreach (array_unique($errors) as $error) {
					echo "\t$error\n";
				}
				$plugin->clearErrors();
				$hadErrors = true;
				echo "\n";
			}
			unset($archive);
		} else {
			if ($this->firstParam == '' || $this->firstParam === 'help' || $this->firstParam === 'usage') {
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
		return !$hadErrors;
	}

	function statusCallback($message) {
		echo "$message\n";
	}
}

$tool = new harvest(isset($argv) ? $argv : array());
$tool->execute();
?>
