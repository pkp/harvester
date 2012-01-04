<?php

/**
 * @file classes/user/User.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class User
 * @ingroup user
 * @see UserDAO
 *
 * @brief Basic class describing users existing in the system.
 */

// $Id$


import('lib.pkp.classes.user.PKPUser');

class User extends PKPUser {

	function User() {
		parent::PKPUser();
	}

	/**
	 * Retrieve a user setting value.
	 * @param $name
	 * @return mixed
	 */
	function &getSetting($name) {
		$userSettingsDao =& DAORegistry::getDAO('UserSettingsDAO');
		$setting =& $userSettingsDao->getSetting($this->getData('userId'), $name);
		return $setting;
	}

	/**
	 * Set a user setting value.
	 * @param $name string
	 * @param $value mixed
	 * @param $type string optional
	 */
	function updateSetting($name, $value, $type = null) {
		$userSettingsDao =& DAORegistry::getDAO('UserSettingsDAO');
		return $userSettingsDao->updateSetting($this->getData('userId'), $name, $value, $type);
	}
}

?>
