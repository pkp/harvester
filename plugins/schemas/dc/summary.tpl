{**
 * summary.tpl
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a summary of a Dublin Core record.
 *
 * $Id$
 *}

<span class="title">{$entries.title|escape}</span><br />
<div class="recordContents">
	{if $entries.creator}{if is_array($entries.creator)}{foreach from=$entries.creator item=creator}FIXME: IT'S AN ARRAY{/foreach}{else}<span class="author">{$entries.creator|escape}</span>{/if}<br />{/if}
	{$record->getDatestamp()|date_format:$dateFormatShort}<br />
	<a href="{url page="viewRecord" path=$record->getRecordId()}" class="action">{translate key="browse.viewRecord"}</a>{if $entries.identifier}&nbsp;|&nbsp;<a href="{$entries.identifier}" class="action">{translate key="browse.viewOriginal"}</a>{/if}
</div>
