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

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');

		$archiveId = array_shift($args);
		$archive = null;
		if ($archiveId === 'all' || ($archive =& $archiveDao->getArchive($archiveId))) {
			// The user has chosen an archive or opted to browse all.
		} else {
			// List archives for the user to browse.
			$rangeInfo = Handler::getRangeInfo('archives');
			// Load the harvester plugins so we can display names.
			$plugins =& PluginRegistry::loadCategory('harvesters');

			$archives =& $archiveDao->getArchives($rangeInfo);

			$templateMgr = &TemplateManager::getManager();
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
