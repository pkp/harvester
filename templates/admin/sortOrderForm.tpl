{**
 * sortOrderForm.tpl
 *
 * Copyright (c) 2005-2010 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Sort order management form.
 *
 * $Id$
 *}
{strip}
{assign var="pageCrumbTitle" value="$sortOrderTitle"}
{if $sortOrderId}
	{assign var="pageTitle" value="admin.sortOrders.edit"}
{else}
	{assign var="pageTitle" value="admin.sortOrders.create"}
{/if}
{assign var="pageId" value="manager.sortOrders.sortOrderForm"}
{assign var="helpTopicId" value="admin.sortOrders"}
{include file="common/header.tpl"}
{/strip}

<br/>

<form name="sortOrder" method="post" action="{url op="updateSortOrder"}">
{if $sortOrderId}
<input type="hidden" name="sortOrderId" value="{$sortOrderId|escape}" />
{/if}

{include file="common/formErrors.tpl"}

<table class="data" width="100%">
{if count($formLocales) > 1}
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
		<td colspan="2" width="80%" class="value">
			{if $sortOrderId}{url|assign:"sortOrderUrl" op="editSortOrder" path=$sortOrderId}
			{else}{url|assign:"sortOrderUrl" op="createSortOrder" escape=false}
			{/if}
			{form_language_chooser form="sortOrder" url=$sortOrderUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
{/if}
<tr valign="top">
	<td width="20%" class="label">{fieldLabel name="name" required="true" key="admin.sortOrders.name"}</td>
	<td width="80%" colspan="2" class="value"><input type="text" name="name[{$formLocale|escape}]" value="{$name[$formLocale]|escape}" size="40" id="name" maxlength="80" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="type" required="true" key="admin.sortOrders.type"}</td>
	<td colspan="2" class="value">
		<select name="type" class="selectMenu" id="type">
			{html_options_translate options=$typeOptions selected=$type}
		</select>
	</td>
</tr>
{foreach from=$schemaPlugins item="schemaPlugin" name="schemaPlugins"}
{assign var="schema" value=$schemaPlugin->getSchema()}
{assign var="schemaId" value=$schema->getSchemaId()}
<tr valign="top">
	{if $smarty.foreach.schemaPlugins.first}
		{* The first row needs a label *}
		<td rowspan="{$schemaPlugins|@count}" class="label">
			{fieldLabel name="fieldNames" suppressId="true" key="admin.sortOrders.fields" required="true"}
		</td>
	{/if}
	<td class="value">
		<label for="fields-{$schemaId|escape}">{$schemaPlugin->getSchemaDisplayName()|escape}</label>
	</td>
	<td class="value">
		<select name="fieldNames[{$schemaId|escape}]" id="fields-{$schemaId|escape}">
			<option value=""></option>
			{foreach from=$schemaPlugin->getFieldList() item="fieldSymbolic"}
				{assign var="schema" value=$schemaPlugin->getSchema()}
				{assign var="isSelected" value=0}
				{foreach from=$fields item="field"}
					{if $field->getSchemaId() == $schema->getSchemaId() && $field->getName() == $fieldSymbolic}
						{assign var="isSelected" value=1}
					{/if}
				{/foreach}
				<option {if $isSelected}selected="selected" {/if}value="{$fieldSymbolic|escape}">{$schemaPlugin->getFieldName($fieldSymbolic)|escape}</option>
			{/foreach}
		</select>
	</td>
</tr>
{/foreach}{* $schemaPlugins *}
</table>

<p><input type="submit" value="{translate key="common.save"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="sortOrders" escape=false}'" /></p>

</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{include file="common/footer.tpl"}
