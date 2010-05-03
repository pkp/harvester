<?php

/**
 * @file Upgrade.inc.php
 *
 * Copyright (c) 2005-2010 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package install
 * @class Upgrade
 *
 * Perform system upgrade.
 *
 * $Id$
 */

import('lib.pkp.classes.install.Installer');

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

	function updateArchivePluginNames() {
		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$archiveSettingsDao =& DAORegistry::getDAO('ArchiveSettingsDAO');
		$archives =& $archiveDao->getArchives(false);
		while ($archive =& $archives->next()) {
			$schemaPluginName = $archive->getSetting('schemaPluginName');
			$archive->setSchemaPluginName($schemaPluginName);
			$archiveDao->updateArchive($archive);
			$archiveSettingsDao->deleteSetting($archive->getArchiveId(), 'schemaPluginName');
			unset($archive);
		}
		return true;
	}

	/**
	 * Install the schema aliases (during upgrade)
	 */
	function installSchemaAliases() {
		$schemaAliasDao =& DAORegistry::getDAO('SchemaAliasDAO');
		$schemaAliasDao->installSchemaAliases();
		return true;
	}

	/**
	 * For 2.3 upgrade:  Add initial plugin data to versions table
	 * @return boolean
	 */
	function addPluginVersions() {
		$versionDao =& DAORegistry::getDAO('VersionDAO');
		import('lib.pkp.classes.site.VersionCheck');
		$categories = PluginRegistry::getCategories();
		foreach ($categories as $category) {
			PluginRegistry::loadCategory($category);
			$plugins = PluginRegistry::getPlugins($category);
			foreach ($plugins as $plugin) {
				$versionFile = $plugin->getPluginPath() . '/version.xml';

				if (FileManager::fileExists($versionFile)) {
					$versionInfo =& VersionCheck::parseVersionXML($versionFile);
					$pluginVersion = $versionInfo['version'];
				}  else {
					$pluginVersion = new Version(
						1, 0, 0, 0, Core::getCurrentDate(), 1,
						'plugins.'.$category, basename($plugin->getPluginPath()), '', 0
					);
				}
				$versionDao->insertVersion($pluginVersion, true);
			}
		}
	}
}

?>
