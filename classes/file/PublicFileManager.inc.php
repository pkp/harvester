<?php

/**
 * @file PublicFileManager.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package file
 * @class PublicFileManager
 *
 * PublicFileManager class.
 * Wrapper class for uploading files to a site's public directory.
 *
 * $Id$
 */

import('lib.pkp.classes.file.PKPPublicFileManager');

class PublicFileManager extends PKPPublicFileManager {
	/**
	 * Constructor
	 */
	function PublicFileManager() {
		parent::PKPPublicFileManager();
	}

	// No extension for now
}

?>
