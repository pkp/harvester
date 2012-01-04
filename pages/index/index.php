<?php

/**
 * @file pages/index/index.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Handle site index requests.
 *
 * @package pages.index
 *
 */

// $Id$


switch ($op) {
	case 'index':
	case 'setLocale':
		define('HANDLER_CLASS', 'IndexHandler');
		import('pages.index.IndexHandler');
		break;
}

?>
