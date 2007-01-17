{**
 * summary.tpl
 *
 * Copyright (c) 2005-2007 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a summary of a MODS record.
 *
 * $Id$
 *}

<span class="title">{$title.title|escape|replace:"\n":"; "|truncate:90|default:"&mdash"}</span><br />

<div class="recordContents">
	{foreach from=$authors key=nameAssocId item=author}
		<span class="author">{if $author.nonSort}{$author.nonSort|escape}{/if}{$author.namePart|escape|default:"&mdash;"}{if $author.roleText} ({$author.roleText|escape}){elseif $author.roleCode}({$author.roleCode|escape}){/if}{if $author.affiliation}; {$author.affiliation|escape}{/if}</span><br />
	{/foreach}
	{$record->getDatestamp()|date_format:$dateFormatShort}<br />
	<a href="{url page="record" op="view" path=$record->getRecordId()}" class="action">{translate key="browse.viewRecord"}</a>{if $url}&nbsp;|&nbsp;<a href="{$url}" class="action">{translate key="browse.viewOriginal"}</a>{/if}
</div>
