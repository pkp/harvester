<?php

/**
 * @file paes/about/AboutHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.about
 * @class AboutHandler
 *
 * Handle requests for the About page.
 *
 */



import('classes.handler.Handler');

class AboutHandler extends Handler {

	/**
	 * Display about index page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, &$request) {
		$this->setupTemplate();
		$this->validate();

		$templateMgr =& TemplateManager::getManager($request);

		$site =& $request->getSite();
		$templateMgr->assign('about', $site->getLocalizedSetting('about'));

		$templateMgr->display('about/index.tpl');
	}


	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($request, $subclass = false) {
		parent::setupTemplate($request);
		$this->validate();

		$templateMgr =& TemplateManager::getManager($request);
		if ($subclass) $templateMgr->assign('pageHierarchy', array(array('about', 'navigation.about')));
	}

	/**
	 * Display contact page.
	 * @param $args
	 * @param $request
	 */
	function contact($args, &$request) {
		$this->validate();

		$this->setupTemplate($request, true);

		$site =& $request->getSite();

		$templateMgr =& TemplateManager::getManager($request);
		$templateMgr->display('about/contact.tpl');
	}

	/**
	 * Display about the harvester page.
	 */
	function harvester($args, &$request) {
		$this->validate();

		$this->setupTemplate($request, true);

		$templateMgr =& TemplateManager::getManager($request);
		$templateMgr->display('about/harvester.tpl');
	}
}

?>
