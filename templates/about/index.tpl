{**
 * index.tpl
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the site.
 *
 * $Id$
 *}

{assign var="pageTitle" value="navigation.about"}
{include file="common/header.tpl"}
{if !empty($about)}
	<p>{$about|nl2br}</p>
{/if}

<a href="{$pageUrl}/about/harvester">{translate key="about.harvester"}</a>

{include file="common/footer.tpl"}
