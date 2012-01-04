<?php

/**
 * @file RTBlockPlugin.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RTBlockPlugin
 * @ingroup plugins_blocks_rt
 *
 * @brief Class for RT block plugin
 */

// $Id$


import('lib.pkp.classes.plugins.BlockPlugin');

class RTBlockPlugin extends BlockPlugin {
	/**
	 * Install default settings on system install.
	 * @return string
	 */
	function getInstallSitePluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.block.rt.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.block.rt.description');
	}

	function getContents(&$templateMgr) {
		if (Request::getRequestedPage() . '/' . Request::getRequestedOp() !== 'record/view') return '';
		return parent::getContents($templateMgr);
	}
}

?>
