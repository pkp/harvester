<?php

/**
 * @file Upgrade.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package install
 * @class Upgrade
 *
 * Perform system upgrade.
 *
 * $Id$
 */

import('install.Installer');

class Upgrade extends Installer {

	/**
	 * Constructor.
	 * @param $params array installer parameters
	 * @param $descriptor string descriptor path
	 * @param $isPlugin boolean true iff a plugin is being installed	
	 */
	function Upgrade($params, $installFile = 'upgrade.xml', $isPlugin = false) {
		parent::Installer($installFile, $params, $isPlugin);
	}

	/**
	 * Returns true iff this is an upgrade process.
	 */
	function isUpgrade() {
		return true;
	}

	//
	// Upgrade actions
	//

	/**
	 * Rebuild the search index.
	 * @return boolean
	 */
	function rebuildSearchIndex() {
		import('search.ArticleSearchIndex');
		ArticleSearchIndex::rebuildIndex();
		return true;
	}

}

?>
