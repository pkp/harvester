<?php

/**
 * @file pages/rt/index.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Handle Reading Tools requests.
 *
 * @package pages.rt
 *
 */

// $Id$


switch ($op) {
	case 'context':
		define('HANDLER_CLASS', 'RTHandler');
		import('pages.rt.RTHandler');
		break;
}

?>
