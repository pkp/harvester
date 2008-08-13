<?php

/**
 * @file classes/security/RoleDAO.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RoleDAO
 * @ingroup security
 * @see Role
 *
 * @brief Operations for retrieving and modifying Role objects.
 */

// $Id$


import('security.Role');

class RoleDAO extends DAO {
	/**
	 * Constructor.
	 */
	function RoleDAO() {
		parent::DAO();
		$this->userDao = &DAORegistry::getDAO('UserDAO');
	}

	/**
	 * Retrieve a role.
	 * @param $userId int
	 * @param $roleId int
	 * @return Role
	 */
	function &getRole($userId, $roleId) {
		$result = &$this->retrieve(
			'SELECT * FROM roles WHERE user_id = ? AND role_id = ?',
			array(
				(int) $userId,
				(int) $roleId
			)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnRoleFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Internal function to return a Role object from a row.
	 * @param $row array
	 * @return Role
	 */
	function &_returnRoleFromRow(&$row) {
		$role = &new Role();
		$role->setUserId($row['user_id']);
		$role->setRoleId($row['role_id']);

		HookRegistry::call('RoleDAO::_returnRoleFromRow', array(&$role, &$row));

		return $role;
	}

	/**
	 * Insert a new role.
	 * @param $role Role
	 */
	function insertRole(&$role) {
		return $this->update(
			'INSERT INTO roles
				(user_id, role_id)
				VALUES
				(?, ?)',
			array(
				(int) $role->getUserId(),
				(int) $role->getRoleId()
			)
		);
	}

	/**
	 * Delete a role.
	 * @param $role Role
	 */
	function deleteRole(&$role) {
		return $this->update(
			'DELETE FROM roles WHERE user_id = ? AND role_id = ?',
			array(
				(int) $role->getUserId(),
				(int) $role->getRoleId()
			)
		);
	}

	/**
	 * Retrieve a list of all roles for a specified user.
	 * @param $userId int
	 * @return array matching Roles
	 */
	function &getRolesByUserId($userId) {
		$roles = array();

		$result = &$this->retrieve(
			'SELECT * FROM roles WHERE user_id = ?',
			(int) $userId
		);

		while (!$result->EOF) {
			$roles[] = &$this->_returnRoleFromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $roles;
	}

	/**
	 * Check if a role exists.
	 * @param $userId int
	 * @param $roleId int
	 * @return boolean
	 */
	function roleExists($userId, $roleId) {
		$result = &$this->retrieve(
			'SELECT COUNT(*) FROM roles WHERE user_id = ? AND role_id = ?', array((int) $userId, (int) $roleId)
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get the i18n key name associated with the specified role.
	 * @param $roleId int
	 * @param $plural boolean get the plural form of the name
	 * @return string
	 */
	function getRoleName($roleId, $plural = false) {
		switch ($roleId) {
			case ROLE_ID_SITE_ADMIN:
				return 'user.role.siteAdmin' . ($plural ? 's' : '');
			case ROLE_ID_SUBMITTER:
				return 'user.role.submitter' . ($plural ? 's' : '');
			default:
				return '';
		}
	}

	/**
	 * Get the URL path associated with the specified role's operations.
	 * @param $roleId int
	 * @return string
	 */
	function getRolePath($roleId) {
		switch ($roleId) {
			case ROLE_ID_SITE_ADMIN:
				return 'admin';
			case ROLE_ID_SUBMITTER:
				return 'submitter';
			default:
				return '';
		}
	}

	/**
	 * Get a role's ID based on its path.
	 * @param $rolePath string
	 * @return int
	 */
	function getRoleIdFromPath($rolePath) {
		switch ($rolePath) {
			case 'admin':
				return ROLE_ID_SITE_ADMIN;
			default:
				return null;
		}
	}
}

?>
