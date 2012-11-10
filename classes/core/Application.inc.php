<?php

/**
 * @file classes/core/Application.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Application
 * @ingroup core
 * @see PKPApplication
 *
 * @brief Class describing this application.
 *
 */


import('lib.pkp.classes.core.PKPApplication');

define('PHP_REQUIRED_VERSION', '4.2.0');

class Application extends PKPApplication {
	function Application() {
		parent::PKPApplication();
	}

	/**
	 * Get the "context depth" of this application, i.e. the number of
	 * parts of the URL after index.php that represent the context of
	 * the current request (e.g. Journal [1], or Conference and
	 * Scheduled Conference [2]).
	 * @return int
	 */
	function getContextDepth() {
		return 0;
	}

	/**
	 * Get the list of the contexts available for this application
	 * i.e. the various parameters that are needed to represent the
	 * (e.g. array('journal') or array('conference', 'schedConf'))
	 * @return Array
	 */
	function getContextList() {
		return array();
	}

	/**
	 * Get the locale key for the name of this application.
	 * @return string
	 */
	function getNameKey() {
		return('common.harvester2');
	}

	/**
	 * Get the symbolic name of this application
	 * @return string
	 */
	function getName() {
		return 'harvester2';
	}

	/**
	 * Get the URL to the XML descriptor for the current version of this
	 * application.
	 * @return string
	 */
	function getVersionDescriptorUrl() {
		return('http://pkp.sfu.ca/harvester2/xml/harvester2-version.xml');
	}

	/**
	 * Get the map of DAOName => full.class.Path for this application.
	 * @return array
	 */
	function getDAOMap() {
		return array_merge(parent::getDAOMap(), array(
			'ArchiveDAO' => 'classes.archive.ArchiveDAO',
			'ArchiveSettingsDAO' => 'classes.archive.ArchiveSettingsDAO',
			'EmailTemplateDAO' => 'classes.mail.EmailTemplateDAO',
			'FieldDAO' => 'classes.field.FieldDAO',
			'OAIDAO' => 'classes.oai.harvester.OAIDAO',
			'PluginSettingsDAO' => 'classes.plugins.PluginSettingsDAO',
			'RecordDAO' => 'classes.record.RecordDAO',
			'RoleDAO' => 'classes.security.RoleDAO',
			'RTDAO' => 'classes.rt.harvester2.RTDAO',
			'SchemaDAO' => 'classes.schema.SchemaDAO',
			'SchemaAliasDAO' => 'classes.schema.SchemaAliasDAO',
			'SignoffDAO' => 'classes.signoff.SignoffDAO',
			'SortOrderDAO' => 'classes.sortOrder.SortOrderDAO',
			'UserDAO' => 'classes.user.UserDAO',
			'UserSettingsDAO' => 'classes.user.UserSettingsDAO'
		));
	}

	/**
	 * Get the list of plugin categories for this application.
	 */
	function getPluginCategories() {
		return array(
			'blocks',
			'generic',
			'harvesters',
			'oaiMetadataFormats',
			'postprocessors',
			'preprocessors',
			'schemas',
			'themes'
		);
	}

	/**
	 * Instantiate the help object for this application.
	 * @return object
	 */
	function &instantiateHelp() {
		import('classes.help.Help');
		$help = new Help();
		return $help;
	}
}

?>
