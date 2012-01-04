{**
 * plugins/generic/zendSearch/searchFormElementForm.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Sort order management form.
 *
 * $Id$
 *}
{strip}
{assign var="pageCrumbTitle" value="$searchFormElementTitle"}
{assign var="helpTopicId" value="plugins.generic.zendSearch.searchForm"}
{if $searchFormElementId}
	{assign var="pageTitle" value="plugins.generic.zendSearch.formElement.edit"}
{else}
	{assign var="pageTitle" value="plugins.generic.zendSearch.formElement.create"}
{/if}
{assign var="pageId" value="manager.searchFormElements.searchFormElementForm"}
{include file="common/header.tpl"}
{/strip}

{if $searchFormElementId}{url|assign:"searchFormElementUrl" op="editSearchFormElement" path=$searchFormElementId}
{else}{url|assign:"searchFormElementUrl" op="createSearchFormElement" escape=false}
{/if}

<script type="text/javascript">
<!--
{literal}
function changeElementType() {
	changeFormAction('searchFormElement', '{/literal}{$searchFormElementUrl|escape:"javascript"}{literal}');
}
{/literal}
// -->
</script>

<br/>

<form id="searchFormElement" method="post" action="{url op="updateSearchFormElement"}">
{if $searchFormElementId}
<input type="hidden" name="searchFormElementId" value="{$searchFormElementId|escape}" />
{/if}

{include file="common/formErrors.tpl"}

<table class="data" width="100%">
{if count($formLocales) > 1}
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
		<td colspan="2" width="80%" class="value">
			{form_language_chooser form="searchFormElement" url=$searchFormElementUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
{else}
	<input type="hidden" name="formLocale" value="{$formLocale|escape}" />
{/if}
<tr valign="top">
	<td width="20%" class="label">{fieldLabel name="name" required="true" key="plugins.generic.zendSearch.formElement.title"}</td>
	<td width="80%" colspan="2" class="value"><input type="text" name="title[{$formLocale|escape}]" value="{$title[$formLocale]|escape}" size="40" id="title" maxlength="80" class="textField" /></td>
</tr>
<tr valign="top">
	<td width="20%" class="label">{fieldLabel name="symbolic" required="true" key="plugins.generic.zendSearch.formElement.symbolic"}</td>
	<td width="80%" colspan="2" class="value"><input type="text" name="symbolic" value="{$symbolic|escape}" size="20" id="symbolic" maxlength="30" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="type" required="true" key="plugins.generic.zendSearch.formElement.type"}</td>
	<td colspan="2" class="value">
		<select name="type" class="selectMenu" id="type" onchange="changeElementType()">
			{html_options_translate options=$typeOptions selected=$type}
		</select>
	</td>
</tr>
{if $type == $smarty.const.SEARCH_FORM_ELEMENT_TYPE_DATE}
	{* The DATE form element type may need its range recalculated. *}
	<tr valign="top">
		<td class="label">{translate key="plugins.generic.zendSearch.formElement.type.date.rangeStart"}</td>
		<td colspan="2" class="value">{$rangeStart|escape|default:"&mdash;"}</td>
	</tr>
	<tr valign="top">
		<td class="label">{translate key="plugins.generic.zendSearch.formElement.type.date.rangeEnd"}</td>
		<td colspan="2" class="value">{$rangeEnd|escape|default:"&mdash;"}</td>
	</tr>
	<tr valign="top">
		<td class="label">&nbsp;</td>
		<td colspan="2" class="value">
			<input type="checkbox" name="recalculateRange" id="recalculateRange" {if $recalculateRange}checked="checked" {/if} />
			{fieldLabel name="recalculateRange" key="plugins.generic.zendSearch.formElement.type.date.recalculateRange"}
		</td>
	</tr>
{elseif $type == $smarty.const.SEARCH_FORM_ELEMENT_TYPE_SELECT}
	{* The SELECT form element type may need its options recalculated. *}
	<tr valign="top">
		<td class="label">&nbsp;</td>
		<td colspan="2" class="value">
			<input type="checkbox" name="recalculateOptions" id="recalculateOptions" {if $recalculateOptions}checked="checked" {/if} />
			{fieldLabel name="recalculateOptions" key="plugins.generic.zendSearch.formElement.type.select.recalculateOptions"}
		</td>
	</tr>
{/if}

<tr valign="top">
	<td class="label">
		{fieldLabel suppressId="true" key="plugins.generic.zendSearch.formElement.includeFields" name="includeFields"}
	</td>
	<td class="value">
		{foreach from=$schemaPlugins item="schemaPlugin" name="schemaPlugins"}
		{assign var="schema" value=$schemaPlugin->getSchema()}
		{assign var="schemaId" value=$schema->getSchemaId()}
			<strong>{$schemaPlugin->getSchemaDisplayName()|escape}</strong><br />
			<select multiple="multiple" size="5" name="fieldNames[{$schemaId|escape}][]" id="fields-{$schemaId|escape}">
				{foreach from=$schemaPlugin->getFieldList() item="fieldSymbolic"}
					{assign var="schema" value=$schemaPlugin->getSchema()}
					{assign var="isSelected" value=0}
					{foreach from=$fields item="field"}
						{if $field->getSchemaId() == $schema->getSchemaId() && $field->getName() == $fieldSymbolic}
							{assign var="isSelected" value=1}
						{/if}
					{/foreach}
					{if $fieldSymbolic == $fieldNames[$schemaId]}
						{assign var="isSelected" value=1}
					{/if}
					<option {if $isSelected}selected="selected" {/if}value="{$fieldSymbolic|escape}">{$schemaPlugin->getFieldName($fieldSymbolic)|escape|truncate:60:"..."}</option>
				{/foreach}
			</select>
			<br />&nbsp;<br />
		{/foreach}
	</td>
</tr>
</table>

<p><input type="submit" value="{translate key="common.save"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="index" escape=false}'" /></p>

</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{include file="common/footer.tpl"}
