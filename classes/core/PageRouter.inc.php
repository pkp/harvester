<?php

/**
 * @file classes/core/PageRouter.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file dHarvester/COPYING.
 *
 * @class PageRouter
 * @ingroup core
 *
 * @brief Class providing Harvester-specific page routing.
 *
 * FIXME: add cacheable pages
 */

// $Id$


import('lib.pkp.classes.core.PKPPageRouter');

class PageRouter extends PKPPageRouter {
	/**
	 * Redirect to user home page (or the role home page if the user has one role).
	 * @param $request PKPRequest the request to be routed
	 */
	function redirectHome(&$request) {
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$user = $request->getUser();
		$userId = $user->getId();

		$roles =& $roleDao->getRolesByUserId($userId);
		if(count($roles) == 1) {
			$role = array_shift($roles);
			$request->redirect($role->getRolePath());
		} else {
			$request->redirect('user');
		}
	}
}

?>
