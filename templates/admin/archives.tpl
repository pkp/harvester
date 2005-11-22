{**
 * archives.tpl
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of archives in site administration.
 *
 * $Id$
 *}

{assign var="pageTitle" value="admin.archives"}
{include file="common/header.tpl"}

<br />

<table width="100%" class="listing">
	<tr>
		<td colspan="3" class="headseparator">&nbsp;</td>
	</tr>
	<tr valign="top" class="heading">
		<td width="40%">{translate key="archive.title"}</td>
		<td width="40%">{translate key="archive.url"}</td>
		<td width="20%" align="right">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td colspan="3" class="headseparator">&nbsp;</td>
	</tr>
	{iterate from=archives item=archive}
	<tr valign="top">
		<td>{$archive->getTitle()|escape}</td>
		<td><a href="{$archive->getUrl()|escape:"quotes"}" target="_new">{$archive->getUrl()|escape}</a></td>
		<td align="right"><a href="{$pageUrl}/admin/editArchive/{$archive->getArchiveId()}" class="action">{translate key="common.edit"}</a> <a class="action" href="{$pageUrl}/admin/deleteArchive/{$archive->getArchiveId()}" onclick="return confirm('{translate|escape:"javascript" key="admin.archives.confirmDelete"}')">{translate key="common.delete"}</a></td>
	</tr>
	<tr>
		<td colspan="3" class="{if $smarty.foreach.archives.last}end{/if}separator">&nbsp;</td>
	</tr>
	{/iterate}
	{if $archives->wasEmpty()}
	<tr>
		<td colspan="3" class="nodata">{translate key="admin.archives.noneCreated"}</td>
	</tr>
	<tr>
		<td colspan="3" class="endseparator">&nbsp;</td>
	<tr>
	{else}
		<tr>
			<td colspan="2" align="left">{page_info iterator=$archives}</td>
			<td colspan="2" align="right">{page_links name="archives" iterator=$archives}</td>
		</tr>
	{/if}
</table>

<p><a href="{$pageUrl}/admin/createArchive" class="action">{translate key="admin.archives.addArchive"}</a></p>

{include file="common/footer.tpl"}
