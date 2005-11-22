<?php return array (
  'common.changesSaved' => 'Your changes have been saved.',
  'common.language' => 'Language',
  'common.languages' => 'Languages',
  'common.notApplicable' => 'Not Applicable',
  'common.harvester2' => 'Harvester2',
  'common.search' => 'Search',
  'common.delete' => 'Delete',
  'common.yes' => 'Yes',
  'common.no' => 'No',
  'common.on' => 'On',
  'common.off' => 'Off',
  'common.edit' => 'Edit',
  'common.error.databaseError' => 'A database error has occurred: {$error}',
  'common.mailingAddress' => 'Mailing Address',
  'common.save' => 'Save',
  'common.cancel' => 'Cancel',
  'common.requiredField' => '* Denotes required field',
  'common.action' => 'Action',
  'navigation.home' => 'Home',
  'navigation.help' => 'Help',
  'navigation.about' => 'About',
  'navigation.search' => 'Search',
  'navigation.login' => 'Log In',
  'navigation.content' => 'Content',
  'navigation.loggedInAs' => 'You are logged in as...',
  'navigation.administration' => 'Administration',
  'navigation.logout' => 'Log Out',
  'navigation.previousPage' => '<<',
  'navigation.nextPage' => '>>',
  'navigation.items' => '{$from} - {$to} of {$total} Items',
  'installer.harvester2Installation' => 'PKP Harvester2 Installation',
  'installer.harvester2Upgrade' => 'Harvester2 Upgrade',
  'installer.installationInstructions' => '<h4>PKP Harvester2 Version {$version}</h4>

<p>Thank you for downloading the Public Knowledge Project\'s <strong>Harvester2</strong>. Before proceeding, please read the <a href="{$baseUrl}/docs/README">README</a> file included with this software. For more information about the Public Knowledge Project and its software projects, please visit the <a href="http://pkp.sfu.ca/" target="_blank">PKP web site</a>. If you have bug reports or technical support inquiries about Harvester2, see the <a href="http://pkp.sfu.ca/support/forum">support forum</a> or visit PKP\'s online <a href="http://pkp.sfu.ca/bugzilla/" target="_blank">bug reporting system</a>. Although the support forum is the preferred method of contact, you can also email the team at <a href="mailto:pkp-support@sfu.ca">pkp-support@sfu.ca</a>.</p>

<h4>Recommended system requirements</h4>

<ul>
	<li><a href="http://www.php.net/" target="_blank">PHP</a> >= 4.2.x (including PHP 5.x)</li>
	<li><a href="http://www.mysql.com/" target="_blank">MySQL</a> >= 3.23.23 (including MySQL 4.x) or <a href="http://www.postgresql.org/" target="_blank">PostgreSQL</a> >= 7.1 (including PostgreSQL 8.x)</li>
	<li><a href="http://httpd.apache.org/" target="_blank">Apache</a> >= 1.3.2x or >= 2.0.4x or Microsoft IIS 6</li>
	<li>Operating system: Any OS that supports the above software, including <a href="http://www.linux.org/" target="_blank">Linux</a>, <a href="http://www.bsd.org/" target="_blank">BSD</a>, <a href="http://www.sun.com/" target="_blank">Solaris</a>, <a href="http://www.apple.com/" target="_blank">Mac OS X</a>, <a href="http://www.microsoft.com/">Windows</a></li>
</ul>

<p>As PKP does not have the resources to test every possible combination of software versions and platforms, no guarantee of correct operation or support is implied.</p>

<p>Changes to these settings can be made after installation by editing the file <tt>config.inc.php</tt> in the base Harvester2 directory, or using the site administration web interface.</p>

<h4>Supported database systems</h4>

<p>Harvester2 has currently only been tested on MySQL and PostgreSQL, although other database management systems supported by <a href="http://php.weblogs.com/adodb/" target="_blank">ADOdb</a> may work (in full or partially). Compatibility reports and/or code patches for alternative DBMSs can be sent to the Harvester2 team.</p>

<h4>Pre-Installation Steps</h4>

<p>1. The following files and directories (and their contents) must be made writable:</p>
<ul>
	<li><tt>config.inc.php</tt> is writable (optional): {$writable_config}</li>
	<li><tt>cache/</tt> is writable: {$writable_cache}</li>
	<li><tt>cache/t_cache/</tt> is writable: {$writable_templates_cache}</li>
	<li><tt>cache/t_compile/</tt> is writable: {$writable_templates_compile}</li>
	<li><tt>cache/_db</tt> is writable: {$writable_db_cache}</li>
</ul>

<h4>Manual installation</h4>

<p>Select <strong>Manual Install</strong> to display the SQL statements necessary to create the Harvester2 database schema and initial data so that database installation can be performed manually. This option is useful when debugging installation problems, particularly on unsupported platforms. Note that this installation method will not attempt to create the database or any tables, although the configuration file will still be written with the supplied database settings.</p>',
  'installer.upgradeInstructions' => '<h4>Harvester2 Version {$version}</h4>
	
<p>Thank you for downloading the Public Knowledge Project\'s <strong>Harvester2</strong>. Before proceeding, please read the <a href="{$baseUrl}/docs/README">README</a> and <a href="{$baseUrl}/docs/UPGRADE">UPGRADE</a> files included with this software. For more information about the Public Knowledge Project and its software projects, please visit the <a href="http://pkp.sfu.ca/" target="_blank">PKP web site</a>. If you have bug reports or technical support inquiries about Harvester2, see the <a href="http://pkp.sfu.ca/support/forum">support forum</a> or visit PKP\'s online <a href="http://pkp.sfu.ca/bugzilla/" target="_blank">bug reporting system</a>. Although the support forum is the preferred method of contact, you can also email the team at <a href="mailto:pkp-support@sfu.ca">pkp-support@sfu.ca</a>.</p>
<p>It is <strong>strongly recommended</strong> that you back up your database, files directory, and Harvester2 installation directory before proceeding.</p>
<p>If you are running in <a href="http://www.php.net/features.safe-mode" target="_blank">PHP Safe Mode</a>, please ensure that the max_execution_time directive in your php.ini configuration file is set to a high limit. If this or any other time limit (e.g. Apache\'s "Timeout" directive) is reached and the upgrade process is interrupted, manual intervention will be required.</p>',
  'installer.localeSettings' => 'Locale Settings',
  'installer.localeSettingsInstructions' => 'For complete Unicode (UTF-8) support, select UTF-8 for all character set settings. Note that this support currently requires a MySQL >= 4.1.1 or PostgreSQL >= 7.1 database server. Please also note that full Unicode support requires PHP >= 4.3.0 compiled with support for the <a href="http://www.php.net/mbstring" target="_blank">mbstring</a> library (enabled by default in most recent PHP installations). You may experience problems using extended character sets if your server does not meet these requirements.
<br /><br />
Your server currently supports mbstring: <strong>{$supportsMBString}</strong>',
  'installer.localeInstructions' => 'The primary language to use for this system. Please consult the Harvester2 documentation if you are interested in support for languages not listed here.',
  'installer.additionalLocales' => 'Additional locales',
  'installer.additionalLocalesInstructions' => 'Select any additional languages to support in this system. Additional languages can also be installed at any time from the site administration interface.',
  'installer.clientCharset' => 'Client character set',
  'installer.clientCharsetInstructions' => 'The encoding to use for data sent to and received from browsers.',
  'installer.connectionCharset' => 'Connection character set',
  'installer.connectionCharsetInstructions' => 'The encoding to use for data sent to and received from the database. This should be the same as the client character set. Note that this capability is only supported with MySQL >= 4.1.1 or PostgreSQL >= 7.1. Select "Not available" if your database server does not meet these requirements.',
  'installer.databaseCharset' => 'Database character set',
  'installer.databaseCharsetInstructions' => 'The encoding to use for data stored in the database. Note that this capability is only supported with MySQL >= 4.1.1 or PostgreSQL >= 7.1. Select "Not available" if your database server does not meet these requirements.',
  'installer.locale' => 'Locale',
  'installer.securitySettings' => 'Security Settings',
  'installer.encryption' => 'Password encryption algorithm',
  'installer.encryptionInstructions' => 'SHA1 is recommended if your system supports it (requires PHP >= 4.3.0).',
  'installer.administratorAccount' => 'Administrator Account',
  'installer.administratorAccountInstructions' => 'This user account will become the site administrator and have complete access to the system. Additional user accounts can be created after installation.',
  'installer.databaseSettings' => 'Database Settings',
  'installer.databaseDriver' => 'Database driver',
  'installer.databaseHost' => 'Host',
  'installer.databaseUsername' => 'Username',
  'installer.databasePassword' => 'Password',
  'installer.databaseName' => 'Database name',
  'installer.databaseDriverInstructions' => '<strong>Database drivers listed in brackets do not appear to have the required PHP extension loaded and installation will likely fail if selected.</strong><br />Any unsupported database drivers listed above are listed solely for academic purposes and are unlikely to work.',
  'installer.databaseHostInstructions' => 'Leave the hostname blank to connect using domain sockets instead of over TCP/IP. This is not necessary with MySQL, which will automatically use sockets if "localhost" is entered, but is required with some other database servers such as PostgreSQL.',
  'installer.createDatabase' => 'Create new database',
  'installer.createDatabaseInstructions' => 'To use this option your database system must support remote database creation and your user account must have the appropriate permissions to create new databases. If installation fails with this option selected, manually create the database on your server and run the installer again with this option disabled.',
  'installer.installHarvester2' => 'Install Harvester2',
  'installer.upgradeHarvester2' => 'Upgrade Harvester2',
  'installer.manualInstall' => 'Manual Install',
  'installer.manualUpgrade' => 'Manual Upgrade',
  'installer.form.localeRequired' => 'A locale must be selected.',
  'installer.form.clientCharsetRequired' => 'A client character set must be selected.',
  'installer.form.filesDirRequired' => 'The directory to be used for storing uploaded files is required.',
  'installer.form.encryptionRequired' => 'The algorithm to use for encrypting user passwords must be selected.',
  'installer.form.databaseDriverRequired' => 'A database driver must be selected.',
  'installer.form.databaseNameRequired' => 'The database name is required.',
  'installer.form.usernameRequired' => 'A username for the administrator account is required.',
  'installer.form.passwordRequired' => 'A password for the administrator account is required.',
  'installer.form.usernameAlphaNumeric' => 'The administrator username can contain only alphanumeric characters, underscores, and hyphens, and must begin and end with an alphanumeric character.',
  'installer.form.passwordsDoNotMatch' => 'The administrator passwords do not match.',
  'installer.form.emailRequired' => 'A valid email address for the administrator account is required.',
  'installer.form.separateMultiple' => 'Separate multiple values with commas',
  'installer.installErrorsOccurred' => 'Errors occurred during installation',
  'installer.installFilesDirError' => 'The directory specified for uploaded files does not exist or is not writable.',
  'installer.publicFilesDirError' => 'The public files directory does not exist or is not writable.',
  'installer.installFileError' => 'The installation file <tt>dbscripts/xml/install.xml</tt> does not exist or is not readable.',
  'installer.installParseDBFileError' => 'Error parsing the database installation file <tt>{$file}</tt>.',
  'installer.configFileError' => 'The configuration file <tt>config.inc.php</tt> does not exist or is not readable.',
  'installer.reinstallAfterDatabaseError' => '<b>Warning:</b> If installation failed part way through database installation you may need to drop your Harvester2 database or database tables before attempting to reinstall the database.',
  'installer.overwriteConfigFileInstructions' => '<h4>IMPORTANT!</h4>
<p>The installer could not automatically overwrite the configuration file. Before attempting to use the system, please open <tt>config.inc.php</tt> in a suitable text editor and replace its contents with the contents of the text field below.</p>',
  'installer.contentsOfConfigFile' => 'Contents of configuration file',
  'installer.manualSQLInstructions' => '<h4>Manual installation</h4>
<p>The SQL statements to create the Harvester2 database schema and initial data are displayed below. Note that the system will be unusable until these statements have been executed manually. You will also have to manually configure the <tt>config.inc.php</tt> configuration file.</p>',
  'installer.installerSQLStatements' => 'SQL statements for installation',
  'installer.installationComplete' => '<p>Installation of Harvester2 has completed successfully.</p>
<p>To begin using the system, <a href="{$indexUrl}/login">login</a> with the username and password entered on the previous page.</p>',
  'installer.upgradeComplete' => '<p>Upgrade of Harvester2 to version {$version} has completed successfully.</p>
<p>Don\'t forget to set the "installed" setting in your config.inc.php configuration file back to <i>On</i>.</p>',
  'installer.releaseNotes' => 'Release Notes',
  'installer.checkYes' => 'Yes',
  'installer.checkNo' => '<span class="formError">NO</span>',
  'locale.primary' => 'Primary Locale',
  'locale.supported' => 'Supported Locales',
  'user.login' => 'Log In',
  'user.logout' => 'Log Out',
  'user.username' => 'Username',
  'user.password' => 'Password',
  'user.email' => 'Email',
  'user.register.repeatPassword' => 'Repeat Password',
  'user.login.loginError' => 'Invalid username or password. Please try again.',
  'user.login.rememberMe' => 'Remember me',
  'user.login.rememberUsernameAndPassword' => 'Remember my username and password',
  'form.errorsOccurred' => 'Errors occurred processing this form',
  'archive.title' => 'Title',
  'archive.url' => 'URL',
  'archive.description' => 'Description',
  'archive.harvester' => 'Harvester',
  'admin.siteAdmin' => 'Site Administration',
  'admin.siteSettings' => 'Site Settings',
  'admin.archives' => 'Archives',
  'admin.settings.siteTitle' => 'Site title',
  'admin.settings.introduction' => 'Introduction',
  'admin.settings.aboutDescription' => 'About the Site description',
  'admin.settings.form.titleRequired' => 'A title is required.',
  'admin.settings.contactName' => 'Name of principal contact',
  'admin.settings.contactEmail' => 'Email of principal contact',
  'admin.settings.form.contactNameRequired' => 'The name of the principal contact is required.',
  'admin.settings.form.contactEmailRequired' => 'The email address of the principal contact is required.',
  'admin.settings.siteLanguage' => 'Site language',
  'admin.siteManagement' => 'Site Management',
  'admin.adminFunctions' => 'Administrative Functions',
  'admin.expireSessions' => 'Expire User Sessions',
  'admin.confirmExpireSessions' => 'Are you sure you want to expire all user sessions? You will be forced to log in again.',
  'admin.clearTemplateCache' => 'Clear Template Cache',
  'admin.clearDataCache' => 'Clear Data Caches',
  'admin.confirmClearTemplateCache' => 'Are you sure you want to clear the cache of compiled templates?',
  'admin.systemInformation' => 'System Information',
  'admin.archives.addArchive' => 'Add Archive',
  'admin.archives.editArchive' => 'Edit Archive',
  'admin.archives.manageArchives' => 'Manage Archives',
  'admin.archives.noneCreated' => 'None Created',
  'admin.archives.form.titleRequired' => 'A title is required.',
  'admin.archives.form.urlRequired' => 'The URL field is required.',
  'admin.archives.form.url.description' => 'e.g. http://www.yourarchive.com',
  'admin.languages.languageSettings' => 'Language Settings',
  'admin.languages.installLanguages' => 'Install Languages',
  'admin.languages.primaryLocaleInstructions' => 'This will be the default language for the site.',
  'admin.languages.supportedLocalesInstructions' => 'Select all locales to support on the site. If multiple locales are not selected, the language toggle menu will not appear and extended language settings will not be available.',
  'admin.languages.languageOptions' => 'Language options',
  'admin.languages.installedLocales' => 'Installed Locales',
  'admin.languages.reload' => 'Reload Locale',
  'admin.languages.confirmReload' => 'Are you sure you want to reload this locale? This will erase any existing locale-specific data such as customized email templates.',
  'admin.languages.uninstall' => 'Uninstall Locale',
  'admin.languages.confirmUninstall' => 'Are you sure you want to uninstall this locale?',
  'admin.languages.installNewLocales' => 'Install New Locales',
  'admin.languages.installNewLocalesInstructions' => 'Select any additional locales to install support for in this system. See the Harvester2 documentation for information on adding support for new languages.',
  'admin.languages.noLocalesAvailable' => 'No additional locales are available for installation.',
  'admin.languages.installLocales' => 'Install',
  'admin.systemVersion' => 'Harvester2 Version',
  'admin.systemConfiguration' => 'Harvester2 Configuration',
  'admin.serverInformation' => 'Server Information',
  'admin.currentVersion' => 'Current version',
  'admin.versionHistory' => 'Version history',
  'admin.version' => 'Version',
  'admin.versionMajor' => 'Major',
  'admin.versionMinor' => 'Minor',
  'admin.versionRevision' => 'Revision',
  'admin.versionBuild' => 'Build',
  'admin.dateInstalled' => 'Date installed',
  'admin.systemConfigurationDescription' => 'Harvester2 configuration settings from <tt>config.inc.php</tt>.',
  'admin.serverInformationDescription' => 'Basic operating system and server software versions. Click on <span class="highlight">Extended PHP Information</span> to view extended details of this server\'s PHP configuration.',
  'admin.phpInfo' => 'Extended PHP Information',
  'admin.server.platform' => 'OS platform',
  'admin.server.phpVersion' => 'PHP version',
  'admin.server.apacheVersion' => 'Apache version',
  'admin.server.dbDriver' => 'Database driver',
  'admin.server.dbVersion' => 'Database server version',
  'admin.editSystemConfigInstructions' => 'Use this form to modify your system configuration (the <tt>config.inc.php</tt> file). Click Save to save your new configuration, or "Display" to simply display the updated configuration file and not modify your existing configuration.
<br /><br />
<span class="formError">Warning: Modifying these settings can potentially leave your site in an inaccessible state (requiring your configuration file to be manually fixed). It is strongly recommended that you do not make any changes unless you know exactly what you are doing.</span>',
  'admin.saveSystemConfig' => 'Save Configuration',
  'admin.displayNewSystemConfig' => 'Display New Configuration',
  'admin.systemConfigFileReadError' => 'The configuration file <tt>config.inc.php</tt> does not exist, is not readable, or is invalid.',
  'admin.overwriteConfigFileInstructions' => '<h4>NOTE!</div>
<p>The system could not automatically overwrite the configuration file. To apply your configuration changes you must open <tt>config.inc.php</tt> in a suitable text editor and replace its contents with the contents of the text field below.</p>',
  'admin.displayConfigFileInstructions' => 'The contents of your updated configuration are displayed below. To apply the configuration changes you must open <tt>config.inc.php</tt> in a suitable text editor and replace its contents with the contents of the text field below.',
  'admin.configFileUpdatedInstructions' => 'Your configuration file has been successfully updated. Please note that if your site no longer functions correctly you may need to manually fix your configuration by editing <tt>config.inc.php</tt> directly.',
  'admin.contentsOfConfigFile' => 'Contents of configuration file',
  'admin.version.checkForUpdates' => 'Check for updates',
  'admin.version.latest' => 'Latest version',
  'admin.version.upToDate' => 'Your system is up-to-date',
  'admin.version.updateAvailable' => 'An updated version is available',
  'admin.version.downloadPackage' => 'Download',
  'admin.version.downloadPatch' => 'Download Patch',
  'admin.version.moreInfo' => 'More Information',
  'sidebar.harvesterStats' => 'Harvester Stats',
  'sidebar.harvesterStats.description' => 'Harvester2 currently has <strong>FIXME</strong> papers from <strong>FIXME</strong> archive(s) indexed.',
  'sidebar.addYourArchive' => 'Add Your Archive',
  'sidebar.addYourArchive.description' => '<a href="{$addUrl}">Click here</a> to add your system to our index.',
  'default.siteIntro' => '<strong>Welcome to the Public Knowledge Project\'s metadata archive...</strong>
	
	To improve the accuracy of searching within the PKP System, authors have been asked to index their work, where applicable, by discipline(s), topics, genre, method, coverage, and sample. This allows you to search for "empirical" versus "historical" studies, for example, under "index terms." You can also view a document\'s index terms by selecting the complete record from among the search results.',
  'default.emailSignature' => '________________________________________________________________________
	Harvester2
	{$indexUrl}',
  'default.footer' => '&copy; 2005 <a href="http://pkp.sfu.ca/harvester2">Public Knowledge Project</a>',
  'about.harvester' => 'About the Harvester',
  'about.harvester.description' => 'The PKP Open Archives Harvester is a free metadata indexing system developed by the Public Knowledge Project through its federally funded efforts to expand and improve access to research. The PKP OAI Harvester allows you to create a searchable index of the metadata from Open Archives Initiative-compliant archives, such as sites using Open Journal Systems or Open Conference Systems.
	
	The PKP OAI Harvester is currently compatible with versions 1.1 and 2.0 of the OAI Harvesting Protocol.',
); ?>