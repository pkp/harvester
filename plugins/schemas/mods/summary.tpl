{**
 * summary.tpl
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a summary of a MODS record.
 *
 * $Id$
 *}

{* Title *}
{assign var=notFirstTitle value=0}
{foreach from=$entries.title item=entry}
	{* Find the first non-empty title *}
	{if !$notFirstTitle && !empty($entry.value)}
		{assign var=notFirstTitle value=1}
		<span class="title">{$entry.value|escape|truncate:90|default:"&mdash"}</span><br />
	{/if}
{/foreach}

<div class="recordContents">
	{foreach from=$entries.creator item=creator}<span class="author">{$creator.value|escape|default:"&mdash;"}</span><br />{/foreach}
	{$record->getDatestamp()|date_format:$dateFormatShort}<br />
	<a href="{url page="record" op="view" path=$record->getRecordId()}" class="action">{translate key="browse.viewRecord"}</a>{if $record->getUrl($entries)|assign:"recordUrl":true}&nbsp;|&nbsp;<a href="{$recordUrl}" class="action">{translate key="browse.viewOriginal"}</a>{/if}
</div>
