<?php

/**
 * @file classes/install/Install.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Install
 * @ingroup install
 *
 * Perform system installation.
 *
 * This script will:
 *  - Create the database (optionally), and install the database tables and initial data.
 *  - Update the config file with installation parameters.
 * It can also be used for a "manual install" to retrieve the SQL statements required for installation.
 *
 * $Id$
 */

// Default installation data
define('INSTALLER_DEFAULT_MIN_PASSWORD_LENGTH', 6);

import('install.PKPInstall');

class Install extends PKPInstall {

	/**
	 * Constructor.
	 * @see install.form.InstallForm for the expected parameters
	 * @param $params array installation parameters
	 */
	function Install($params) {
		parent::PKPInstall('install.xml', $params);
	}

	//
	// Installer actions
	//

	/**
	 * Get the names of the directories to create
	 * @return array
	 */
	function getCreateDirectories() {
		$directories = Parent::getCreateDirectories();
		return $directories;
	}
}

?>
