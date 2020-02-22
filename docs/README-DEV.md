# Coding Standards

## Contributing Code

The PKP team is happy to accept patches in the PKP Community Forum at
https://forum.pkp.sfu.ca or Github at https://www.github.com/pkp. If you
would like to have your patch included in the OHS codebase, we suggest
discussing it with the OHS team before implementation to ensure that it suits
upcoming development plans.

For code that is intended for inclusion in the main codebase:
* Pull requests against a current git clone are preferred; alternately, pull
  requests against the most recent release version are acceptable. Patches will
  also be accepted.
* Unless agreed with the development team, users should be able to toggle
  contributed features between enabled and disabled with a single setting; the
  system behavior should not be modified when the feature is disabled.
* The feature should be suitable for situations where very distinct journals are
  hosted within the same deployment; i.e. settings should generally be journal-
  level, not system-level.
* The design patterns used in OHS should be understood and adhered to.
* Localization standards should be maintained.
* Database IDs should be checked as in the current codebase.
* XSS (cross-site scripting) attacks should be checked as in the current
  codebase.
* Contributors are responsible for writing code compatible with the primary
  platforms listed in README.
* OHS management features should be kept in mind, such as upgrade and
  installation; database schema information should be maintained in the
  `dbscripts/xml/harvester2_schema.xml` file for OHS-specific functionality and in
  `lib/pkp/xml/schema` for functionality that can be shared across PKP apps; etc.
* The development team is happy to review contributed patches, but we have a
  limited amount of time to spend integrating patches with the codebase or
  modifying contributed code. If aspects of the code need work, we would rather
  inform the author and have them perform the modifications.

For contributions that are distributed separately as patches or plugins:
* If contributors haven't met all the conditions above, they are welcome to
  distribute additional features as patches or plugins. However, the OHS team
  won't be able to provide support in this case.
* If the option is available, coding a feature as a plugin is the preferred
  method. The OHS team is continuing to refine the plugin infrastructure and
  welcomes discussion with plugin developers.


## General Conventions

### Editors

* Tabs: Configure your editor to use tabs instead of spaces for indentation.
* Linefeeds: Your editor must save files using UNIX linefeed format (not DOS
  CR/LF or Mac CR format).


### Indentation style

* Use K&R indentation style.
* For example:
```
	if (condition) {
		...
	} else {
		...
	}
```


### Naming Conventions

* Use descriptive names. One character names are only acceptable as index
  variables in for loops.
* Variable and function/method names (excluding constructors) should start with
  a lowercase letter and capitalize all other words.
  E.g., `$myVariableName; function myMethodName() { }`
* Class names should start with a capital letter and capitalize all words.
  E.g., `MyClassName`
* Constant names should be capitalized with words separated by an underscore. In
  general, constant names should also be prefixed with the package/class name to
  avoid collisions. E.g., `ROLE_ID_MANAGER`


### Comments

* PHPDoc/Javadoc-style commenting is encouraged. See http://www.phpdoc.de/
* For example:
```php
	/**
	 * My method.
	 * @param $foo string
	 * @return boolean
	 */
	function myMethod($foo) {
		...
	}
```


### PHP Tags

* Use the `<?php` tag to begin PHP code instead of the abbreviated `<?` form.
* Omit ending `?>` tags at the end of PHP files, as recommended per modern PHP
  standards.


### Quoting Strings

* Use single quotes (`'`) instead of double quotes (`"`) to quote strings unless the
  string contains variables or escape sequences. Single quotes are slightly more
  efficient since PHP does not have to perform variable interpolation.


### Error Level

* Code must not produce any error or warning messages with the error_reporting
  level set to `E_ALL` (this is the default level set in `includes/driver.inc.php`).
* This means using `$array['key']` rather than `$array[key]`, not using
  uninitialized variables, etc.
* Note that this means that "`@`" should not be used haphazardly to suppress
  error messages.


### Global Variables

* Code should not rely on register_globals being enabled.
* GET/POST/Cookie variables should be accessed through the appropriate helper
  function.


## Other PHP Conventions

* The inline form of if/else is acceptable for small statements (e.g.,
  assignments) only. E.g., `$foo = $bar ? 1 : 0;`
* Compatibility with PHP per the `README.md` document is required. Appropriate
  abstractions should be used around non-backwards compatible code (e.g., using
  `function_exists()` to check for an available function and using an alternate
  implementation if it does not exist).
* Traditionally we used static access to class methods a lot. These provide
  a relatively easy to implement Singleton-like design pattern. They have two
  important drawbacks though: static methods cannot be overridden which inhibits
  clean OO design and they are notoriously difficult to unit test. That's why
  we try to no longer introduce static method calls and refactor to object methods
  where possible.


## HTML/XML

* Tag names should be lower case.


## SQL

* Uppercase SQL keywords. E.g., `INSERT`, `UPDATE`, etc.
* Long SQL statements should be logically broken up into multiple lines.
* SQL INSERT statements should always specify the column names.
* For example:
```sql
	INSERT INTO mytable (x, y, z)
	VALUES (?, ?, ?)
```
* All SQL queries should be compatible with at least the versions of MySQL and
  PostgreSQL supported by the application. Although vendor-specific SQL
  expressions should be avoided, a record should be kept of any non-portable SQL
  that does get used (e.g., by filing a bug report).


## OHS Conventions

### git

* A web interface to the git repository is located at https://github.com/pkp.
* A brief log message describing the changes made must be included with all git
  commits.
* Whenever possible, git commit log messages should be prefixed with
  `pkp/pkp-lib#ISSUENUM` to reference a git issue; see
  https://github.com/pkp/pkp-lib#issues.
* Please consult https://pkp.sfu.ca/wiki/index.php/HOW-TO_check_out_PKP_applications_from_git
  for instructions on setting up a development environment.


### File Header

* PHP files should begin with a header similar to the following. Non-PHP files
  should begin with a similar header adapted to the appropriate comment style.

```php
/**
 * @file /path/to/filename.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package PACKAGE
 * @class CLASS
 *
 * DESCRIPTION.
 */
```

### Database Queries

* SQL queries should use the ADOdb or Laravel abstraction layer.
* SQL should use placeholders for variables.
* Explicit typecasts should be used where possible in variable replacements.
* For example:
```
	$dbconn = DBConnection::getConn();
	$result = $dbconn->execute('SELECT x FROM mytable WHERE y = ?', array($y));
	$result = $dbconn->execute('INSERT INTO mytable (x, y) VALUES (?, ?)', array((int) $x, $y));
```
* Only portable, standards compliant SQL should be used - compatibility with
  MySQL and PostgreSQL (versions as per README) is required. If database
  specific logic cannot be avoided it should be abstracted into DBConnection or
  ADOdb.


### Direct Access Objects

* DAO classes should be used to encapsulate all database calls.
* For example:
	`$sessionDao = DAORegistry::getDAO('SessionDAO');`
* DAO classes are expected to handle date/datetime format conversion between the
  database and PHP, and insertion ID retrieval for sequenced records;
  abstractions are provided in the base DAO class.


### Templates

* HTML output in PHP code should be kept to a minimum.
* HTML output should come from the Smarty template abstraction layer.
* The template engine supports basic conditional logic and loops and can access
  objects and arrays, but the complexity of the business logic used in templates
  should be minimized.
* Basic template skeleton:
```
	{**
	 * /path/to/filename.tpl
	 *
	 * Copyright (c) 2014-2019 Simon Fraser University
	 * Copyright (c) 2003-2019 John Willinsky
	 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
	 *
	 * DESCRIPTION.
	 *}
	{include file="common/header.tpl" pageTitle="user.userHome"}
	...
	{include file="common/footer.tpl"}
```

### Localization

* i18n strings are defined in locale/<locale_key>/locale.xml.
* Key names should be in the form "sectionname(.subsectionname)*.name".
  E.g., "manager.setup.journalTitle"
* Use {translate key="my.key.name"} in templates to translate i18n keys.
* Use the String wrapper class in place of the built-in string
  manipulation/regexp routines when handling data that could potentially be in
  UTF-8 (e.g., user input, parsed user files, etc.).


### Input Validation

* User input should be properly validated/escaped (do not rely on
  magic_quotes_gpc being on or off).
* For example, in template forms:
	`<input type="text" name="title" value="{$title|escape}" />`
* Retrieving user input:
	`$foo = $request->getUserVar('foo');`
* Escaping habits in order of precedence:
  - Manager's setup text fields: No escaping performed
  - Abstracts, notes, comments, emails: {$field|strip_unsafe_html|nl2br}
  - Database IDs safely fetched from DB: no escaping necessary.
  - Mailing Address fields: {$mailingAddress|escape|nl2br}
  - Biography fields: {$biography|escape|nl2br}
  - Custom issue or article IDs in URLs: {$pageUrl}/.../{$customId|escape:"url"}
  - Date fields: Use date_format.
  - Comment fields: {$comment|strip_unsafe_html}
  - Multi-line fields inside textarea tags: {$field|escape}
  - Multi-line input fields that are filled in by the Manager or Site Administrator: {$field|nl2br}
  - All other fields: {$field|escape}

Note that these should apply to parameters supplied to {translate key="..."} and
{mailto address="..."} calls, e.g
{translate key="my.key.takes.parameter" myParam=$myVar|escape}


## Other Tips

* Use `$request->redirectUrl($url)`, or better yet, `$request->redirect(...)` for
  HTTP redirects instead of `header('Location: ...');`
* For additional coding convention information, see the OHS Design Document
  at https://pkp.sfu.ca/wp-content/uploads/2014/04/TechnicalReference.pdf.
