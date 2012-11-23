<?php

/**
 * @file classes/controllers/grid/languages/LanguageGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LanguageGridHandler
 * @ingroup classes_controllers_grid_languages
 *
 * @brief Handle language grid requests.
 */

import('lib.pkp.classes.controllers.grid.languages.PKPLanguageGridHandler');

class LanguageGridHandler extends PKPLanguageGridHandler {
	/**
	 * Constructor
	 */
	function LanguageGridHandler() {
		parent::PKPLanguageGridHandler();
		$this->addRoleAssignment(array(
			ROLE_ID_SITE_ADMIN),
			array('saveLanguageSetting', 'setContextPrimaryLocale'));
	}
}

?>
