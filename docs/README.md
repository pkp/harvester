	====================================
	=== Open Harvester Systems
	=== The Public Knowledge Project
	=== Version: 2.3.1
	=== GIT tag: ohs-2_3_1-0
	=== Release date: Aug 12, 2010
	====================================


## About

The Open Harvester Systems (OHS) has been developed by the Public Knowledge Project.
For general information about OHS and other open research systems, visit the
PKP web site at <https://pkp.sfu.ca/>.


## License

OHS is licensed under the GNU General Public License v2. See the file
[docs/COPYING](COPYING) for the complete terms of this license.

Third parties are welcome to modify and redistribute OHS in entirety or parts
according to the terms of this license. PKP also welcomes patches for
improvements or bug fixes to the software.


## System Requirements

Recommended server requirements:

* PHP >= 7.1
* MySQL >= 5.6
* Apache >= 1.3.2x or >= 2.0.4x or Microsoft IIS >= 6
* Operating system: Any OS that supports the above software, including
	Linux, BSD, Solaris, Mac OS X, Windows

As PKP does not have the resources to test every possible combination of
software versions and platforms, no guarantee of correct operation or support
is implied. We welcome feedback from users who have deployed OHS on systems
other than those listed above.


## Recommended Configuration

A secure deployment can be best achieved by using the following policies:

* Dedicate a database to OHS; use unique credentials to access it.
	Configure this database to perform automated backups on a regular
	basis. Perform a manual backup when upgrading or performing
	maintenance.

* Configure OHS (`config.inc.php`) to use SHA1 hashing rather than MD5.

* Configure OHS (`config.inc.php`) to use force_ssl_login so that
	authenticated users communicate with the server via HTTPS.


## Installation

Please review this document and the [RELEASE](RELEASE) document prior to installing OHS.
If you encounter problems, please also see the [FAQ](FAQ) document in this directory.

To install OHS:

1. Extract the OHS archive to the desired location in your web
	 documents directory.

2. Make the following files and directories (and their contents)
	 writeable (i.e., by changing the owner or permissions with chown or
	 chmod):
	 
	 * `config.inc.php` (optional -- if not writable you will be prompted
		 to manually overwrite this file during installation)
	 * `public`
	 * `cache`
	 * `cache/t_cache`
	 * `cache/t_config`
	 * `cache/t_compile`
	 * `cache/_db`

4. Open a web browser to http://yourdomain.com/path/to/ohs/ and
	 follow the on-screen installation instructions.
	 
	 Alternatively, the command-line installer can be used instead by
	 running the command `php tools/install.php` from your OHS directory.
	 (Note: with the CLI installer you may need to chown/chmod the public
	 and uploaded files directories after installation, if the Apache
	 user is different from the user running the tool.)

5. Recommended additional steps post-installation:

	 * Review `config.inc.php` for additional configuration settings
	 * Review the FAQ document for frequently asked technical and
		 server configuration questions.


## Upgrading

See [docs/UPGRADE.md](UPGRADE.md) for information on upgrading from previous OHS releases.


## Localization

To add support for other languages, the following sets of XML files must be
localized and placed in an appropriately named directory (using ISO locale 
codes, e.g. `fr_FR`, is recommended):

* `locale/en_US`
* `lib/pkp/locale/en_US`
* `dbscripts/xml/data/locale/en_US`
* `help/en_US`
* `rt/en_US`
* `plugins/[plugin category]/[plugin name]/locale`, where applicable

The only critical files that need translation for the system to function
properly are found in `locale/en_US`, `lib/pkp/locale/en_US`, 
`dbscripts/xml/data/locale/en_US`, and `dbscripts/xml/data/locale/en_US`.

New locales must also be added to the file `registry/locales.xml`, after which
they can be installed in the system through the site administration web
interface.
	
Translations can be contributed back to PKP for distribution with future
releases of OHS.


## Third-party Libraries

* See [lib/pkp/lib/libraries.txt](../lib/pkp/lib/libraries.txt) for a list of third-party libraries
	used by OHS.

## Contact/Support

The forum is the recommended method of contacting the team with technical
issues.

* Forum: https://forum.pkp.sfu.ca/
* Bugs: https://github.com/pkp/pkp-lib#issues
* Email: pkp.contact@gmail.com
