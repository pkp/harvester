# Upgrading an OHS Installation

Note: Before upgrading your installation, perform a complete backup of your
data files and database. If the upgrade process fails, you will need to recover
from backup before continuing.

If you are using PHP Safe Mode, please ensure that the max_execution_time
directive in your php.ini configuration file is set to a high limit. If this
or any other time limit (e.g. Apache's "Timeout" directive) is reached and
the upgrade process is interrupted, manual intervention will be required.


## Upgrading from Harvester 2.x

WARNING: Harvester release 2.3 contains a rewrite of the metadata storage code
used in prior releases. Upgrading will maintain settings and the list of
archives but will DELETE all metadata stored in the system. After upgrading,
perform a harvest of all archives to re-index and store the data in the new
structure.

Upgrading to the latest version of the Harvester involves two steps:

    - Obtaining the latest Harvester code
    - Upgrading the Harvester database

It is highly recommended that you also review the release notes (docs/RELEASE)
and other documentation in the docs directory before performing an upgrade.


### Obtaining the latest OHS code

The OHS source code is available in two forms: a complete stand-alone 
package, and from read-only github access.

#### 1. Full Package

If you have not made local code modifications to the system, upgrade by 
downloading the complete package for the latest release of OHS:

- Download and decompress the package from the OHS web site into an empty
	directory (NOT over top of your current OHS installation)
- Move or copy the following files and directories into it from your current
	OHS installation:
		- config.inc.php
		- public/
- Synchronize new changes from config.TEMPLATE.inc.php to config.inc.php
- Replace the current OHS directory with the new OHS directory, moving the
	old one to a safe location as a backup
- Be sure to review the Configuration Changes section of the release notes
	in docs/release-notes/README-(version) for all versions between your
	original version and the new version. You may need to manually add
	new items to your config.inc.php file.


#### 2. git

Updating from github is the recommended approach if you have made local
modifications to the system.

If your instance of OHS was checked out from github (see [docs/README-GIT.md](README-GIT.md)),
you can update the OHS code using a git client.

To update the OHS code from a git check-out, run the following command from
your OHS directory:

```
$ git rebase --onto <new-release-tag> <previous-release-tag>
```

This assumes that you have made local changes and committed them on top of
the old release tag. The command will take your custom changes and apply
them on top of the new release. This may cause merge conflicts which have to
be resolved in the usual way, e.g. using a merge tool like kdiff3.

"TAG" should be replaced with the git tag corresponding to the new release.
OHS release version tags are of the form "ohs-MAJOR_MINOR_REVSION-BUILD".
For example, the tag for the initial release of OHS 2.3.1 is "ohs-2_3_1-0".

Consult the [README](README.md) of the latest OHS package or the OHS web site for the
tag corresponding to the latest available OHS release.

Note that attempting to update to an unreleased version (e.g., using the HEAD
tag to obtain the bleeding-edge OHS code) is not recommended for anyone other
than OHS or third-party developers; using experimental code on a production
deployment is strongly discouraged and will not be supported in any way by
the OHS team.


### Upgrading the OHS database

After obtaining the latest OHS code, an additional script must be run to
upgrade the OHS database.

NOTE: Patches to the included ADODB library may be required for PostgreSQL
upgrades; see https://forum.pkp.sfu.ca/t/upgrade-failure-postgresql/19215

This script can be executed from the command-line or via the OHS web interface.

#### 1. Command-line

If you have the CLI version of PHP installed (e.g., `/usr/bin/php`), you can
upgrade the database as follows:

- Edit config.inc.php and change "installed = On" to "installed = Off"
- Run the following command from the OHS directory (not including the $):
	`$ php tools/upgrade.php upgrade`
- Re-edit config.inc.php and change "installed = Off" back to
	 "installed = On"

#### 2. Web

If you do not have the PHP CLI installed, you can also upgrade by running a
web-based script. To do so:

- Edit config.inc.php and change "installed = On" to "installed = Off"
- Open a web browser to your OHS site; you should be redirected to the
	installation and upgrade page
- Select the "Upgrade" link and follow the on-screen instructions
- Re-edit config.inc.php and change "installed = Off" back to
	 "installed = On"


