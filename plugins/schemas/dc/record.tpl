{**
 * record.tpl
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * View a Dublin Core record.
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
	{foreach from=$entries item=entry key=name name=entries}
		<tr valign="top">
			<td>{translate key="plugins.schemas.dc.fields.$name.name"}</td>
			<td>
				{foreach from=$entry item=value}
					{$value.value|escape|default:"&mdash;"}<br/>
				{/foreach}
			</td>
		</tr>
		<tr>
			<td colspan="3" class="{if $smarty.foreach.entries.last}end{/if}separator">&nbsp;</td>
		</tr>
	{/foreach}
</table>

{include file="common/footer.tpl"}
