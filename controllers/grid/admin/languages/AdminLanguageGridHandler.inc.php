<?php

/**
 * @file controllers/grid/admin/languages/AdminLanguageGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminLanguageGridHandler
 * @ingroup controllers_grid_admin_languages
 *
 * @brief Handle administrative language grid requests. If in single context (e.g.
 * press) installation, this grid can also handle language management requests.
 * See _canManage().
 */

import('lib.pkp.controllers.grid.admin.languages.PKPAdminLanguageGridHandler');

class AdminLanguageGridHandler extends PKPAdminLanguageGridHandler {
	/**
	 * Constructor
	 */
	function AdminLanguageGridHandler() {
		parent::PKPAdminLanguageGridHandler();
	}

	/**
	 * Helper function to update locale settings in all
	 * installed conferences, based on site locale settings.
	 * @param $request object
	 */
	function _updateContextLocaleSettings(&$request) {
	}

	/**
	 * This grid can also present management functions
	 * if the conditions above are true.
	 * @param $request Request
	 * @return boolean
	 */
	function _canManage($request) {
		return false;
	}
}

?>
