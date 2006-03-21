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
{assign var="helpTopicId" value="admin.archives"}
{include file="common/header.tpl"}

<br />

<table width="100%" class="listing">
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
	<tr valign="top" class="heading">
		<td width="45%">{translate key="archive.title"}</td>
		<td width="35%">{translate key="archive.url"}</td>
		<td width="5%">{translate key="archive.type"}</td>
		<td width="15%" align="right">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
	{iterate from=archives item=archive}
	<tr valign="top">
		<td>{$archive->getTitle()|escape}</td>
		<td><a href="{$archive->getUrl()|escape}" target="_new">{$archive->getUrl()|escape|truncate:40}</a></td>
		<td>
			{assign var=pluginName value=$archive->getHarvesterPluginName()}
			{assign var=plugin value=$harvesters[$pluginName]}
			{if $plugin}
				{$plugin->getProtocolDisplayName()}
			{else}
				&mdash;
			{/if}
		</td>
		<td align="right"><a href="{url op="editArchive" path=$archive->getArchiveId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url op="manage" path=$archive->getArchiveId()}" class="action">{translate key="common.manage"}</a>&nbsp;|&nbsp;<a class="action" href="{url op="deleteArchive" path=$archive->getArchiveId()}" onclick="return confirm('{translate|escape:"javascript" key="admin.archives.confirmDelete"}')">{translate key="common.delete"}</a></td>
	</tr>
	<tr>
		<td colspan="4" class="{if $smarty.foreach.archives.last}end{/if}separator">&nbsp;</td>
	</tr>
	{/iterate}
	{if $archives->wasEmpty()}
	<tr>
		<td colspan="4" class="nodata">{translate key="admin.archives.noneCreated"}</td>
	</tr>
	<tr>
		<td colspan="4" class="endseparator">&nbsp;</td>
	<tr>
	{else}
		<tr>
			<td colspan="2" align="left">{page_info iterator=$archives}</td>
			<td colspan="2" align="right">{page_links name="archives" iterator=$archives}</td>
		</tr>
	{/if}
</table>

<p><a href="{url op="createArchive"}" class="action">{translate key="admin.archives.addArchive"}</a></p>

{include file="common/footer.tpl"}
