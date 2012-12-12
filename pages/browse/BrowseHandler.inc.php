<?php

/**
 * @file pages/browse/BrowseHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.browse
 * @class BrowseHandler
 *
 * Handle requests for browse functions. 
 *
 */



import('classes.handler.Handler');

class BrowseHandler extends Handler {
	/**
	 * Display record list or archive list.
	 */
	function index($args, &$request) {
		$this->validate();
		$templateMgr =& TemplateManager::getManager($request);

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$recordDao =& DAORegistry::getDAO('RecordDAO');

		$archiveId = array_shift($args);
		$archive = null;
		if (($archive =& $archiveDao->getArchive($archiveId)) || $archiveId == 'all') {
			$this->setupTemplate($request, $archive, true);

			$rangeInfo = $this->getRangeInfo($request, 'records');

			$sortOrderDao = DAORegistry::getDAO('SortOrderDAO');
			$sortOrderId = $request->getUserVar('sortOrderId');
			$sortOrder = $sortOrderDao->getSortOrder($sortOrderId);
			if ($sortOrder) {
				$templateMgr->assign('sortOrderId', $sortOrderId);
			}

			$sortOrders =& $sortOrderDao->getSortOrders();
			$templateMgr->assign_by_ref('sortOrders', $sortOrders);

			// The user has chosen an archive or opted to browse all
			$records =& $recordDao->getRecords(
				$archive?(int)$archiveId:null,
				true, // Only enabled archives
				$sortOrder,
				$rangeInfo
			);

			$templateMgr->assign_by_ref('records', $records);
			$templateMgr->assign_by_ref('archive', $archive);
			$templateMgr->display('browse/records.tpl');
		} else {
			$this->setupTemplate($request, $archive);

			// List archives for the user to browse.
			$rangeInfo = $this->getRangeInfo($request, 'archives');

			$archives = $archiveDao->getArchives(true, $rangeInfo);

			$templateMgr->assign('archives', $archives);
			$templateMgr->display('browse/index.tpl');
		}
	}

	/**
	 * Display archive info.
	 */
	function archiveInfo($args, &$request) {
		$this->validate();
		$templateMgr =& TemplateManager::getManager($request);

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');

		$archiveId = array_shift($args);
		$archive = null;
		if (($archive =& $archiveDao->getArchive($archiveId))) {
			PluginRegistry::loadCategory('harvesters');
			$this->setupTemplate($request, $archive, true);

			$templateMgr->assign_by_ref('archive', $archive);
			$templateMgr->display('browse/archiveInfo.tpl');
		} else {
			$request->redirect('browse');
		}
	}

	/**
	 * Setup common template variables.
	 * @param $request PKPRequest
	 * @param $archive object optional
	 * @param $isSubclass boolean optional
	 */
	function setupTemplate($request, &$archive, $isSubclass = null) {
		parent::setupTemplate($request);
		$templateMgr =& TemplateManager::getManager($request);
		$hierarchy = array();
		if ($isSubclass) {
			$hierarchy[] = array($request->url('browse'), 'navigation.browse');
		}
		if ($archive) {
			$hierarchy[] = array($request->url('browse', 'index', $archive->getArchiveId()), $archive->getTitle(), true);
		}
		$templateMgr->assign('pageHierarchy', $hierarchy);
	}
}

?>
