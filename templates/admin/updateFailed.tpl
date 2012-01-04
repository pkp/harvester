{**
 * updateFailed.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a "metadata index update failed" message with details
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="admin.archives.manage.updateIndex"}
{assign var="helpTopicId" value="admin.manage"}
{include file="common/header.tpl"}
{/strip}
<div id="updateFailed">
<p>{translate key="admin.archive.manage.updateIndex.failure"}</p>
<ul>
{foreach from=$errors item=error}
	<li><span class="formError">{$error}</span></li>
{foreachelse}
	<li><span class="formError">{translate key="admin.archive.manage.updateIndex.failure.generic"}</span></li>
{/foreach}
</ul>

<a href="{url op="manage" path=$archiveId}">{translate key="admin.archive.manage.updateIndex.return"}</a>
</div>
{include file="common/footer.tpl"}
