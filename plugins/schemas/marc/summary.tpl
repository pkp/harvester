{**
 * summary.tpl
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a summary of a Marc record.
 *
 * $Id$
 *}

<span class="title">{$record->getTitle()|escape|truncate:90|default:"&mdash"}</span><br />
<div class="recordContents">
	<span class="author">{foreach from=$record->getAuthors() name="creators" item=creator}{$creator|escape|default:"&mdash;"}{if !$smarty.foreach.creators.last}</span><br /><span class="author">{/if}{/foreach}</span><br/>
	{$record->getDatestamp()|date_format:$dateFormatShort}<br />
	<a href="{url page="record" op="view" path=$record->getRecordId()}" class="action">{translate key="browse.viewRecord"}</a>{if $record->getUrl($entries)|assign:"recordUrl":true}&nbsp;|&nbsp;<a href="{$recordUrl}" class="action">{translate key="browse.viewOriginal"}</a>{/if}
</div>
