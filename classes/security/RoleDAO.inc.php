<?php

/**
 * @file classes/security/RoleDAO.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RoleDAO
 * @ingroup security
 * @see Role
 *
 * @brief Operations for retrieving and modifying Role objects.
 */


import('classes.security.Role');

class RoleDAO extends DAO {
	/**
	 * Constructor.
	 */
	function RoleDAO() {
		parent::DAO();
		$this->userDao =& DAORegistry::getDAO('UserDAO');
	}

	/**
	 * Retrieve a role.
	 * @param $userId int
	 * @param $roleId int
	 * @return Role
	 */
	function &getRole($userId, $roleId) {
		$result =& $this->retrieve(
			'SELECT * FROM roles WHERE user_id = ? AND role_id = ?',
			array(
				(int) $userId,
				(int) $roleId
			)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnRoleFromRow($result->GetRowAssoc(false));
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
		$role = new Role();
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
		return $this->deleteRoleByUserId($role->getUserId(), $role->getRoleId());
	}

	/**
	 * Delete a role.
	 * @param $userId int
	 * @param $roleId int optional
	 */
	function deleteRoleByUserId($userId, $roleId = null) {
		$params = array((int) $userId);
		$roleId = (int) $roleId;
		if ($roleId) $params[] = $roleId;
		return $this->update(
			'DELETE FROM roles WHERE user_id = ?' . ($roleId?' AND role_id = ?':''),
			$params
		);
	}

	/**
	 * Retrieve a list of all roles for a specified user.
	 * @param $userId int
	 * @return array matching Roles
	 */
	function &getRolesByUserId($userId) {
		$roles = array();

		$result =& $this->retrieve(
			'SELECT * FROM roles WHERE user_id = ?',
			(int) $userId
		);

		while (!$result->EOF) {
			$roles[] =& $this->_returnRoleFromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $roles;
	}

	/**
	* Return an array of objects corresponding to the roles a given user has,
	* grouped by context id.
	* @param $userId int
	* @return array
	*/
	function &getByUserIdGroupedByContext($userId) {
		$roles = $this->getRolesByUserId($userId);

		$groupedRoles = array();
		foreach ($roles as $role) {
			$groupedRoles[CONTEXT_ID_NONE][$role->getRoleId()] =& $role;
			unset($role);
		}

		return $groupedRoles;
	}

	/**
	 * Retrieve a list of users in a specified role.
	 * @param $roleId int optional (can leave as null to get all users in journal)
	 * @param $searchType int optional, which field to search
	 * @param $search string optional, string to match
	 * @param $searchMatch string optional, type of match ('is' vs. 'contains' vs. 'startsWith')
	 * @param $dbResultRange object DBRangeInfo object describing range of results to return
	 * @return array matching Users
	 */
	function &getUsersByRoleId($roleId = null, $searchType = null, $search = null, $searchMatch = null, $dbResultRange = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
		$users = array();

		$paramArray = array('interests');
		if ($roleId) $paramArray[] = (int) $roleId;

		$searchSql = '';

		$searchTypeMap = array(
			USER_FIELD_FIRSTNAME => 'u.first_name',
			USER_FIELD_LASTNAME => 'u.last_name',
			USER_FIELD_USERNAME => 'u.username',
			USER_FIELD_EMAIL => 'u.email',
			USER_FIELD_INTERESTS => 's.setting_value'
		);

		if (isset($search) && isset($searchTypeMap[$searchType])) {
			$fieldName = $searchTypeMap[$searchType];
			switch ($searchMatch) {
				case 'is':
					$searchSql = "AND LOWER($fieldName) = LOWER(?)";
					$paramArray[] = $search;
					break;
				case 'contains':
					$searchSql = "AND LOWER($fieldName) LIKE LOWER(?)";
					$paramArray[] = '%' . $search . '%';
					break;
				case 'startsWith':
					$searchSql = "AND LOWER($fieldName) LIKE LOWER(?)";
					$paramArray[] = $search . '%';
					break;
			}
		} elseif (isset($search)) switch ($searchType) {
			case USER_FIELD_USERID:
				$searchSql = 'AND u.user_id=?';
				$paramArray[] = $search;
				break;
			case USER_FIELD_INITIAL:
				$searchSql = 'AND LOWER(u.last_name) LIKE LOWER(?)';
				$paramArray[] = $search . '%';
				break;
		}

		$searchSql .= ($sortBy?(' ORDER BY ' . $this->getSortMapping($sortBy) . ' ' . $this->getDirectionMapping($sortDirection)) : '');

		$result =& $this->retrieveRange(
			'SELECT DISTINCT u.* FROM users AS u LEFT JOIN user_settings s ON (u.user_id = s.user_id AND s.setting_name = ?), roles AS r WHERE u.user_id = r.user_id ' . ($roleId?'AND r.role_id = ?':'') . ' ' . $searchSql,
			$paramArray,
			$dbResultRange
		);

		$returner = new DAOResultFactory($result, $this->userDao, '_returnUserFromRowWithData');
		return $returner;
	}

	/**
	 * Validation check to see if a user belongs to any group that has a given role
	 * DEPRECATE: keeping around because HandlerValidatorRoles in pkp-lib uses
	 * until we port user groups to OxS
	 * Check if a role exists.
	 * @param $userId int
	 * @param $roleId int
	 * @return boolean
	 */
	function roleExists($userId, $roleId) {
		if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
		return $this->userHasRole($userId, $roleId);
	}

	/**
	 * Validation check to see if a user belongs to any group that has a given role
	 * @param $userId int
	 * @param $roleId int
	 * @return boolean
	 */
	function userHasRole($userId, $roleId) {
		$result =& $this->retrieve(
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
			case 'submitter':
				return ROLE_ID_SUBMITTER;
			default:
				return null;
		}
	}

	/**
	 * Map a column heading value to a database value for sorting
	 * @param string
	 * @return string
	 */
	function getSortMapping($heading) {
		switch ($heading) {
			case 'username': return 'u.username';
			case 'name': return 'u.last_name';
			case 'email': return 'u.email';
			default: return null;
		}
	}

}

?>
