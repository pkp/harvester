<?php

/**
 * @file pages/record/RecordHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.record
 * @class RecordHandler
 *
 * Handle requests for browse functions. 
 *
 */



import('classes.handler.Handler');

class RecordHandler extends Handler {
	function index($args, &$request) {
		$request->redirect('browse');
	}

	function view($args, &$request) {
		$this->validate();

		$recordDao =& DAORegistry::getDAO('RecordDAO');

		$recordId = (int) array_shift($args);
		$record =& $recordDao->getRecord($recordId);
		if (!$record) $request->redirect('index');

		$archive =& $record->getArchive();
		if (!$archive || !$archive->getEnabled()) $request->redirect('index');

		$this->setupTemplate($request, $record, true);
		$record->display();
	}

	/**
	 * Setup common template variables.
	 * @param $request PKPRequest
	 * @param $record object optional
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($request, &$record, $subclass = false) {
		parent::setupTemplate($request);
		$templateMgr =& TemplateManager::getManager($request);
		$hierarchy = array();
		if ($subclass) {
			$hierarchy[] = array($request->url('browse'), 'navigation.browse');
		}
		if ($record) {
			$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
			$archive =& $archiveDao->getArchive($record->getArchiveId(), false);
			$hierarchy[] = array($request->url('browse', 'index', $archive->getArchiveId()), $archive->getTitle(), true);
		}
		$templateMgr->assign('pageHierarchy', $hierarchy);
		$templateMgr->assign('theseArchiveIds', array($archive->getArchiveId()));
	}
}

?>
