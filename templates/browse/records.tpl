{**
 * records.tpl
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Browse page for a specific archive or all records in all archives.
 *
 * $Id$
 *}
{* Make the quick search form limit itself to this archive by default *}
{strip}
{assign var="pageTitle" value="record.records"}
{assign var="helpTopicId" value="index.browse"}
{include file="common/header.tpl"}
{/strip}

{if $archive}
	{assign var=archiveId value=$archive->getArchiveId()}
	<h3>{$archive->getTitle()|escape}</h3>
	<p><a class="action" href="{url op=archiveInfo path=$archive->getArchiveId()}">{translate key="browse.archiveInfo"}</a></p>
{else}
	{assign var=archiveId value="all"}
{/if}

<a name="records"></a>

<ul class="plain">
{iterate from=records item=record}
	<li>&#187; {$record->displaySummary()}</li>
{/iterate}
</ul>
	{page_info iterator=$records}&nbsp;&nbsp;&nbsp;&nbsp;{page_links anchor="records" name="records" sortId=$sortId iterator=$records}

{include file="common/footer.tpl"}
