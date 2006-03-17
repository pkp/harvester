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
{literal}
<!--
function refreshForm() {
{/literal}
	document.crosswalkForm.action='{url}';
	document.crosswalkForm.submit();
{literal}
}
-->
{/literal}
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
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>

	<tr valign="top">
		<td class="label">{translate key="admin.crosswalks.crosswalk.type"}</td>
		<td class="value">
			{foreach from=$crosswalkTypes item=typeName key=typeId}
				<input {if $crosswalkType == $typeId}checked {/if}onclick="refreshForm()" name="crosswalkType" type="radio" value="{$typeId}">&nbsp;&nbsp;{translate key=$typeName}<br />
			{/foreach}
		</td>
	</tr>

	<tr>
		<td colspan="2" class="headseparator">&nbsp;</td>
	</tr>

	{foreach from=$filteredPlugins item=schemaPlugin name="schemaPlugins"}
		<tr valign="top">
			<td>{$schemaPlugin->getSchemaDisplayName()}</td>
			<td>
				{foreach from=$schemaPlugin->getFieldList() item=field}
					{assign var=fieldType value=$schemaPlugin->getFieldType($field)}
					{assign var=isFieldMixedType value=$schemaPlugin->isFieldMixedType($field)}
					{if $fieldType == $crosswalkType || $isFieldMixedType}
					{* Determine whether this field is already chosen *}
					{assign var=isFieldChosen value=0}
					{foreach from=$fields item=chosenField}
						{assign var=thisSchemaPlugin value=$chosenField->getSchemaPlugin()}
						{if $thisSchemaPlugin->getName() == $schemaPlugin->getName() && $field == $chosenField->getName()}
							{assign var=isFieldChosen value=1}
						{/if}
					{/foreach}
					<input type="hidden" name="{$schemaPlugin->getName()|escape}-{$field|escape}-displayed" value="1"/>
					<input type="checkbox" {if $isFieldChosen}checked="checked" {/if}class="checkbox" name="{$schemaPlugin->getName()|escape}-{$field|escape}" value="1"/>&nbsp;<strong>{$schemaPlugin->getFieldName($field)|escape}</strong>:&nbsp;{$schemaPlugin->getFieldDescription($field)|escape}<br/>
					{/if}
				{/foreach}
			</td>
		</tr>
		<tr>
			<td colspan="2" class="{if $smarty.foreach.schemaPlugins.last}end{/if}separator">&nbsp;</td>
		</tr>
	{/foreach}

	{call_hook name="Template::Admin::Crosswalks::displayHarvesterForm" plugin=$harvesterPlugin}
</table>

<label for="schemaPluginName">{translate key="admin.crosswalks.schemaFilter"}:</label> <select id="schemaPluginName" name="schemaPluginName" class="selectMenu" onchange="refreshForm()">
	<option value="">{translate key="admin.crosswalks.schemaFilter.all"}</option>
	{foreach from=$schemaPlugins item=schemaPlugin}
		<option {if $schemaPlugin->getName() == $schemaPluginName}selected {/if}value="{$schemaPlugin->getName()|escape}">{$schemaPlugin->getSchemaDisplayName()}</option>
	{/foreach}
</select>

<p><input type="submit" value="{translate key="common.save"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="crosswalks"}'" /></p>

</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{include file="common/footer.tpl"}
