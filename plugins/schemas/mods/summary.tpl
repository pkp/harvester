{**
 * summary.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a summary of a MODS record.
 *
 * $Id$
 *}
{assign var=contents value=$record->getParsedContents()}
<span class="title">{$contents.title|escape|default:"&mdash"}</span><br />
<div class="recordContents">
	<span class="author">
	{strip}
		{assign var=isFirstAuthor value=1}
		{foreach from=$contents.names item=name}
			{assign var=isAuthor value=0}
			{foreach from=$name.roles item=role}
				{if $role.term == 'author'}
					{assign var=isAuthor value=1}
				{/if}
			{/foreach}{* roles *}
			{if $isAuthor}
				{if $isFirstAuthor}
					{assign var=isFirstAuthor value=0}
				{else},&nbsp;
				{/if}
				{$name.namePart|escape|default:"&mdash;"}
			{/if}
		{/foreach}{* names *}
	{/strip}
	</span><br/>
	{$contents.originInfo.dateIssued.value|escape}<br />
	<a href="{url page="record" op="view" path=$record->getRecordId()}" class="action">{translate key="browse.viewRecord"}</a>{if $contents.identifier}&nbsp;|&nbsp;<a href="{$contents.identifier}" class="action">{translate key="browse.viewOriginal"}</a>{/if}
</div>
