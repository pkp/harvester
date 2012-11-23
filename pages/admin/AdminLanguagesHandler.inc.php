<?php

/**
 * @file pages/admin/AdminLanguagesHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.admin
 * @class AdminLanguagesHandler
 *
 * Handle requests for changing site language settings. 
 *
 */


import('pages.admin.AdminHandler');

class AdminLanguagesHandler extends AdminHandler {

	/**
	 * Display form to modify site language settings.
	 * @param $args array
	 * @param $request object
	 */
	function languages($args, &$request) {
		$this->validate();
		$this->setupTemplate($request, true);

		$templateMgr =& TemplateManager::getManager($request);
		$templateMgr->display('admin/languages.tpl');
	}
}

?>
