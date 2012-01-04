<?php

/**
 * @file pages/admin/index.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Handle requests for site administration functions. 
 *
 * @package pages.admin
 *
 * $Id$
 */

switch ($op) {
	//
	// Settings
	//
	case 'settings':
	case 'saveSettings':
		define('HANDLER_CLASS', 'AdminSettingsHandler');
		import('pages.admin.AdminSettingsHandler');
		break;
	//
	// Archive Management
	//
	case 'archives':
	case 'createArchive':
	case 'editArchive':
	case 'manage':
	case 'updateArchive':
	case 'deleteArchive':
	case 'updateIndex':
	case 'flushIndex':
		define('HANDLER_CLASS', 'AdminArchiveHandler');
		import('pages.admin.AdminArchiveHandler');
		break;
	//
	// Sort Order Management
	//
	case 'sortOrders':
	case 'createSortOrder':
	case 'editSortOrder':
	case 'updateSortOrder':
	case 'deleteSortOrder':
		define('HANDLER_CLASS', 'AdminSortOrdersHandler');
		import('pages.admin.AdminSortOrdersHandler');
		break;
	//
	// Languages
	//
	case 'languages':
	case 'saveLanguageSettings':
	case 'installLocale':
	case 'uninstallLocale':
	case 'reloadLocale':
		define('HANDLER_CLASS', 'AdminLanguagesHandler');
		import('pages.admin.AdminLanguagesHandler');
		break;
	//
	// People Management
	//
	case 'people':
	case 'enrollSearch':
	case 'enroll':
	case 'unEnroll':
	case 'enrollSyncSelect':
	case 'enrollSync':
	case 'createUser':
	case 'suggestUsername':
	case 'mergeUsers':
	case 'disableUser':
	case 'enableUser':
	case 'removeUser':
	case 'editUser':
	case 'updateUser':
	case 'userProfile':
	case 'signInAsUser':
	case 'signOutAsUser':
		define('HANDLER_CLASS', 'PeopleHandler');
		import('pages.admin.PeopleHandler');
		break;
	//
	// Administrative functions
	//
	case 'systemInfo':
	case 'phpinfo':
	case 'expireSessions':
	case 'clearTemplateCache':
	case 'clearDataCache':
		define('HANDLER_CLASS', 'AdminFunctionsHandler');
		import('pages.admin.AdminFunctionsHandler');
		break;
	//
	// Plugin Management
	//
	case 'plugins':
	case 'plugin':
		define('HANDLER_CLASS', 'PluginHandler');
		import('pages.admin.PluginHandler');
		break;
	case 'managePlugins':
		define('HANDLER_CLASS', 'PluginManagementHandler');
		import('pages.admin.PluginManagementHandler');
		break;
	case 'index':
	case 'layout':
	case 'saveLayout':
	case 'viewCaptcha':
		define('HANDLER_CLASS', 'AdminHandler');
		import('pages.admin.AdminHandler');
		break;
}

?>
