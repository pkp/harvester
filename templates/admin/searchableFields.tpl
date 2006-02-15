{**
 * searchableFields.tpl
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of searchable fields in site administration.
 *
 * $Id$
 *}

{assign var="pageTitle" value="admin.indexing"}
{include file="common/header.tpl"}

<br />

<table width="100%" class="listing">
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
	<tr valign="top" class="heading">
		<td width="25%">{translate key="admin.indexing.searchableField.name"}</td>
		<td width="60%">{translate key="admin.indexing.searchableField.description"}</td>
		<td width="15%" align="right">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
	{iterate from=searchableFields item=searchableField}
	<tr valign="top">
		<td>{$searchableField->getName()|escape}</td>
		<td>{$searchableField->getDescription()|escape}</td>
		<td align="right">{*<a href="{url op="manage" path=$archive->getArchiveId()}" class="action">{translate key="common.manage"}</a>&nbsp;|&nbsp;<a href="{url op="editArchive" path=$archive->getArchiveId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a class="action" href="{url op="deleteArchive" path=$archive->getArchiveId()}" onclick="return confirm('{translate|escape:"javascript" key="admin.archives.confirmDelete"}')">{translate key="common.delete"}</a>*}</td>
	</tr>
	<tr>
		<td colspan="4" class="{if $smarty.foreach.searchableFields.last}end{/if}separator">&nbsp;</td>
	</tr>
	{/iterate}
	{if $searchableFields->wasEmpty()}
	<tr>
		<td colspan="4" class="nodata">{translate key="admin.indexing.searchableFields.noneCreated"}</td>
	</tr>
	<tr>
		<td colspan="4" class="endseparator">&nbsp;</td>
	<tr>
	{else}
		<tr>
			<td colspan="2" align="left">{page_info iterator=$searchableFields}</td>
			<td colspan="2" align="right">{page_links name="searchableFields" iterator=$searchableFields}</td>
		</tr>
	{/if}
</table>

<p><a href="{url op="createSearchableField"}" class="action">{translate key="admin.indexing.addSearchableField"}</a></p>

{include file="common/footer.tpl"}
