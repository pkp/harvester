{**
 * management.tpl
 *
 * Copyright (c) 2005-2006 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Archive statistics & management page
 *
 * $Id$
 *}

{assign var="pageTitle" value="admin.archives.manage"}

{assign var="helpTopicId" value="admin.manage"}
{include file="common/header.tpl"}

<ul class="menu">
	<li><a href="{url op="editArchive" path=$archiveId}">{translate key="admin.archives.editArchive"}</a></li>
	<li class="current"><a href="{url op="manage" path=$archiveId}">{translate key="admin.archives.manage"}</a></li>
</ul>

<br />

<form method="post" action="{url op="updateIndex" path=$archiveId}">
<table class="listing" width="100%">
	<tr valign="top"><td colspan="3" class="headseparator">&nbsp;</td></tr>
	<tr valign="top">
		<td width="20%" class="label">{translate key="archive.title"}</td>
		<td width="80%" colspan="2" class="value">{$title|escape}</td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{translate key="archive.recordCount"}</td>
		<td width="80%" colspan="2" class="value">{$numRecords|default:"&mdash;"}</td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{translate key="archive.lastIndexed"}</td>
		<td width="80%" colspan="2" class="value">{$lastIndexed|date_format:$dateFormatShort|default:"&mdash;"}</td>
	</tr>
	<tr valign="top"><td colspan="3" class="headseparator">&nbsp;</td></tr>

	<tr valign="top">
		<td class="label"><label for="set">{translate key="plugins.harvesters.oai.archive.form.sets"}</td>
		<td colspan="2" class="value">
			<select class="selectMenu" size="5" multiple name="set[]" id="set">
				<option {if empty($selectedSets)}selected {/if}value="">{translate key="plugins.harvesters.oai.archive.form.allSets"}</option>
				{foreach from=$availableSets key=setSpec item=setName}
					<option {if in_array($setSpec, $availableSets)}selected {/if}value="{$setSpec|escape}">{$setName|escape}</option>
				{/foreach}
			</select><br />
			<input type="button" class="button" value="{translate key="common.refresh"}" onclick="selectHarvester()"/>
		</td>
	</tr>

	<tr valign="top">
		<td colspan="3" class="separator">&nbsp;</td>
	</tr>

	<tr valign="top">
		<td rowspan="2" class="label">{translate key="plugins.harvesters.oai.archive.form.dates"}</td>
		<td class="value">{translate key="common.from"}:</td>
		<td class="value">
			{html_select_date prefix="from" time=$lastIndexed all_extra="class=\"selectMenu\"" year_empty="" month_empty="" day_empty="" start_year="1900" end_year="+10"}
		</td>
	</tr>
	<tr valign="top">
		<td class="value">{translate key="common.until"}:</td>
		<td class="value">
			{html_select_date prefix="until" time="--" all_extra="class=\"selectMenu\"" year_empty="" month_empty="" day_empty="" start_year="1900" end_year="+10"}
		</td>
	</tr>

	<tr valign="top">
		<td colspan="3" class="headseparator">&nbsp;</td>
	</tr>
</table>

<input type="submit" class="button defaultButton" onclick="return confirm('{translate|escape:"javascript" key="admin.archives.manage.updateIndex.confirm"}')" value="{translate key="admin.archives.manage.updateIndex"}"/>
<input type="button" onclick="document.location='{url op="flushIndex" path=$archiveId}'" value="{translate key="admin.archives.manage.flush"}" class="button" />
<input type="button" value="{translate key="common.cancel"}" onclick="history.go(-1)" class="button"/>
</form>

{include file="common/footer.tpl"}

