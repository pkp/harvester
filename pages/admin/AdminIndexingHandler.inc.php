<?php

/**
 * AdminIndexingHandler.inc.php
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.admin
 *
 * Handle requests for changing indexing settings. 
 *
 * $Id$
 */

class AdminIndexingHandler extends AdminHandler {
	
	/**
	 * Display indexing information.
	 */
	function indexing() {
		parent::validate();
		parent::setupTemplate(true);
		
		$rangeInfo = Handler::getRangeInfo('searchableFields');

		$searchableFieldDao = &DAORegistry::getDAO('SearchableFieldDAO');
		$searchableFields = &$searchableFieldDao->getSearchableFields($rangeInfo);
		
		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign_by_ref('searchableFields', $searchableFields);
		$templateMgr->assign('helpTopicId', 'site.indexing');
		$templateMgr->display('admin/searchableFields.tpl');
	}
	
}

?>
