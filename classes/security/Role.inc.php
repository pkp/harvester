<?php

/**
 * @file classes/security/Role.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Role
 * @ingroup security
 * @see RoleDAO
 *
 * @brief Describes user roles within the system and the associated permissions.
 */

import('lib.pkp.classes.security.PKPRole');

/** ID codes for all user roles */
define('ROLE_ID_SUBMITTER',		0x00000002);

class Role extends PKPRole {
	/**
	 * Constructor.
	 * @param $roleId for this role.  Default to null for backwards
	 * 	compatibility
	 */
	function Role($roleId = null) {
		parent::PKPRole($roleId);
	}

	/**
	 * Get the i18n key name associated with this role.
	 * @return String the key
	 */
	function getRoleName() {
		switch ($this->getId()) {
			case ROLE_ID_SUBMITTER:
				return 'user.role.submitter' . ($plural ? 's' : '');
			default:
				return parent::getRoleName($plural);
		}
	}

	/**
	 * Get the URL path associated with this role's operations.
	 * @return String the path
	 */
	function getPath() {
		switch ($this->getId()) {
			case ROLE_ID_SUBMITTER:
				return ROLE_PATH_SUBMITTER;
			default:
				return parent::getPath();
		}
	}

	//
	// Get/set methods
	//

	/**
	 * Get user ID associated with role.
	 * @return int
	 */
	function getUserId() {
		return $this->getData('userId');
	}

	/**
	 * Set user ID associated with role.
	 * @param $userId int
	 */
	function setUserId($userId) {
		return $this->setData('userId', $userId);
	}
}

?>
