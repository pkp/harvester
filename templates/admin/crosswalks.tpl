{**
 * crosswalks.tpl
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of searchable fields in site administration.
 *
 * $Id$
 *}

{assign var="pageTitle" value="admin.crosswalks"}
{include file="common/header.tpl"}

<br />

<table width="100%" class="listing">
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
	<tr valign="top" class="heading">
		<td width="25%">{translate key="admin.crosswalks.crosswalk.name"}</td>
		<td width="60%">{translate key="admin.crosswalks.crosswalk.description"}</td>
		<td width="15%" align="right">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
	{iterate from=crosswalks item=crosswalk}
	<tr valign="top">
		<td>{$crosswalk->getName()|escape}</td>
		<td>{$crosswalk->getDescription()|escape}</td>
		<td align="right"><a href="{url op="editCrosswalk" path=$crosswalk->getCrosswalkId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url op="deleteCrosswalk" path=$crosswalk->getCrosswalkId()}" onclick="return confirm('{translate|escape:"javascript" key="admin.crosswalks.confirmDelete"}')" class="action">{translate key="common.delete"}</a></td>
	</tr>
	<tr>
		<td colspan="4" class="{if $smarty.foreach.crosswalks.last}end{/if}separator">&nbsp;</td>
	</tr>
	{/iterate}
	{if $crosswalks->wasEmpty()}
	<tr>
		<td colspan="4" class="nodata">{translate key="admin.crosswalks.crosswalks.noneCreated"}</td>
	</tr>
	<tr>
		<td colspan="4" class="endseparator">&nbsp;</td>
	<tr>
	{else}
		<tr>
			<td colspan="2" align="left">{page_info iterator=$crosswalks}</td>
			<td colspan="2" align="right">{page_links name="crosswalks" iterator=$crosswalks}</td>
		</tr>
	{/if}
</table>

<p><a href="{url op="createCrosswalk"}" class="action">{translate key="admin.crosswalks.addCrosswalk"}</a></p>

{include file="common/footer.tpl"}
