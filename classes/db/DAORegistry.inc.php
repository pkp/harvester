<?php

/**
 * DAORegistry.inc.php
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package db
 *
 * Class for retrieving DAO objects.
 * Maintains a static list of DAO objects so each DAO is instantiated only once. 
 *
 * $Id$
 */

class DAORegistry {

	/**
	 * Get the current list of registered DAOs.
	 * This returns a reference to the static hash used to
	 * store all DAOs currently instantiated by the system.
	 * @return array
	 */
	function &getDAOs() {
		static $daos = array();
		return $daos;
	}

	/**
	 * Register a new DAO with the system.
	 * @param $name string The name of the DAO to register
	 * @param $dao object A reference to the DAO to be registered
	 * @return object A reference to previously-registered DAO of the same
	 *    name, if one was already registered; null otherwise
	 */
	function &registerDAO($name, &$dao) {
		if (isset($daos[$name])) {
			$returner = &$daos[$name];
		} else {
			$returner = null;
		}
		$daos = &DAORegistry::getDAOs();
		$daos[$name] = &$dao;
		return $returner;
	}

	/**
	 * Retrieve a reference to the specified DAO.
	 * @param $name string the class name of the requested DAO
	 * @param $dbconn ADONewConnection optional
	 * @return DAO
	 */
	function &getDAO($name, $dbconn = null) {
		$daos = &DAORegistry::getDAOs();

		if (!isset($daos[$name])) {
			// Import the required DAO class.
			import(DAORegistry::getQualifiedDAOName($name));

			// Only instantiate each class of DAO a single time
			$daos[$name] = &new $name();
			if ($dbconn != null) {
				// FIXME Needed by installer but shouldn't access member variable directly
				$daos[$name]->_dataSource = $dbconn;
			}
		}
		
		return $daos[$name];
	}

	/**
	 * Return the fully-qualified (e.g. page.name.ClassNameDAO) name of the
	 * given DAO.
	 * @param $name string
	 * @return string
	 */
	function getQualifiedDAOName($name) {
		// FIXME This function should be removed (require fully-qualified name to be passed to getDAO?)
		switch ($name) {
			case 'ArchiveDAO': return 'archive.ArchiveDAO';
			case 'ArchiveSettingsDAO': return 'archive.ArchiveSettingsDAO';
			case 'FieldDAO': return 'field.FieldDAO';
			case 'SearchableFieldDAO': return 'field.SearchableFieldDAO';
			case 'IndexerDAO': return 'indexer.IndexerDAO';
			case 'RecordDAO': return 'record.RecordDAO';
			case 'EntryDAO': return 'entry.EntryDAO';
			case 'SessionDAO': return 'session.SessionDAO';
			case 'SiteSettingsDAO': return 'site.SiteSettingsDAO';
			case 'VersionDAO': return 'site.VersionDAO';
			default: fatalError('Unrecognized DAO ' . $name);
		}
		return null;
	}
}
?>
