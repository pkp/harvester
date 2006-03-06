{**
 * manage.tpl
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Archive statistics & management page
 *
 * $Id$
 *}

{assign var="pageTitle" value="admin.archives.manage"}

{include file="common/header.tpl"}

<ul class="menu">
	<li><a href="{url op="editArchive" path=$archiveId}">{translate key="admin.archives.editArchive"}</a></li>
	<li class="current"><a href="{url op="manage" path=$archiveId}">{translate key="admin.archives.manage"}</a></li>
</ul>

<br />

<table class="data" width="100%">
	<tr valign="top">
		<td width="20%" class="label">{translate key="archive.title"}</td>
		<td width="80%" class="value">{$title|escape}</td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{translate key="admin.archives.manage.numRecords"}</td>
		<td width="80%" class="value">{$numRecords|default:"&mdash;"}</td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{translate key="admin.archives.manage.lastIndexed"}</td>
		<td width="80%" class="value">{$lastIndexed|date_format:$dateFormatShort|default:"&mdash;"}</td>
	</tr>
	{call_hook name="Template::Admin::Archives::manage" plugin=$archive->getHarvesterPluginName()}
</table>

<form method="post" action="{url op="updateIndex" path=$archiveId}"><input type="submit" class="button defaultButton" onclick="return confirm('{translate|escape:"javascript" key="admin.archives.manage.updateIndex.confirm"}')" value="{translate key="admin.archives.manage.updateIndex"}"/> <input type="button" onclick="document.location='{url op="flushIndex" path=$archiveId}'" value="{translate key="admin.archives.manage.flush"}" class="button" /> <input type="button" value="{translate key="common.cancel"}" onclick="history.go(-1)" class="button"/></form>

{include file="common/footer.tpl"}
