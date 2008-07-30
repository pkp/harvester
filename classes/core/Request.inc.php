<?php

/**
 * @file classes/core/Request.inc.php
 *
 * Copyright (c) 2005-2008 John Willinsky and Alec Smecher
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Request
 * @ingroup core
 *
 * @brief Class providing operations associated with HTTP requests.
 * Requests are assumed to be in the format http://host.tld/index.php/<page_name>/<operation_name>/<arguments...>
 * <journal_id> is assumed to be "index" for top-level site requests.
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
	 * Build a URL into Harvester2.
	 */
	function url($page = null, $op = null, $path = null, $params = null, $anchor = null, $escape = false) {
		$pathInfoDisabled = !Request::isPathInfoEnabled();

		$prefix = '?';
		$amp = $escape?'&amp;':'&';

		// Establish defaults for page and op
		$defaultPage = Request::getRequestedPage();
		$defaultOp = Request::getRequestedOp();

		// If a page has been specified, don't supply a default op.
		if ($page) {
			$page = rawurlencode($page);
			$defaultOp = null;
		} else {
			$page = $defaultPage;
		}

		// Encode the op.
		if ($op) $op = rawurlencode($op);
		else $op = $defaultOp;

		// Process anchor
		if (!empty($anchor)) $anchor = '#' . rawurlencode($anchor);
		else $anchor = '';

		if (!empty($path)) {
			if (is_array($path)) $path = array_map('rawurlencode', $path);
			else $path = array(rawurlencode($path));
			if (!$page) $page = 'index';
			if (!$op) $op = 'index';
		}

		$pathString = '';
		if ($pathInfoDisabled) {
			$baseParams = '';
			if (!empty($page)) {
				$baseParams .= $prefix . "page=$page";
				$prefix = $amp;
				if (!empty($op)) {
					$baseParams .= $amp . "op=$op";
				}
			}
			if (!empty($path)) {
				$pathString = $prefix . 'path%5B%5D=' . implode($amp . 'path%5B%5D=', $path);
				$prefix = $amp;
			}
		} else {
			if (!empty($path)) $pathString = '/' . implode('/', $path);
			$baseParams = '';
			if (!empty($page)) {
				$baseParams .= "/$page";
				if (!empty($op)) {
					$baseParams .= "/$op";
				}
			}
		}

		// Process additional parameters
		$additionalParams = '';
		if (!empty($params)) foreach ($params as $key => $value) {
			if (is_array($value)) foreach($value as $element) {
				$additionalParams .= $prefix . $key . '%5B%5D=' . rawurlencode($element);
				$prefix = $amp;
			} else {
				$additionalParams .= $prefix . $key . '=' . rawurlencode($value);
				$prefix = $amp;
			}
		}

		return Request::getIndexUrl() . $baseParams . $pathString . $additionalParams . $anchor;
	}
}

?>
