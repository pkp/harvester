<?php

/**
 * @file classes/core/Request.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Request
 * @ingroup core
 *
 * @brief Class providing operations associated with HTTP requests.
 * Requests are assumed to be in the format http://host.tld/index.php/<page_name>/<operation_name>/<arguments...>
 */

// $Id$


import('lib.pkp.classes.core.PKPRequest');

class Request extends PKPRequest {
	/**
	 * Redirect to the specified page within Open Harvesting Systems. Shorthand for a common call to Request::redirect(Request::url(...)).
	 * @param $page string The name of the op to redirect to.
	 * @param $op string optional The name of the op to redirect to.
	 * @param $path mixed string or array containing path info for redirect.
	 * @param $params array Map of name => value pairs for additional parameters
	 * @param $anchor string Name of desired anchor on the target page
	 */
	function redirect($page = null, $op = null, $path = null, $params = null, $anchor = null) {
		$_this =& PKPRequest::_checkThis();
		$_this->redirectUrl($_this->url($page, $op, $path, $params, $anchor));
	}

	/**
	 * Deprecated
	 * @see PKPPageRouter::url()
	 */
	function url($page = null,
			$op = null, $path = null, $params = null, $anchor = null, $escape = false) {
		$_this =& PKPRequest::_checkThis();
		return $_this->_delegateToRouter('url', null, $page, $op, $path,
			$params, $anchor, $escape);
	}

	/**
	 * Deprecated
	 * @see PageRouter::redirectHome()
	 */
	function redirectHome() {
		$_this =& PKPRequest::_checkThis();
		return $_this->_delegateToRouter('redirectHome');
	}
}

?>
