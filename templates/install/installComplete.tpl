{**
 * installComplete.tpl
 *
 * Copyright (c) 2005-2010 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display confirmation of successful installation.
 * If necessary, will also display new config file contents if config file could not be written.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="installer.harvester2Installation"}
{include file="core:install/installComplete.tpl"}
{/strip}