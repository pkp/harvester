<?php

/**
 * @file paes/about/AboutHandler.inc.php
 *
 * Copyright (c) 2005-2009 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.about
 * @class AboutHandler
 *
 * Handle requests for the About page.
 *
 */

// $Id$


import('handler.Handler');

class AboutHandler extends Handler {

	/**
	 * Display about index page.
	 */
	function index() {
		$this->setupTemplate();
		$this->validate();

		$templateMgr =& TemplateManager::getManager();

		$site =& Request::getSite();
		$templateMgr->assign('about', $site->getLocalizedSetting('about'));

		$templateMgr->display('about/index.tpl');
	}


	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		parent::setupTemplate();
		$this->validate();

		$templateMgr =& TemplateManager::getManager();
		if ($subclass) $templateMgr->assign('pageHierarchy', array(array('about', 'navigation.about')));
	}

	/**
	 * Display contact page.
	 */
	function contact() {
		$this->validate();

		$this->setupTemplate(true);

		$site =& Request::getSite();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->display('about/contact.tpl');
	}

	/**
	 * Display about the harvester page.
	 */
	function harvester() {
		$this->validate();

		$this->setupTemplate(true);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->display('about/harvester.tpl');
	}
}

?>
