<?php

/**
 * @file pages/admin/PluginHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PluginHandler
 * @ingroup pages_admin
 *
 * @brief Handle requests for plugin management functions.
 */


import('pages.admin.AdminHandler');

class PluginHandler extends AdminHandler {
	/**
	 * Display a list of plugins along with management options.
	 */
	function plugins($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$this->validate();

		$this->setupTemplate($request, true);
		$templateMgr->assign('pageTitle', 'admin.plugins');
		$templateMgr->assign('pageHierarchy', array(
			array(
				$request->url('admin'),
				'admin.siteAdmin',
				false
			)
		));
		$templateMgr->display('admin/plugins.tpl');
	}

	/**
	 * Perform plugin-specific management functions.
	 */
	function plugin($args, &$request) {
		$category = array_shift($args);
		$plugin = array_shift($args);
		$verb = array_shift($args);

		$this->validate();
		$this->setupTemplate($request, true);

		$plugins =& PluginRegistry::loadCategory($category);
		$message = null;
		if (!isset($plugins[$plugin]) || !$plugins[$plugin]->manage($verb, $args, $message)) {
			if ($message) {
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign('message', $message);
			}
			$this->plugins(array($category));
		}

	}
}

?>
