<?php

/**
 * @file ZendSearchAdminHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ZendSearchAdminHandler
 * @ingroup plugins_generic_zendSearch
 *
 * @brief Handle requests for search administration functions
 */



import('classes.handler.Handler');

class ZendSearchAdminHandler extends Handler {
	/**
	 * Constructor
	 */
	function ZendSearchAdminHandler() {
		parent::Handler();

		$this->addCheck(new HandlerValidatorRoles($this, true, null, null, array(ROLE_ID_SITE_ADMIN)));
	}
	
	/**
	 * Get the Zend Search Plugin object.
	 */
	function &getPlugin() {
		$plugin =& PluginRegistry::getPlugin('generic', ZEND_SEARCH_PLUGIN_NAME);
		return $plugin;
	}

	/**
	 * Administer the search form.
	 */
	function index($args, &$request) {
		$this->validate();
		$this->setupTemplate($request, true);
		$templateMgr = TemplateManager::getManager($request);
		$plugin = $this->getPlugin();

		$rangeInfo = $this->getRangeInfo($request, 'searchFormElements');
		$searchFormElementDao = DAORegistry::getDAO('SearchFormElementDAO');
		$searchFormElements = $searchFormElementDao->getSearchFormElements($rangeInfo);

		$templateMgr->assign_by_ref('searchFormElements', $searchFormElements);
		$templateMgr->display($plugin->getTemplatePath() . 'searchForm.tpl');
	}

	/**
	 * Display the settings form
	 */
	function settings($args, &$request) {
		$this->validate();
		$this->setupTemplate($request, true);

		$plugin =& $this->getPlugin();
		$plugin->import('ZendSearchSettingsForm');

		$zendSearchSettingsForm = new ZendSearchSettingsForm(ZEND_SEARCH_PLUGIN_NAME);
		if ($zendSearchSettingsForm->isLocaleResubmit()) {
			$zendSearchSettingsForm->readInputData();
		} else {
			$zendSearchSettingsForm->initData();
		}
		$zendSearchSettingsForm->display();
	}

	/**
	 * Save changes to plugin settings.
	 */
	function saveSettings($args, &$request) {
		$this->validate();

		$plugin =& $this->getPlugin();
		$plugin->import('ZendSearchSettingsForm');

		$zendSearchSettingsForm = new ZendSearchSettingsForm(ZEND_SEARCH_PLUGIN_NAME);
		$zendSearchSettingsForm->initData();
		$zendSearchSettingsForm->readInputData();

		if ($zendSearchSettingsForm->validate()) {
			$zendSearchSettingsForm->execute();
			$request->redirect(null, 'index');
		} else {
			$this->setupTemplate($request, true);
			$zendSearchSettingsForm->display();
		}
	}

	/**
	 * Display form to create a new search form element.
	 */
	function createSearchFormElement($args, &$request) {
		$this->editSearchFormElement($args, $request);
	}

	/**
	 * Display form to create/edit a search form element.
	 * @param $args array optional, if set the first parameter is the ID of the search form element to edit
	 */
	function editSearchFormElement($args, &$request) {
		$this->validate();
		$this->setupTemplate($request, true);

		$plugin =& $this->getPlugin();
		$plugin->import('SearchFormElementForm');

		$searchFormElementForm = new SearchFormElementForm($plugin->getName(), !isset($args) || empty($args) ? null : (int) $args[0]);
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
	function updateSearchFormElement($args, &$request) {
		$this->validate();

		$plugin =& $this->getPlugin();
		$plugin->import('SearchFormElementForm');

		$searchFormElementId = (int) $request->getUserVar('searchFormElementId');

		$searchFormElementForm = new SearchFormElementForm($plugin->getName(), $searchFormElementId);
		$searchFormElementForm->initData();
		$searchFormElementForm->readInputData();

		if ($searchFormElementForm->validate()) {
			$searchFormElementForm->execute();
			$request->redirect(null, 'index');
		} else {
			$this->setupTemplate($request, true);
			$searchFormElementForm->display();
		}
	}

	/**
	 * Delete a search form element.
	 * @param $args array first parameter is the ID of the search form element to delete
	 */
	function deleteSearchFormElement($args, &$request) {
		$this->validate();

		$searchFormElementDao =& DAORegistry::getDAO('SearchFormElementDAO');

		// Disable timeout, as this operation may take
		// a long time.
		@set_time_limit(0);

		if (isset($args) && isset($args[0])) {
			$searchFormElementId = $args[0];
			$searchFormElementDao->deleteSearchFormElementById($searchFormElementId);
		}

		$request->redirect(null, 'index', null, array('searchFormElementPage' => $request->getUserVar('searchFormElementPage')));
	}

	/**
	 * Setup common template variables.
	 */
	function setupTemplate($request) {
		parent::setupTemplate($request);
		$templateMgr =& TemplateManager::getManager($request);
		$templateMgr->assign('pageHierarchy', array(
			array($request->url('admin'), 'admin.settings.administration')
		));
	}
}

?>
