<?php

/**
 * @file classes/core/HarvesterApplication.inc.php
 *
 * Copyright (c) 2005-2008 John Willinsky and Alec Smecher
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
		import('core.Request');
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
	 * Get the locale key for the name of this application.
	 * @return string
	 */
	function getNameKey() {
		return('common.harvester2');
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
		return array(
			'ArchiveDAO' => 'archive.ArchiveDAO',
			'ArchiveSettingsDAO' => 'archive.ArchiveSettingsDAO',
			'CaptchaDAO' => 'captcha.CaptchaDAO',
			'CrosswalkDAO' => 'field.CrosswalkDAO',
			'EntryDAO' => 'entry.EntryDAO',
			'EmailTemplateDAO' => 'mail.EmailTemplateDAO',
			'FieldDAO' => 'field.FieldDAO',
			'HelpTocDAO' => 'help.HelpTocDAO',
			'HelpTopicDAO' => 'help.HelpTopicDAO',
			'PluginSettingsDAO' => 'plugins.PluginSettingsDAO',
			'RecordDAO' => 'record.RecordDAO',
			'RTDAO' => 'rt.harvester2.RTDAO',
			'SchemaDAO' => 'schema.SchemaDAO',
			'SearchDAO' => 'search.SearchDAO',
			'SessionDAO' => 'session.SessionDAO',
			'SiteDAO' => 'site.SiteDAO',
			'SiteSettingsDAO' => 'site.SiteSettingsDAO',
			'VersionDAO' => 'site.VersionDAO'
		);
	}
}

?>
