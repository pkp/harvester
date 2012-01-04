{**
 * header.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common header for help pages.
 *
 * $Id$
 *}
{strip}
{translate|assign:"defaultTitle" key="common.harvester2"}
{translate|assign:"applicationHelpTranslated" key="help.harvesterHelp" siteTitle=$siteTitle|default:$defaultTitle}
{include file="core:help/header.tpl"}
{/strip}