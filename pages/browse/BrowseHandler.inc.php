<?php

/**
 * BrowseHandler.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.browse
 *
 * Handle requests for browse functions. 
 *
 * $Id$
 */

class BrowseHandler extends Handler {

	/**
	 * Display site admin index page.
	 */
	function index($args) {
		BrowseHandler::validate();
		BrowseHandler::setupTemplate();
		$templateMgr = &TemplateManager::getManager();

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$recordDao =& DAORegistry::getDAO('RecordDAO');

		$archiveId = array_shift($args);
		$archive = null;
		if ($archiveId === 'all' || ($archive =& $archiveDao->getArchive($archiveId))) {
			$rangeInfo = Handler::getRangeInfo('records');
			$sort = RECORD_SORT_DATE; // FIXME

			// The user has chosen an archive or opted to browse all
			$records =& $recordDao->getRecords($archive?$archiveId:null, $sort, $rangeInfo);

			$templateMgr->assign_by_ref('records', $records);
			$templateMgr->display('browse/records.tpl');
		} else {
			// List archives for the user to browse.
			$rangeInfo = Handler::getRangeInfo('archives');

			$archives =& $archiveDao->getArchives($rangeInfo);

			$templateMgr->assign('helpTopicId', 'site.browse');
			$templateMgr->assign_by_ref('archives', $archives);
			$templateMgr->display('browse/index.tpl');
		}
	}
	
	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		$templateMgr = &TemplateManager::getManager();
		if ($subclass) {
			$templateMgr->assign('pageHierarchy',
				array(array(Request::url('browse'), 'navigation.browse'))
			);
		}
	}
}

?>
