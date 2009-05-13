<?php

/**
 * @file classes/core/Request.inc.php
 *
 * Copyright (c) 2005-2009 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Request
 * @ingroup core
 *
 * @brief Class providing operations associated with HTTP requests.
 * Requests are assumed to be in the format http://host.tld/index.php/<page_name>/<operation_name>/<arguments...>
 */

// $Id$


import('core.PKPRequest');

class Request extends PKPRequest {
	/**
	 * Redirect to the specified page within Harvester2. Shorthand for a common call to Request::redirect(Request::url(...)).
	 * @param $page string The name of the op to redirect to.
	 * @param $op string optional The name of the op to redirect to.
	 * @param $path mixed string or array containing path info for redirect.
	 * @param $params array Map of name => value pairs for additional parameters
	 * @param $anchor string Name of desired anchor on the target page
	 */
	function redirect($page = null, $op = null, $path = null, $params = null, $anchor = null) {
		Request::redirectUrl(Request::url($page, $op, $path, $params, $anchor));
	}
	
	/**
	 * Redirect to user home page (or the role home page if the user has one role).
	 */
	function redirectHome() {
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$user = Request::getUser();
		$userId = $user->getId();

		$roles =& $roleDao->getRolesByUserId($userId);
		if(count($roles) == 1) {
			$role = array_shift($roles);
			Request::redirect($role->getRolePath());
		} else {
			Request::redirect('user');
		}
	}
	
	/**
	 * Build a URL into Harvester2.
	 */
	function url($page = null,
			$op = null, $path = null, $params = null, $anchor = null, $escape = false) {
		return parent::url(null, $page, $op, $path, $params, $anchor, $escape);
	}
}

?>
