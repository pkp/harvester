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

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$templateMgr->assign('archives', $archiveDao->getArchives());

		// Assign prior values, if supplied, to form fields
		$templateMgr->assign('query', Request::getUserVar('query'));
		$templateMgr->assign('archiveIds', Request::getUserVar('archiveIds'));

		$templateMgr->display('search/index.tpl');
	}

	/**
	 * Display search results.
	 */
	function results($args) {
		SearchHandler::validate();
		SearchHandler::setupTemplate();
		import('search.Search');
		$rangeInfo = Handler::getRangeInfo('search');

		// Get the archives we're searching.
		$archiveIds = Request::getUserVar('archiveIds');
		if (!is_array($archiveIds)) {
			if (empty($archiveIds)) $archiveIds = null;
			else $archiveIds = array($archiveIds);
		}
		if ($archiveIds !== null && in_array('all', $archiveIds)) {
			$archiveIds = null;
		}

		$query = Request::getUserVar('query');

		$keywords = array('FIXME' => Search::parseQuery($query));
		$results = &Search::retrieveResults($keywords, $archiveIds, $rangeInfo);

		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'site.search');

		// Give the results page access to the search parameters
		$templateMgr->assign('isAdvanced', Request::getUserVar('isAdvanced'));
		$templateMgr->assign('query', $query);
		$templateMgr->assign('archiveIds', Request::getUserVar('archiveIds'));

		$templateMgr->assign_by_ref('results', $results);
		$templateMgr->display('search/results.tpl');
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
