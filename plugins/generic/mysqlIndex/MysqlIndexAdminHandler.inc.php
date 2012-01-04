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

// $Id$


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
	function adminCrosswalks() {
		parent::validate();
		MysqlIndexAdminHandler::setupTemplate(false);

		$rangeInfo = PKPHandler::getRangeInfo('crosswalks');

		$crosswalkDao =& DAORegistry::getDAO('CrosswalkDAO');
		$crosswalks =& $crosswalkDao->getCrosswalks($rangeInfo);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('crosswalks', $crosswalks);

		$plugin =& MysqlIndexAdminHandler::getPlugin();
		$templateMgr->display($plugin->getTemplatePath() . 'crosswalks.tpl');
	}

	function editCrosswalk() {
		parent::validate();
		MysqlIndexAdminHandler::setupTemplate(true);

		$plugin =& MysqlIndexAdminHandler::getPlugin();
		$plugin->import('CrosswalkForm');

		$crosswalkForm = new CrosswalkForm(MYSQL_PLUGIN_NAME, Request::getUserVar('crosswalkId'));
		$crosswalkForm->initData();
		$crosswalkForm->display();
	}

	/**
	 * Save changes to a searchable field's settings.
	 */
	function updateCrosswalk() {
		parent::validate();

		$plugin =& MysqlIndexAdminHandler::getPlugin();
		$plugin->import('CrosswalkForm');

		$crosswalkForm = new CrosswalkForm(MYSQL_PLUGIN_NAME, Request::getUserVar('crosswalkId'));
		$crosswalkForm->initData();
		$crosswalkForm->readInputData();

		if ($crosswalkForm->validate()) {
			$crosswalkForm->execute();
			Request::redirect(null, 'adminCrosswalks');

		} else {
			MysqlIndexAdminHandler::setupTemplate(true);
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
		Request::redirect(null, 'adminCrosswalks');
	}

	function createCrosswalk() {
		MysqlIndexAdminHandler::editCrosswalk();
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
		Request::redirect(null, 'adminCrosswalks');
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
