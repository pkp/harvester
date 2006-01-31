{**
 * archiveForm.tpl
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Basic archive settings under site administration.
 *
 * $Id$
 *}

{if $archiveId}
	{assign var="pageTitle" value="admin.archives.editArchive"}
{else}
	{assign var="pageTitle" value="admin.archives.addArchive"}
{/if}

{include file="common/header.tpl"}

{if $archiveId}
<ul class="menu">
	<li class="current"><a href="{url op="editArchive" path=$archiveId}">{translate key="admin.archives.editArchive"}</a></li>
	<li><a href="{url op="manage" path=$archiveId}">{translate key="admin.archives.manage"}</a></li>
</ul>
{/if}

<br />

<script type="text/javascript">
<!--

{literal}
function selectHarvester() {
	document.archiveForm.action="{/literal}{if $archiveId}{url op="editArchive" anchor="archiveForm" path=$archiveId escape=false}{else}{url op="editArchive" anchor="archiveForm" escape=false}{/if}{literal}";
	document.archiveForm.submit();
}

{/literal}
// -->
</script>

<a name="archiveForm"/>
<form name="archiveForm" method="post" action="{url op="updateArchive"}">
{if $archiveId}
<input type="hidden" name="archiveId" value="{$archiveId}" />
{/if}

{include file="common/formErrors.tpl"}

<table class="data" width="100%">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="title" key="archive.title" required="true"}</td>
		<td width="80%" class="value"><input type="text" id="title" name="title" value="{$title|escape}" size="40" maxlength="120" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="description" key="archive.description"}</td>
		<td class="value"><textarea name="description" id="description" cols="40" rows="10" class="textArea">{$description|escape}</textarea></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="url" key="archive.url" required="true"}</td>
		<td class="value">
			<input type="text" id="url" name="url" value="{$url|escape}" size="40" maxlength="120" class="textField" />
			<br/>
			{translate key="admin.archives.form.url.description"}
		</td>
	</tr>

		<tr>
			<td class="label">{fieldLabel name="harvester" key="archive.type" required="true"}</td>
			<td><select onchange="selectHarvester()" name="harvesterPlugin" id="harvesterPlugin" size="1" class="selectMenu">
				{foreach from=$harvesters item=harvester}
					<option {if $harvester->getName() == $harvesterPlugin}selected="selected" {/if}value="{$harvester->getName()}">{$harvester->getProtocolDisplayName()}</option>
				{/foreach}
			</select></td>
		</tr>

		{call_hook name="Template::Admin::Archives::displayHarvesterForm" plugin=$harvesterPlugin}
</table>

<p><input type="submit" value="{translate key="common.save"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="archives"}'" /></p>

</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{include file="common/footer.tpl"}
