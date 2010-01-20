<?php

/**
 * @file managePlugin.php
 *
 * Copyright (c) 2005-2010 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class managePlugin
 * @ingroup tools
 *
 * @brief CLI tool to manage plugins.
 */

// $Id$


require(dirname(__FILE__) . '/bootstrap.inc.php');

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

$tool = new managePlugin(isset($argv) ? $argv : array());
$tool->execute();

?>
