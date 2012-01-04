<?php

/**
 * @file pages/about/index.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Handle requests for about functions. 
 *
 * @package pages.about
 *
 */

// $Id$


switch ($op) {
	case 'index':
	case 'contact':
	case 'harvester':
		define('HANDLER_CLASS', 'AboutHandler');
		import('pages.about.AboutHandler');
		break;
}

?>
