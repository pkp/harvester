{**
 * records.tpl
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Browse page for a specific archive or all records in all archives.
 *
 * $Id$
 *}

{assign var="pageTitle" value="record.records"}
{assign var="helpTopicId" value="index.browse"}
{include file="common/header.tpl"}

{if $archive}<h3>{$archive->getTitle()|escape}</h3>{/if}
<br />

<ul class="plain">
{iterate from=records item=record}
	{$record->getArchive()|assign:"archive"}
	<li>&#187; {$record->displaySummary()}</li>
{/iterate}
</ul>
	{page_info iterator=$records}&nbsp;&nbsp;&nbsp;&nbsp;{page_links name="records" iterator=$records}

{include file="common/footer.tpl"}
