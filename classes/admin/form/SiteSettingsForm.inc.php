<?php

/**
 * @file classes/admin/form/SiteSettingsForm.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SiteSettingsForm
 * @ingroup admin_form
 * @see PKPSiteSettingsForm
 *
 * @brief Form to edit site settings.
 */

// $Id$


import('admin.form.PKPSiteSettingsForm');

class SiteSettingsForm extends PKPSiteSettingsForm {
	/**
	 * Constructor.
	 */
	function SiteSettingsForm() {
		parent::PKPSiteSettingsForm();
	}

	function initData() {
		$site =& Request::getSite();
		parent::initData();
		$this->_data['enableSubmit'] = $site->getSetting('enableSubmit');
	}

	function readInputData() {
		parent::readInputData();
		$this->readUserVars(array('enableSubmit'));
	}

	function execute() {
		if (parent::execute()) {
			$site =& Request::getSite();
			$site->updateSetting('enableSubmit', $this->getData('enableSubmit')?1:0);
		}
		return false;
	}
}

?>
