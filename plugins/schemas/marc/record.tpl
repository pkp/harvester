{**
 * record.tpl
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * View a Marc record.
 *
 * $Id$
 *}

{assign var="pageTitle" value="record.viewRecord"}
{include file="common/header.tpl"}

<h3>{foreach from=$entries.245 item=entry}{$entry.value|escape}{/foreach}</h3>
<h4>{$archive->getTitle()|escape}</h4>

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
	{foreach from=$entries item=entry key=name}
		<tr valign="top">
			<td>{translate key="plugins.schemas.marc.fields.$name.name"}</td>
			<td>
				{foreach from=$entry item=value}{$value.value|escape|default:"&mdash;"}<br/>{/foreach}
			</td>
		</tr>
		<tr>
			<td colspan="3" class="{if $smarty.foreach.searchableFields.last}end{/if}separator">&nbsp;</td>
		</tr>
	{/foreach}
</table>

<br />

<a href="{url page="browse" op="archiveInfo" path=$archive->getArchiveId()}" class="action">{translate key="browse.archiveInfo"}</a>

{include file="common/footer.tpl"}
