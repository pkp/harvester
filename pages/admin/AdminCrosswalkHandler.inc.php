<?php

/**
 * AdminCrosswalkHandler.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.admin
 *
 * Handle requests for changing crosswalk settings. 
 *
 * $Id$
 */

class AdminCrosswalkHandler extends AdminHandler {
	
	/**
	 * Display indexing information.
	 */
	function crosswalks() {
		parent::validate();
		parent::setupTemplate(true);
		
		$rangeInfo = Handler::getRangeInfo('crosswalks');

		$crosswalkDao = &DAORegistry::getDAO('CrosswalkDAO');
		$crosswalks = &$crosswalkDao->getCrosswalks($rangeInfo);
		
		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign_by_ref('crosswalks', $crosswalks);
		$templateMgr->assign('helpTopicId', 'site.crosswalks');
		$templateMgr->display('admin/crosswalks.tpl');
	}

	function editCrosswalk() {
		parent::validate();
		parent::setupTemplate(true);

		import('admin.form.CrosswalkForm');
		$crosswalkForm =& new CrosswalkForm(Request::getUserVar('crosswalkId'));
		$crosswalkForm->initData();
		$crosswalkForm->display();
	}

	/**
	 * Save changes to a searchable field's settings.
	 */
	function updateCrosswalk() {
		parent::validate();
		
		import('admin.form.CrosswalkForm');
		
		$crosswalkForm = &new CrosswalkForm(Request::getUserVar('crosswalkId'));
		$crosswalkForm->initData();
		$crosswalkForm->readInputData();
		
		if ($crosswalkForm->validate()) {
			$crosswalkForm->execute();
			Request::redirect('admin', 'crosswalks');
			
		} else {
			parent::setupTemplate(true);
			$crosswalkForm->display();
		}
	}
	
	function deleteCrosswalk($args) {
		parent::validate();

		$crosswalkDao =& DAORegistry::getDAO('CrosswalkDAO');
		if (isset($args) && !empty($args) && !empty($args[0])) {
			$crosswalkId = $args[0];
			$crosswalkDao->deleteCrosswalkById($crosswalkId);
		}
		Request::redirect('admin', 'crosswalks');
	}

	function createCrosswalk() {
		AdminCrosswalkHandler::editCrosswalk();
	}

	/**
	 * Reset crosswalks to the installation default.
	 */
	function resetCrosswalks() {
		parent::validate();

		$crosswalkDao =& DAORegistry::getDAO('CrosswalkDAO');
		$crosswalks =& $crosswalkDao->getCrosswalks();
		while ($crosswalk =& $crosswalks->next()) {
			$crosswalkDao->deleteCrosswalk($crosswalk);
			unset($crosswalk);
		}
		$crosswalkDao->installCrosswalks('registry/crosswalks.xml');
		Request::redirect('admin', 'crosswalks');
	}
}

?>
