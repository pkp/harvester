<?php

/**
 * @file classes/admin/form/SiteSettingsForm.inc.php
 *
 * Copyright (c) 2005-2008 John Willinsky and Alec Smecher
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
}

?>
