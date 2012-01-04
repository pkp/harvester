<?php

/**
 * @file pages/record/index.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Handle requests for record functions. 
 *
 * @package pages.record
 *
 */

// $Id$


switch ($op) {
	case 'index':
	case 'view':
		define('HANDLER_CLASS', 'RecordHandler');
		import('pages.record.RecordHandler');
		break;
}

?>
