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

	function editSearchableField($args = array()) {
		parent::validate();
		parent::setupTemplate(true);

		import('admin.form.SearchableFieldForm');
		$searchableFieldForm =& new SearchableFieldForm(!isset($args) || empty($args) ? null : (int) $args[0]);
		$searchableFieldForm->initData();
		$searchableFieldForm->display();
	}

	/**
	 * Save changes to a searchable field's settings.
	 */
	function updateSearchableField() {
		parent::validate();
		
		import('admin.form.SearchableFieldForm');
		
		$searchableFieldForm = &new SearchableFieldForm(Request::getUserVar('searchableFieldId'));
		$searchableFieldForm->initData();
		$searchableFieldForm->readInputData();
		
		if ($searchableFieldForm->validate()) {
			$searchableFieldForm->execute();
			Request::redirect('admin', 'indexing');
			
		} else {
			parent::setupTemplate(true);
			$searchableFieldForm->display();
		}
	}
	
	function deleteSearchableField($args) {
		parent::validate();

		$searchableFieldDao =& DAORegistry::getDAO('SearchableFieldDAO');
		if (isset($args) && !empty($args) && !empty($args[0])) {
			$searchableFieldId = $args[0];
			$searchableFieldDao->deleteSearchableFieldById($searchableFieldId);
		}
		Request::redirect('admin', 'indexing');
	}

	function createSearchableField() {
		AdminIndexingHandler::editSearchableField();
	}
}

?>
