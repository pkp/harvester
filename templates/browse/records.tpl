{**
 * records.tpl
 *
 * Copyright (c) 2005-2006 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Browse page for a specific archive or all records in all archives.
 *
 * $Id$
 *}

{* Make the quick search form limit itself to this archive by default *}
{if $archive}{assign var="theseArchiveIds" value=$archive->getArchiveId()|to_array}{/if}

{assign var="pageTitle" value="record.records"}
{assign var="helpTopicId" value="index.browse"}
{include file="common/header.tpl"}

{if $archive}
	{assign var=archiveId value=$archive->getArchiveId()}
	<h3>{$archive->getTitle()|escape}</h3>
	<p><a class="action" href="{url op=archiveInfo path=$archive->getArchiveId()}">{translate key="browse.archiveInfo"}</a></p>
{else}
	{assign var=archiveId value="all"}
{/if}

{if $sortableCrosswalks}
	{translate key="browse.sortBy"}:
	{iterate from=sortableCrosswalks item=crosswalk}{if $notFirstCrosswalk}&nbsp;|&nbsp;{/if}{if $sortId == $crosswalk->getCrosswalkId()}{assign var=matchedSort value=1}<strong>{else}<a href="{url path=$archiveId sortId=$crosswalk->getCrosswalkId()}" class="action">{/if}{$crosswalk->getName()|escape}{if $sortId == $crosswalk->getCrosswalkId()}</strong>{else}</a>{/if}{assign var=notFirstCrosswalk value=1}{/iterate}{if $notFirstCrosswalk}&nbsp;|&nbsp;{/if}{if !$matchedSort}<strong>{else}<a href="{url path=$archiveId sortId="none"}" class="action">{/if}{translate key="browse.sortBy.none"}{if !$matchedSort}</strong>{else}</a>{/if}
{/if}
{if $sortableFields}
	{translate key="browse.sortBy"}:
	{foreach from=$sortableFields item=field}{if $notFirstField}&nbsp;|&nbsp;{/if}{if $sortId == $field->getFieldId()}{assign var=matchedSort value=1}<strong>{else}<a href="{url path=$archiveId sortId=$field->getFieldId()}" class="action">{/if}{$field->getDisplayName()|escape}{if $sortId == $field->getFieldId()}</strong>{else}</a>{/if}{assign var=notFirstField value=1}{/foreach}{if $notFirstField}&nbsp;|&nbsp;{/if}{if !$matchedSort}<strong>{else}<a href="{url path=$archiveId sortId="none"}" class="action">{/if}{translate key="browse.sortBy.none"}{if !$matchedSort}</strong>{else}</a>{/if}
{/if}

<br />&nbsp;

<a name="records"></a>

<ul class="plain">
{iterate from=records item=record}
	<li>&#187; {$record->displaySummary()}</li>
{/iterate}
</ul>
	{page_info iterator=$records}&nbsp;&nbsp;&nbsp;&nbsp;{page_links anchor="records" name="records" sortId=$sortId iterator=$records}

{include file="common/footer.tpl"}
