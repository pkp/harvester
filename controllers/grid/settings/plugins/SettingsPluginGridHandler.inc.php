<?php

/**
 * @file controllers/grid/settings/plugins/SettingsPluginGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsPluginGridHandler
 * @ingroup controllers_grid_settings_plugins
 *
 * @brief Handle plugin grid requests.
 */

import('lib.pkp.classes.controllers.grid.plugins.PluginGridHandler');

class SettingsPluginGridHandler extends PluginGridHandler {
	/**
	 * Constructor
	 */
	function SettingsPluginGridHandler() {
		$roles = array(ROLE_ID_SITE_ADMIN);
		$this->addRoleAssignment($roles, array('plugin'));
		parent::PluginGridHandler($roles);
	}


	//
	// Overridden template methods.
	//
	/**
	 * @see CategoryGridHandler::getRowInstance()
	 */
	function getRowInstance() {
		$userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);

		import('controllers.grid.plugins.PluginGridRow');
		return new PluginGridRow($userRoles);
	}

	/**
	 * @see GridHandler::authorize()
	 */
	function authorize($request, $args, $roleAssignments) {
		$category = $request->getUserVar('category');
		$pluginName = $request->getUserVar('plugin');
		$verb = $request->getUserVar('verb');

		if ($category && $pluginName) {
			import('classes.security.authorization.OhsPluginAccessPolicy');
			$this->addPolicy(new OhsPluginAccessPolicy($request, $args, $roleAssignments));
		}

		return parent::authorize($request, $args, $roleAssignments);
	}
}

?>
