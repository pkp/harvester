{**
 * records.tpl
 *
 * Copyright (c) 2005-2010 Alec Smecher and John Willinsky
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
	<h3><a href="{$archive->getUrl()|escape}">{$archive->getTitle()|escape}</a></h3>
	<p><a class="action" href="{url op=archiveInfo path=$archive->getArchiveId()}">{translate key="browse.archiveInfo"}</a></p>
{else}
	{assign var=archiveId value="all"}
{/if}

{* Sort orders *}
<p>
{assign var=isFirst value=1}
{iterate from=sortOrders item=sortOrder}
	{if $isFirst}
		{assign var=isFirst value=0}
	{else}
		&nbsp;|&nbsp;
	{/if}
	<a class="action" href="{url path=$archiveId sortOrderId=$sortOrder->getSortOrderId()}">{$sortOrder->getSortOrderName()|escape}</a>
{/iterate}
</p>

<div id="records">
<ul class="plain">
{iterate from=records item=record}
	<li>&#187; {$record->displaySummary()}</li>
{/iterate}
</ul>
	{page_info iterator=$records}&nbsp;&nbsp;&nbsp;&nbsp;{page_links anchor="records" name="records" sortOrderId=$sortOrderId iterator=$records}
</div>
{include file="common/footer.tpl"}
