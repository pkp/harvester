{**
 * index.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Reading Tool Administrator index.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="admin.rtAdmin"}
{assign var="helpTopicId" value="admin.rtAdmin"}
{include file="common/header.tpl"}
{/strip}
<div id="status">
<h3>{translate key="rt.admin.status"}</h3>
<form action="{url op="selectVersion" path=$archiveId}" method="post">
<p>{translate key="rt.admin.selectedVersion"}:&nbsp;<select name="versionId" class="selectMenu" id="versionId">
	<option value="">({if $archiveId}{translate key="common.default"}{else}{translate key="common.none"}{/if})</option>
	{iterate from=versions item=versionLoop}
		<option {if $version && $versionLoop->getVersionId() == $version->getVersionId()}selected {/if}value="{$versionLoop->getVersionId()}">{$versionLoop->getTitle()|escape}</option>
	{/iterate}
</select>&nbsp;&nbsp;<input type="submit" class="button defaultButton" value="{translate key="common.save"}"/></p>
</form>
</div>
<div id="config">
<p>{translate key="rt.admin.rtEnable"}</p>

<h3>{translate key="rt.admin.configuration"}</h3>
<ul class="plain">
	<li>&#187; <a href="{url op="versions" path=$archiveId}">{translate key="rt.versions"}</a></li>
</ul>

<h3>{translate key="rt.admin.management"}</h3>
<ul class="plain">
	<li>&#187; <a href="{url op="validateUrls" path="all"|to_array:$archiveId}">{translate key="rt.admin.validateUrls"}</a></li>
</ul>
</div>
{include file="common/footer.tpl"}
