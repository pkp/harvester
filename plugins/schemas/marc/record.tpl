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

<h3>{$entries.title|escape}</h3>
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
	{foreach from=$entries item=value key=name}
		<tr valign="top">
			<td>{translate key="plugins.schemas.marc.fields.$name.name"}</td>
			<td>
				{if is_array($value)}
					{foreach from=$value item=element}
						{$element|escape|default:"&mdash;"}<br/>
					{/foreach}
				{else}
					{$value|escape|default:"&mdash;"}<br/>
				{/if}
			</td>
		</tr>
		<tr>
			<td colspan="3" class="{if $smarty.foreach.searchableFields.last}end{/if}separator">&nbsp;</td>
		</tr>
	{/foreach}
</table>

{include file="common/footer.tpl"}
