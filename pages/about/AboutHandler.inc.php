<?php

/**
 * @file AboutHandler.inc.php
 *
 * Copyright (c) 2005-2007 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.about
 * @class AboutHandler
 *
 * Handle requests for the About page.
 *
 * $Id$
 */

class AboutHandler extends Handler {

	/**
	 * Display about index page.
	 */
	function index() {
		parent::validate();
		
		$templateMgr = &TemplateManager::getManager();
				
		$site = &Request::getSite();
		$about = $site->getAbout();
		$templateMgr->assign('about', $about);

		$templateMgr->display('about/index.tpl');
	}
	

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		parent::validate();
		
		$templateMgr = &TemplateManager::getManager();
		if ($subclass) $templateMgr->assign('pageHierarchy', array(array('about', 'navigation.about')));
	}
	
	/**
	 * Display contact page.
	 */
	function contact() {
		parent::validate(true);
		
		AboutHandler::setupTemplate(true);
		
		$site = &Request::getSite();
	
		$templateMgr = &TemplateManager::getManager();
		$templateMgr->display('about/contact.tpl');
	}
	
	/**
	 * Display about the harvester page.
	 */
	function harvester() {
		parent::validate();
		
		AboutHandler::setupTemplate(true);
		
		$templateMgr = &TemplateManager::getManager();
		$templateMgr->display('about/harvester.tpl');
	}
	

}

?>
