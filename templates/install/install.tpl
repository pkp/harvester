{**
 * install.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Installation form.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="installer.harvester2Installation"}
{assign var="skipFilesDirSection" value=1}
{assign var="skipMiscSettings" value=1}
{include file="core:install/install.tpl"}
{/strip}