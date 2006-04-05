{**
 * record.tpl
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * View a MODS record.
 *
 * $Id$
 *}

{assign var="pageTitle" value="record.viewRecord"}
{include file="common/header.tpl"}

<h3>{$entries.title.value|escape}</h3>
<h4>{$archive->getTitle()|escape}</h4>

<a href="{url page="browse" op="archiveInfo" path=$archive->getArchiveId()}" class="action">{translate key="browse.archiveInfo"}</a><br/>&nbsp;

<table width="100%" class="listing">
	<tr>
		<td colspan="3" class="headseparator">&nbsp;</td>
	</tr>
	<tr valign="top" class="heading">
		<td width="25%">{translate key="record.field"}</td>
		<td width="75%">{translate key="record.value"}</td>
	</tr>
	<tr>
		<td colspan="3" class="headseparator">&nbsp;</td>
	</tr>
	
	<tr valign="top">
		<td>{translate key="plugins.schemas.mods.fields.title.name"}</td>
		<td>
			{$title.title|escape|default:"&mdash;"}<br/>
		</td>
	</tr>
	<tr>
		<td colspan="3" class="endseparator">&nbsp;</td>
	</tr>
	{foreach from=$authors item=author name=authors}
		<tr valign="top">
			<td>{translate key="plugins.schemas.mods.fields.name.name"}</td>
			<td>
				{foreach from=$author key=key item=value}
					{if $key == 'roleText' || $key == 'roleCode'}{assign var=key value=role}{/if}
					{translate key="plugins.schemas.mods.fields.$key.name"}: {$value|escape|default:"&mdash;"}<br/>
				{/foreach}
			</td>
		</tr>
		<tr>
			<td colspan="3" class="{if $smarty.foreach.authors.last}end{/if}separator">&nbsp;</td>
		</tr>
	{/foreach}
	{foreach from=$entries item=value name=entries key=name}
		{assign var=isTitleOrAuthor value=0}
		{foreach from=$value item=element}
			{if $element.attributes.titleAssocId || $element.attributes.nameAssocId}
				{assign var=isTitleOrAuthor value=1}
			{/if}
		{/foreach}
		{if !$isTitleOrAuthor}
		<tr valign="top">
			<td>{translate key="plugins.schemas.mods.fields.$name.name"}</td>
			<td>
				{foreach from=$value item=element}
					{$element.value|escape|default:"&mdash;"}<br/>
				{/foreach}
			</td>
		</tr>
		<tr>
			<td colspan="3" class="{if $smarty.foreach.entries.last}end{/if}separator">&nbsp;</td>
		</tr>
		{/if}
	{/foreach}
</table>

{include file="common/footer.tpl"}
