{**
 * crosswalkForm.tpl
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Basic crosswalk settings under site administration.
 *
 * $Id$
 *}

{if $crosswalkId}
	{assign var="pageTitle" value="admin.crosswalks.editCrosswalk"}
{else}
	{assign var="pageTitle" value="admin.crosswalks.addCrosswalk"}
{/if}
{include file="common/header.tpl"}

<script type="text/javascript">
<!--

{literal}

function chooseSchemaPluginName() {
	document.crosswalkForm.action="{/literal}{if $crosswalkId}{url op="editCrosswalk" anchor="crosswalkForm" path=$crosswalkId escape=false}{else}{url op="editCrosswalk" anchor="crosswalkForm" escape=false}{/if}{literal}";
	document.crosswalkForm.submit();
}

{/literal}

// -->
</script>

<br />

<a name="crosswalkForm"/>
<form name="crosswalkForm" method="post" action="{url op="updateCrosswalk"}">
{if $crosswalkId}
<input type="hidden" name="crosswalkId" value="{$crosswalkId}" />
{/if}

{include file="common/formErrors.tpl"}

<table class="listing" width="100%">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="name" key="admin.crosswalks.crosswalk.name" required="true"}</td>
		<td width="80%" class="value"><input type="text" id="name" name="name" value="{$name|escape}" size="40" maxlength="120" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="description" key="admin.crosswalks.crosswalk.description" required="true"}</td>
		<td class="value"><textarea name="description" id="description" cols="40" rows="10" class="textArea">{$description|escape}</textarea></td>
	</tr>

	<tr>
		<td colspan="2" class="headseparator">&nbsp;</td>
	</tr>

	<tr valign="top">
		<td colspan="2">
			{translate key="admin.crosswalks.schemaFilter"}&nbsp;&nbsp;<select name="schemaPluginName" onchange="chooseSchemaPluginName()" class="selectMenu">
				<option value="">{translate key="admin.crosswalks.schemaFilter.all"}</option>
				{foreach from=$schemaPlugins item=schemaPlugin}
					<option {if $schemaPluginName==$schemaPlugin->getName()}selected="selected" {/if}value="{$schemaPlugin->getName()|escape}">{$schemaPlugin->getSchemaDisplayName()|escape}</option>
				{/foreach}
			</select>
		</td>
	</tr>

	<tr>
		<td colspan="2" class="headseparator">&nbsp;</td>
	</tr>

	{if $schemaPluginName && $schemaPlugins[$schemaPluginName]|assign:"schemaPlugin":true}
		<tr valign="top">
			<td>{$schemaPlugin->getSchemaDisplayName()}</td>
			<td>
				{foreach from=$schemaPlugin->getFieldList() item=field}
					<input type="checkbox" class="checkbox" name="{$schemaPlugin->getName()|escape}-{$field|escape}" value=""/>&nbsp;{$schemaPlugin->getFieldName($field)|escape}<br/>
				{/foreach}
			</td>
		</tr>
		<tr>
			<td colspan="2" class="endseparator">&nbsp;</td>
		</tr>
	{else}
		{foreach from=$schemaPlugins item=schemaPlugin name="schemaPlugins"}
			<tr valign="top">
				<td>{$schemaPlugin->getSchemaDisplayName()}</td>
				<td>
					{foreach from=$schemaPlugin->getFieldList() item=field}
						<input type="checkbox" class="checkbox" name="{$schemaPlugin->getName()|escape}-{$field|escape}" value=""/>&nbsp;{$schemaPlugin->getFieldName($field)|escape}<br/>
					{/foreach}
				</td>
			</tr>
			<tr>
				<td colspan="2" class="{if $smarty.foreach.schemaPlugins.last}end{/if}separator">&nbsp;</td>
			</tr>
		{/foreach}
	{/if}

	{call_hook name="Template::Admin::Crosswalks::displayHarvesterForm" plugin=$harvesterPlugin}
</table>

<p><input type="submit" value="{translate key="common.save"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="crosswalks"}'" /></p>

</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{include file="common/footer.tpl"}
