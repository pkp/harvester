<?php

/**
 * SearchHandler.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.search
 *
 * Handle requests for search functions. 
 *
 * $Id$
 */

class SearchHandler extends Handler {

	/**
	 * Display site admin index page.
	 */
	function index() {
		SearchHandler::validate();
		SearchHandler::setupTemplate();
			
		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'site.search');
		$templateMgr->display('search/index.tpl');
	}
	
	/**
	 * Validate that user has admin privileges
	 * Redirects to the user index page if not properly authenticated.
	 */
	function validate() {
		parent::validate();
	}
	
	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		$templateMgr = &TemplateManager::getManager();
		if ($subclass) {
			$templateMgr->assign('pageHierarchy',
				array(array(Request::url('search'), 'navigation.search'))
			);
		}
	}
}

?>
