<?php

/**
 * Install.inc.php
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package install
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

import('install.Installer');

class Install extends Installer {
	
	/**
	 * Constructor.
	 * @see install.form.InstallForm for the expected parameters
	 * @param $params array installation parameters
	 */
	function Install($params) {
		parent::Installer('install.xml', $params);
	}

 	/**
	 * Returns true iff this is an upgrade process.
	 */
	function isUpgrade() {
		return false;
	}

	/**
	 * Pre-installation.
	 * @return boolean
	 */
	function preInstall() {
 		$this->currentVersion = Version::fromString('');
 		
 		$this->locale = $this->getParam('locale');
		$this->installedLocales = $this->getParam('additionalLocales');
		if (!isset($this->installedLocales) || !is_array($this->installedLocales)) {
			$this->installedLocales = array();
		}
		if (!in_array($this->locale, $this->installedLocales) && Locale::isLocaleValid($this->locale)) {
			array_push($this->installedLocales, $this->locale);
		}
		
		if ($this->getParam('manualInstall')) {
			// Do not perform database installation for manual install
			// Create connection object with the appropriate database driver for adodb-xmlschema
			$conn = &new DBConnection(
				$this->getParam('databaseDriver'),
				null,
				null,
				null,
				null
			);
			$this->dbconn = &$conn->getDBConn();
			
		} else {
			// Connect to database
			$conn = &new DBConnection(
				$this->getParam('databaseDriver'),
				$this->getParam('databaseHost'),
				$this->getParam('databaseUsername'),
				$this->getParam('databasePassword'),
				$this->getParam('createDatabase') ? null : $this->getParam('databaseName'),
				true,
				$this->getParam('connectionCharset') == '' ? false : $this->getParam('connectionCharset')
			);
			
			$this->dbconn = &$conn->getDBConn();
			
			if (!$conn->isConnected()) {
				$this->setError(INSTALLER_ERROR_DB, $this->dbconn->errorMsg());
				return false;
			}
		}
		
		DBConnection::getInstance($conn);
		
		return parent::preInstall();
	}
	
	
	//
	// Installer actions
	//
	
	/**
	 * Create a new database if required.
	 * @return boolean
	 */
	function createDatabase() {
		if (!$this->getParam('createDatabase')) {
			return true;
		}
		
		// Get database creation sql
		$dbdict = &NewDataDictionary($this->dbconn);
		
		if ($this->getParam('databaseCharset')) {
				$dbdict->SetCharSet($this->getParam('databaseCharset'));
		}
		
		list($sql) = $dbdict->CreateDatabase($this->getParam('databaseName'));
		unset($dbdict);
		
		if (!$this->executeSQL($sql)) {
			return false;
		}
		
		if (!$this->getParam('manualInstall')) {
			// Re-connect to the created database
			$this->dbconn->disconnect();
			
			$conn = &new DBConnection(
				$this->getParam('databaseDriver'),
				$this->getParam('databaseHost'),
				$this->getParam('databaseUsername'),
				$this->getParam('databasePassword'),
				$this->getParam('databaseName'),
				true,
				$this->getParam('connectionCharset') == '' ? false : $this->getParam('connectionCharset')
			);
			
			DBConnection::getInstance($conn);
		
			$this->dbconn = &$conn->getDBConn();
			
			if (!$conn->isConnected()) {
				$this->setError(INSTALLER_ERROR_DB, $this->dbconn->errorMsg());
				return false;
			}
		}
			
		return true;
	}
	
	/**
	 * Create initial required data.
	 * @return boolean
	 */
	function createData() {
		if ($this->getParam('manualInstall')) {
			$this->executeSQL(sprintf('DELETE FROM site_settings WHERE setting_name = \'username\''));
			$this->executeSQL(sprintf('DELETE FROM site_settings WHERE setting_name = \'password\''));
			$this->executeSQL(sprintf('INSERT INTO site_settings (setting_name, setting_value, setting_type) VALUES (\'username\', \'%s\', \'string\')', addslashes($this->getParam('adminUsername'))));
			$this->executeSQL(sprintf('INSERT INTO site_settings (setting_name, setting_value, setting_type) VALUES (\'password\', \'%s\', \'string\')', addslashes(Validation::encryptCredentials($this->getParam('adminUsername'), $this->getParam('adminPassword'), $this->getParam('encryption')))));
		} else {
			// Add initial site data
			import('site.Site');
			$site = &new Site();
			$site->setLocale($this->getParam('locale'));
			$site->setInstalledLocales($this->installedLocales);
			$site->installSettings('registry/siteSettings.xml', array(
				'indexUrl' => Request::getIndexUrl(),
				'adminUsername' => $this->getParam('adminUsername'),
				'adminEmail' => $this->getParam('adminEmail'),
				'encryptedPassword' => Validation::encryptCredentials($this->getParam('adminUsername'), $this->getParam('adminPassword'), $this->getParam('encryption'))
			));

			$crosswalkDao =& DAORegistry::getDAO('CrosswalkDAO');
			$crosswalkDao->installCrosswalks('registry/crosswalks.xml');
		}
		
		return true;
	}
	
	/**
	 * Write the configuration file.
	 * @return boolean
	 */
	function createConfig() {
		return $this->updateConfig(
			array(
				'general' => array(
					'installed' => 'On',
					'base_url' => Request::getBaseUrl()
				),
				'database' => array(
					'driver' => $this->getParam('databaseDriver'),
					'host' => $this->getParam('databaseHost'),
					'username' => $this->getParam('databaseUsername'),
					'password' => $this->getParam('databasePassword'),
					'name' => $this->getParam('databaseName')
				),
				'i18n' => array(
					'locale' => $this->getParam('locale'),
					'client_charset' => $this->getParam('clientCharset'),
					'connection_charset' => $this->getParam('connectionCharset') == '' ? 'Off' : $this->getParam('connectionCharset'),
					'database_charset' => $this->getParam('databaseCharset') == '' ? 'Off' : $this->getParam('databaseCharset')
				),
				'files' => array(
					'files_dir' => $this->getParam('filesDir')
				),
				'security' => array(
					'encryption' => $this->getParam('encryption')
				),
				'oai' => array(
					'repository_id' => $this->getParam('oaiRepositoryId')
				)
			)
		);
	}
	
}

?>
