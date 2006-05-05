{**
 * summary.tpl
 *
 * Copyright (c) 2005-2006 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a summary of a Dublin Core record.
 *
 * $Id$
 *}

<span class="title">{foreach name=title from=$entries.title item=entry}{$entry.value|escape|truncate:90|default:"&mdash"}{if !$smarty.foreach.title.last}<br/>{/if}{/foreach}</span><br />
<div class="recordContents">
	{foreach from=$entries.creator item=creator}<span class="author">{$creator.value|escape|default:"&mdash;"}</span><br />{/foreach}
	{$record->getDatestamp()|date_format:$dateFormatShort}<br />
	<a href="{url page="record" op="view" path=$record->getRecordId()}" class="action">{translate key="browse.viewRecord"}</a>{if $record->getUrl($entries)|assign:"recordUrl":true}&nbsp;|&nbsp;<a href="{$recordUrl}" class="action">{translate key="browse.viewOriginal"}</a>{/if}
</div>
