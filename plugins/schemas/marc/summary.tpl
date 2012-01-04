{**
 * plugins/schemas/marc/summary.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a summary of a Marc record.
 *
 * $Id$
 *}
<span class="title">{$record->getTitle()|escape|truncate:90:"...":false:false:false|default:"&mdash"}</span><br />
<div class="recordContents">
	<span class="author">{foreach from=$record->getAuthors() name="creators" item=creator}{$creator|escape|default:"&mdash;"}{if !$smarty.foreach.creators.last}</span><br /><span class="author">{/if}{/foreach}</span><br/>
	{get_marc_element|assign:"date" record=$record id="260" i1=" " i2=" " label="c" firstOnly=true}
	{if $date}
		<span class="date">
			{$date|strtotime|date_format:$dateFormatShort}
		</span><br />
	{/if}
	<a href="{url page="record" op="view" path=$record->getRecordId()}" class="action">{translate key="browse.viewRecord"}</a>{if $record->getUrl()|assign:"recordUrl":true}&nbsp;|&nbsp;<a href="{$recordUrl}" class="action">{translate key="browse.viewOriginal"}</a>{/if}
</div>
