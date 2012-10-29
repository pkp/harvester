<?php

/**
 * @file controllers/grid/plugins/PluginGridRow.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PluginGridRow
 * @ingroup controllers_grid_plugins
 *
 * @brief Plugin grid row definition
 */

import('lib.pkp.classes.controllers.grid.plugins.PKPPluginGridRow');

class PluginGridRow extends PKPPluginGridRow {
	/**
	 * Constructor
	 * @param $userRoles array
	 */
	function PluginGridRow($userRoles) {
		parent::PKPPluginGridRow($userRoles, 0);
	}


	//
	// Protected helper methods
	//
	/**
	 * Return if user can edit a plugin settings or not.
	 * @param $plugin Plugin
	 * @return boolean
	 */
	function _canEdit(&$plugin) {
		return in_array(ROLE_ID_SITE_ADMIN, $this->_userRoles);
	}
}

?>
