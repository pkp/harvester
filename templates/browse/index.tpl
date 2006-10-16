{**
 * index.tpl
 *
 * Copyright (c) 2005-2006 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Browse page.
 *
 * $Id$
 *}

{assign var="pageTitle" value="navigation.browse"}
{assign var="helpTopicId" value="index.browse"}
{include file="common/header.tpl"}

<br />

<ul class="plain">
	<li>&#187; <a href="{url path="all"}">{translate key="browse.browseAll"}</a><br/>&nbsp;</li>
{iterate from=archives item=archive}
	<li>&#187; <a href="{url path=$archive->getArchiveId()}">{$archive->getTitle()|escape}</a> {translate key="browse.recordCount" count=$archive->getRecordCount()}</li>
{/iterate}
{if $archives->wasEmpty()}
	<li>{translate key="admin.archives.noneCreated"}</li>
</ul>
{else}
</ul>
	{page_info iterator=$archives}&nbsp;&nbsp;&nbsp;&nbsp;{page_links name="archives" iterator=$archives}
{/if}

{include file="common/footer.tpl"}
