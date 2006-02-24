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
	 * Display site search page.
	 */
	function index() {
		SearchHandler::validate();
		SearchHandler::setupTemplate();
			
		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'site.search');
		$templateMgr->display('search/index.tpl');
	}

	/**
	 * Display search results.
	 */
	function results($args, $isBasic = false) {
		SearchHandler::validate();
		SearchHandler::setupTemplate();
		import('search.Search');
		$rangeInfo = Handler::getRangeInfo('search');

		$keywords = array('FIXME' => Search::parseQuery(Request::getUserVar('query')));
		$results = &Search::retrieveResults($keywords, null, null, $rangeInfo);

		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'site.search');
		$templateMgr->assign('basicQuery', $isBasic);
		$templateMgr->assign('query', Request::getUserVar('query'));
		$templateMgr->assign_by_ref('results', $results);

		$templateMgr->display('search/results.tpl');
	}

	function basicResults($args) {
		SearchHandler::results($args, true);
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
