<?php

/**
 * @file pages/browse/index.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Handle requests for browse functions. 
 *
 * @package pages.browse
 *
 */

// $Id$


switch ($op) {
	case 'index':
	case 'archiveInfo':
		define('HANDLER_CLASS', 'BrowseHandler');
		import('pages.browse.BrowseHandler');
		break;
}

?>
