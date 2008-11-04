<?php

/**
 * @file managePlugin.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class managePlugin
 * @ingroup tools
 *
 * @brief CLI tool to manage plugins.
 */

// $Id$


define('INDEX_FILE_LOCATION', dirname(dirname(__FILE__)) . '/index.php');
require(dirname(dirname(__FILE__)) . '/lib/pkp/classes/cliTool/CliTool.inc.php');

import('cliTool.PluginTool');

class managePlugin extends PluginTool {
	/**
	 * Constructor.
	 * @param $argv array command-line arguments
	 */
	function managePlugin($argv = array()) {
		parent::PluginTool($argv);
	}
}

$tool =& new managePlugin(isset($argv) ? $argv : array());
$tool->execute();

?>