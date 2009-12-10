<?php

/**
 * @file classes/core/HarvesterApplication.inc.php
 *
 * Copyright (c) 2005-2009 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HarvesterApplication
 * @ingroup core
 * @see PKPApplication
 *
 * @brief Class describing this application.
 *
 */

// $Id$


import('core.PKPApplication');

class HarvesterApplication extends PKPApplication {
	function HarvesterApplication() {
		parent::PKPApplication();
	}

	function initialize(&$application) {
		PKPApplication::initialize($application);

		import('i18n.Locale');
	}

	/**
	 * Get the dispatcher implementation singleton
	 * @return Dispatcher
	 */
	function &getDispatcher() {
		$dispatcher =& parent::getDispatcher();

		// Inject application-specific configuration
		$dispatcher->addRouterName('core.HarvesterPageRouter', ROUTE_PAGE);
		return $dispatcher;
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
			'ArchiveDAO' => 'archive.ArchiveDAO',
			'ArchiveSettingsDAO' => 'archive.ArchiveSettingsDAO',
			'EmailTemplateDAO' => 'mail.EmailTemplateDAO',
			'FieldDAO' => 'field.FieldDAO',
			'OAIDAO' => 'oai.harvester.OAIDAO',
			'PluginSettingsDAO' => 'plugins.PluginSettingsDAO',
			'RecordDAO' => 'record.RecordDAO',
			'RoleDAO' => 'security.RoleDAO',
			'RTDAO' => 'rt.harvester2.RTDAO',
			'SchemaDAO' => 'schema.SchemaDAO',
			'SchemaAliasDAO' => 'schema.SchemaAliasDAO',
			'SortOrderDAO' => 'sortOrder.SortOrderDAO',
			'UserDAO' => 'user.UserDAO',
			'UserSettingsDAO' => 'user.UserSettingsDAO'
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
		import('help.Help');
		$help = new Help();
		return $help;
	}
}

?>
