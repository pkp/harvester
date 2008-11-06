<?php

/**
 * @file ZendSearchAdminHandler.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ZendSearchAdminHandler
 * @ingroup plugins_generic_zendSearch
 *
 * @brief Handle requests for search administration functions
 */

// $Id$


import('core.PKPHandler');

class ZendSearchAdminHandler extends PKPHandler {
	/**
	 * Get the Zend Search Plugin object.
	 */
	function &getPlugin() {
		$plugin =& PluginRegistry::getPlugin('generic', 'ZendSearchPlugin');
		return $plugin;
	}

	/**
	 * Administer the search form.
	 */
	function index($args) {
		ZendSearchAdminHandler::validate();
		ZendSearchAdminHandler::setupTemplate(true);
		$templateMgr =& TemplateManager::getManager();
		$plugin =& ZendSearchAdminHandler::getPlugin();

		$rangeInfo = PKPHandler::getRangeInfo('searchFormElements');
		$searchFormElementDao =& DAORegistry::getDAO('SearchFormElementDAO');
		$searchFormElements =& $searchFormElementDao->getSearchFormElements($rangeInfo);

		$templateMgr->assign_by_ref('searchFormElements', $searchFormElements);
		$templateMgr->display($plugin->getTemplatePath() . 'searchForm.tpl');
	}

	/**
	 * Display form to create a new search form element.
	 */
	function createSearchFormElement() {
		ZendSearchAdminHandler::editSearchFormElement();
	}

	/**
	 * Display form to create/edit a search form element.
	 * @param $args array optional, if set the first parameter is the ID of the search form element to edit
	 */
	function editSearchFormElement($args = array()) {
		ZendSearchAdminHandler::validate();
		ZendSearchAdminHandler::setupTemplate(true);

		$plugin =& ZendSearchAdminHandler::getPlugin();
		$plugin->import('SearchFormElementForm');

		$searchFormElementForm = new SearchFormElementForm(!isset($args) || empty($args) ? null : (int) $args[0]);
		if ($searchFormElementForm->isLocaleResubmit()) {
			$searchFormElementForm->readInputData();
		} else {
			$searchFormElementForm->initData();
		}
		$searchFormElementForm->display();
	}

	/**
	 * Save changes to a search form element's settings.
	 */
	function updateSearchFormElement() {
		ZendSearchAdminHandler::validate();

		$plugin =& ZendSearchAdminHandler::getPlugin();
		$plugin->import('SearchFormElementForm');

		$searchFormElementId = (int) Request::getUserVar('searchFormElementId');

		$searchFormElementForm = new SearchFormElementForm($searchFormElementId);
		$searchFormElementForm->initData();
		$searchFormElementForm->readInputData();

		if ($searchFormElementForm->validate()) {
			$searchFormElementForm->execute();
			Request::redirect(null, 'index');
		} else {
			ZendSearchAdminHandler::setupTemplate(true);
			$searchFormElementForm->display();
		}
	}

	/**
	 * Delete a search form element.
	 * @param $args array first parameter is the ID of the search form element to delete
	 */
	function deleteSearchFormElement($args) {
		ZendSearchAdminHandler::validate();

		$searchFormElementDao =& DAORegistry::getDAO('SearchFormElementDAO');

		// Disable timeout, as this operation may take
		// a long time.
		@set_time_limit(0);

		if (isset($args) && isset($args[0])) {
			$searchFormElementId = $args[0];
			$searchFormElementDao->deleteSearchFormElementById($searchFormElementId);
		}

		Request::redirect(null, 'index', null, array('searchFormElementPage' => Request::getUserVar('searchFormElementPage')));
	}

	/**
	 * Validate that user has admin privileges
	 * Redirects to the user index page if not properly authenticated.
	 */
	function validate() {
		parent::validate();
		if (!Validation::isSiteAdmin()) {
			Validation::redirectLogin();
		}
	}

	/**
	 * Setup common template variables.
	 */
	function setupTemplate() {
		parent::setupTemplate();
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageHierarchy', array(
			array(Request::url('admin'), 'admin.settings.administration')
		));
	}
}

?>
