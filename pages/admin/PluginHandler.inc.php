<?php

/**
 * @file pages/admin/PluginHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.admin
 * @class PluginHandler
 *
 * Handle requests for plugin management functions.
 *
 */

// $Id$

import('pages.admin.AdminHandler');

class PluginHandler extends AdminHandler {
	/**
	 * Display a list of plugins along with management options.
	 */
	function plugins($args) {
		$category = isset($args[0])?$args[0]:null;
		$categories = PluginRegistry::getCategories();
		
		$templateMgr =& TemplateManager::getManager();
		$this->validate();

		if (isset($category)) {
			// The user specified a category of plugins to view;
			// get the plugins in that category only.
			$mainPage = false;
			$plugins =& PluginRegistry::loadCategory($category);

			$this->setupTemplate(false);
			$templateMgr->assign('pageTitle', 'plugins.categories.' . $category);
			$templateMgr->assign('pageHierarchy', $this->setBreadcrumbs(true));
		} else {
			// No plugin specified; display all.
			$mainPage = true;
			$plugins = array();
			foreach ($categories as $category) {
				$newPlugins =& PluginRegistry::loadCategory($category);
				if (isset($newPlugins)) {
					$plugins = array_merge($plugins, PluginRegistry::loadCategory($category));
				}
			}
			
			$this->setupTemplate(true);
			$templateMgr->assign('pageTitle', 'admin.plugins');
			$templateMgr->assign('pageHierarchy', $this->setBreadcrumbs(false));
		}

		$templateMgr->assign_by_ref('plugins', $plugins);
		$templateMgr->assign_by_ref('categories', $categories);
		$templateMgr->assign('mainPage', $mainPage);
		$templateMgr->assign('isSiteAdmin', Validation::isSiteAdmin());
		
		$templateMgr->display('admin/plugins.tpl');
	}

	/**
	 * Perform plugin-specific management functions.
	 */
	function plugin($args) {
		$category = array_shift($args);
		$plugin = array_shift($args);
		$verb = array_shift($args);

		$this->validate();
		$this->setupTemplate(true);

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
	
	/**
	 * Set the page's breadcrumbs
	 * @param $subclass boolean
	 */
	function setBreadcrumbs($subclass = false) {
		$templateMgr =& TemplateManager::getManager();
		$pageCrumbs = array(
			array(
				Request::url('admin'),
				'admin.siteAdmin',
				false
			)
		);
		
		if ($subclass) {
			$pageCrumbs[] = array(
				Request::url(null, 'plugins'),
				"admin.plugins",
				false
			);
		}

		return $pageCrumbs;
	}
}

?>
