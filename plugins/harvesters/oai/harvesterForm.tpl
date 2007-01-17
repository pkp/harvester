{**
 * archiveForm.tpl
 *
 * Copyright (c) 2005-2007 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Basic archive settings under site administration.
 *
 * $Id$
 *}

	<tr valign="top">
		<td class="label">{fieldLabel name="harvesterUrl" key="plugins.harvesters.oai.archive.form.harvesterUrl" required="true"}</td>
		<td class="value">
			<input type="text" id="harvesterUrl" name="harvesterUrl" value="{$harvesterUrl|escape}" size="40" maxlength="120" class="textField" />&nbsp;<input type="button" value="{translate key="plugins.harvesters.oai.archive.form.fetchMetadata"}" class="button" onclick="if (confirm('{translate|escape:"quotes" key="plugins.harvesters.oai.archive.form.fetchMetadata.warning"}')) {literal}{{/literal}document.archiveForm.action='{url|escape:"quotes" op="plugin" path="harvesters"|to_array:$harvesterPluginName:"fetchArchiveInfo":$archiveId}';document.archiveForm.submit();{literal}}{/literal}" />
			<br/>
			{translate key="plugins.harvesters.oai.archive.form.harvesterUrl.description"}
		</td>
	</tr>
	<tr valign="top">
		<td class="label">
			{if $isUserLoggedIn}
				{fieldLabel name="adminEmail" key="plugins.harvesters.oai.archive.form.adminEmail"}
			{else}
				{fieldLabel name="adminEmail" key="plugins.harvesters.oai.archive.form.adminEmail" required="true"}
			{/if}
		</td>
		<td class="value"><input type="text" id="adminEmail" name="adminEmail" value="{$adminEmail|escape}" size="40" maxlength="120" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="oaiIndexMethod" key="plugins.harvesters.oai.archive.form.oaiIndexMethod" required="true"}</td>
		<td class="value">
			<select class="selectMenu" name="oaiIndexMethod" id="oaiIndexMethod">
				{html_options options=$oaiIndexMethods selected=$oaiIndexMethod}
			</select>
		</td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="metadataFormat" key="plugins.harvesters.oai.archive.form.metadataFormat" required="true"}</td>
		<td class="value">
			<select class="selectMenu" name="metadataFormat" id="metadataFormat">
				{html_options options=$metadataFormats selected=$metadataFormat}
			</select>&nbsp;<input type="button" class="button" value="{translate key="common.refresh"}" onclick="selectHarvester()"/>
		</td>
	</tr>
