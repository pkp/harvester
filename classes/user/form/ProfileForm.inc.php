<?php

/**
 * @file classes/user/form/ProfileForm.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProfileForm
 * @ingroup user_form
 *
 * @brief Form to edit user profile.
 */

import('lib.pkp.classes.user.form.PKPProfileForm');

class ProfileForm extends PKPProfileForm {
	/**
	 * Constructor.
	 */
	function ProfileForm($user) {
		parent::PKPProfileForm('user/profile.tpl', $user);
	}
}

?>
