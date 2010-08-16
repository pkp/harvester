<!-- templates/about/index.tpl -->

{**
 * index.tpl
 *
 * Copyright (c) 2005-2010 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the site.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="navigation.about"}
{include file="common/header.tpl"}
{/strip}

{if !empty($about)}
	<p>{$about|nl2br}</p>
{/if}

<a href="{url op="harvester"}">{translate key="about.harvester"}</a>

{include file="common/footer.tpl"}

<!-- / templates/about/index.tpl -->

