<?php

/**
 * @defgroup pages_login
 */

/**
 * @file pages/login/index.php
 *
 * Copyright (c) 2005-2012 John Willinsky and Alec Smecher
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Handle login/logout requests.
 *
 * @ingroup pages_login
 */

// $Id$


switch ($op) {
	case 'index':
	case 'implicitAuthLogin':
	case 'implicitAuthReturn':
	case 'signIn':
	case 'signOut':
	case 'lostPassword':
	case 'requestResetPassword':
	case 'resetPassword':
	case 'changePassword':
	case 'savePassword':
		define('HANDLER_CLASS', 'PKPLoginHandler');
		import('lib.pkp.pages.login.PKPLoginHandler');
		break;
}

?>
