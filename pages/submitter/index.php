<?php

/**
 * @file pages/submitter/index.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Handle requests for add archive functions.
 *
 * @package pages.add
 *
 */

// $Id$


switch ($op) {
	case 'index':
	case 'createArchive':
	case 'editArchive':
	case 'updateArchive':
	case 'deleteArchive':
	case 'plugin':
		define('HANDLER_CLASS', 'SubmitterHandler');
		import('pages.submitter.SubmitterHandler');
		break;
}

?>
