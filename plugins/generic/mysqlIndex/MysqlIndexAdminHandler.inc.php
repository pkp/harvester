<?php

/**
 * @file MysqlIndexAdminHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MysqlIndexAdminHandler
 * @ingroup plugins_generic_mysqlIndex
 *
 * @brief Handle requests for search administration functions
 */



import('classes.handler.Handler');

class MysqlIndexAdminHandler extends Handler {
	function MysqlIndexAdminHandler() {
		parent::Handler();
		$this->addCheck(new HandlerValidatorRoles($this, true, null, null, array(ROLE_ID_SITE_ADMIN)));
	}

	/**
	 * Get the Zend Search Plugin object.
	 */
	function &getPlugin() {
		$plugin =& PluginRegistry::getPlugin('generic', MYSQL_PLUGIN_NAME);
		return $plugin;
	}

	/**
	 * Display indexing information.
	 */
	function adminCrosswalks($args, &$request) {
		parent::validate();
		$this->setupTemplate($request, false);

		$rangeInfo = $this->getRangeInfo($request, 'crosswalks');

		$crosswalkDao = DAORegistry::getDAO('CrosswalkDAO');
		$crosswalks = $crosswalkDao->getCrosswalks($rangeInfo);

		$templateMgr = TemplateManager::getManager();
		$templateMgr->assign_by_ref('crosswalks', $crosswalks);

		$plugin = $this->getPlugin();
		$templateMgr->display($plugin->getTemplatePath() . 'crosswalks.tpl');
	}

	function editCrosswalk($args, &$request) {
		parent::validate();
		$this->setupTemplate($request, true);

		$plugin =& $this->getPlugin();
		$plugin->import('CrosswalkForm');

		$crosswalkForm = new CrosswalkForm(MYSQL_PLUGIN_NAME, $request->getUserVar('crosswalkId'));
		$crosswalkForm->initData();
		$crosswalkForm->display();
	}

	/**
	 * Save changes to a searchable field's settings.
	 */
	function updateCrosswalk($args, &$request) {
		parent::validate();

		$plugin =& $this->getPlugin();
		$plugin->import('CrosswalkForm');

		$crosswalkForm = new CrosswalkForm(MYSQL_PLUGIN_NAME, $request->getUserVar('crosswalkId'));
		$crosswalkForm->initData();
		$crosswalkForm->readInputData();

		if ($crosswalkForm->validate()) {
			$crosswalkForm->execute();
			$request->redirect(null, 'adminCrosswalks');

		} else {
			$this->setupTemplate($request, true);
			$crosswalkForm->display();
		}
	}

	function deleteCrosswalk($args, &$request) {
		parent::validate();

		$crosswalkDao = DAORegistry::getDAO('CrosswalkDAO');
		if (isset($args) && !empty($args) && !empty($args[0])) {
			$crosswalkId = $args[0];
			$crosswalkDao->deleteCrosswalkById($crosswalkId);
		}
		$request->redirect(null, 'adminCrosswalks');
	}

	function createCrosswalk($args, &$request) {
		$this->editCrosswalk($args, $request);
	}

	/**
	 * Reset crosswalks to the installation default.
	 */
	function resetCrosswalks($args, &$request) {
		parent::validate();

		$crosswalkDao = DAORegistry::getDAO('CrosswalkDAO');
		$crosswalks =& $crosswalkDao->getCrosswalks();
		while ($crosswalk =& $crosswalks->next()) {
			$crosswalkDao->deleteCrosswalk($crosswalk);
			unset($crosswalk);
		}
		$crosswalkDao->installCrosswalks('registry/crosswalks.xml');
		$request->redirect(null, 'adminCrosswalks');
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
	function setupTemplate($request) {
		parent::setupTemplate($request);
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageHierarchy', array(
			array($request->url('admin'), 'admin.settings.administration')
		));
	}
}

?>
