<?php

/**
 * @file classes/install/Install.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
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
 */

// $Id$


define('INSTALLER_DEFAULT_SITE_TITLE', 'common.harvester2');
define('INSTALLER_DEFAULT_MIN_PASSWORD_LENGTH', 6);

import('lib.pkp.classes.install.PKPInstall');

class Install extends PKPInstall {

	/**
	 * Constructor.
	 * @see install.form.InstallForm for the expected parameters
	 * @param $params array installer parameters
	 * @param $descriptor string descriptor path
	 * @param $isPlugin boolean true iff a plugin is being installed
	 */
	function Install($params, $descriptor = 'install.xml', $isPlugin = false) {
		parent::PKPInstall($descriptor, $params, $isPlugin);
	}

	//
	// Installer actions
	//

	/**
	 * Get the names of the directories to create
	 * @return array
	 */
	function getCreateDirectories() {
		$directories = parent::getCreateDirectories();
		return $directories;
	}

	/**
	 * Create initial required data.
	 * @return boolean
	 */
	function createData() {
		// Add initial site data
		$locale = $this->getParam('locale');
		$siteDao =& DAORegistry::getDAO('SiteDAO', $this->dbconn);
		$site = new Site();
		$site->setRedirect(0);
		$site->setMinPasswordLength(INSTALLER_DEFAULT_MIN_PASSWORD_LENGTH);
		$site->setPrimaryLocale($locale);
		$site->setInstalledLocales($this->installedLocales);
		$site->setSupportedLocales($this->installedLocales);
		if (!$siteDao->insertSite($site)) {
			$this->setError(INSTALLER_ERROR_DB, $this->dbconn->errorMsg());
			return false;
		}

		// Install site settings
		$siteSettingsDao =& DAORegistry::getDAO('SiteSettingsDAO');
		$siteSettingsDao->installSettings('registry/siteSettings.xml', array(
			'adminEmail' => $this->getParam('adminEmail')
		));

		// Add initial site administrator user
		$userDao =& DAORegistry::getDAO('UserDAO', $this->dbconn);
		$user = new User();
		$user->setUsername($this->getParam('adminUsername'));
		$user->setPassword(Validation::encryptCredentials($this->getParam('adminUsername'), $this->getParam('adminPassword'), $this->getParam('encryption')));
		$user->setFirstName($user->getUsername());
		$user->setLastName('');
		$user->setEmail($this->getParam('adminEmail'));
		if (!$userDao->insertUser($user)) {
			$this->setError(INSTALLER_ERROR_DB, $this->dbconn->errorMsg());
			return false;
		}

		$roleDao =& DAORegistry::getDao('RoleDAO', $this->dbconn);
		$role = new Role();
		$role->setUserId($user->getId());
		$role->setRoleId(ROLE_ID_SITE_ADMIN);
		if (!$roleDao->insertRole($role)) {
			$this->setError(INSTALLER_ERROR_DB, $this->dbconn->errorMsg());
			return false;
		}

		// Install the schema aliases
		$schemaAliasDao =& DAORegistry::getDAO('SchemaAliasDAO');
		$schemaAliasDao->installSchemaAliases();

		// Add initial plugin data to versions table
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

		return true;
	}
}

?>
