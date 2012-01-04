{**
 * index.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Search form.
 *
 * $Id$
 *}
{assign var="pageTitle" value="navigation.search"}
{assign var="helpTopicId" value="plugins.generic.mysqlIndex.search"}
{include file="common/header.tpl"}

<script type="text/javascript">
{literal}
<!--
function handleArchiveSelect() {
	// Specific fields are currently displayed; the field set should be
	// updated.
	document.getElementById('search').action = "{/literal}{url page="misearch" escape="false"}{literal}";
	document.getElementById('search').submit();
	return true;
}

// -->
{/literal}
</script>

<form method="post" name="search" action="{url op="results"}">
<input type="hidden" name="isAdvanced" value="1"/>

<table class="data" width="100%">
<tr valign="top">
	<td width="25%" class="label"><label for="query">{translate key="search.allFields"}</label></td>
	<td width="75%" colspan="2" class="value"><input type="text" id="query" name="query" size="40" maxlength="255" value="{$query|escape}" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label"><label for="archiveIds">{translate key="archive.archives"}</label></td>
	<td class="value" colspan="2">
		<select onchange="handleArchiveSelect()" multiple id="archiveIds" name="archiveIds[]" size="5" class="selectMenu">
			{if (is_array($archiveIds) && in_array('all', $archiveIds)) || (!is_array($archiveIds) && ($archiveIds == 'all' || $archiveIds == ''))}
				{assign var=searchAll value=1}
			{else}
				{assign var=searchAll value=0}
			{/if}
			<option {if $searchAll}selected {/if}value="all">{translate key="search.allArchives"}</option>
			{iterate from=archives item=archive}
				<option {if !$searchAll && ((is_array($archiveIds) && in_array($archive->getArchiveId(), $archiveIds)) || (!is_array($archiveIds) && $archiveIds == $archive->getArchiveId()))}selected {/if}value="{$archive->getArchiveId()}">{$archive->getTitle()|escape|truncate:80:"..."}</option>
			{/iterate}
		</select><br />
	</td>
</tr>

	{foreach from=$crosswalks item=crosswalk}
		{assign var=crosswalkType value=$crosswalk->getType()}
		{assign var=crosswalkId value=$crosswalk->getCrosswalkId()}
		{if $crosswalkType == FIELD_TYPE_DATE}
			{assign var=crosswalkValueFromVar value=crosswalk-$crosswalkId-from}
			{assign var=crosswalkValueToVar value=crosswalk-$crosswalkId-to}
			<tr valign="top">
				<td rowspan="2" class="label">{$crosswalk->getName()|escape}</td>
				<td class="value">{translate key="common.from"}</td>
				<td class="value">{html_select_date prefix="crosswalk-$crosswalkId-from" time=$crosswalkValueFromVar|get_value|default:"--" all_extra="class=\"selectMenu\"" year_empty="" month_empty="" day_empty="" start_year="1900" end_year="+10"}</td>
			</tr>
			<tr valign="top">
				<td class="value">{translate key="common.until"}</td>
				<td class="value">{html_select_date prefix="crosswalk-$crosswalkId-to" time=$crosswalkValueToVar|get_value|default:"--" all_extra="class=\"selectMenu\"" year_empty="" month_empty="" day_empty="" start_year="1900" end_year="+10"}</td>
			</tr>
		{elseif $crosswalkType == FIELD_TYPE_SELECT}
			{assign var=crosswalkValueVar value=crosswalk-$crosswalkId}
			{assign var=crosswalkValues value=$crosswalkValueVar|get_value}
			{assign var=crosswalkOptionsVar value=crosswalk-options-$crosswalkId}
			{assign var=crosswalkOptions value=$crosswalkOptionsVar|get_value}
			<tr valign="top">
				<td class="label"><label for="crosswalk-{$crosswalkId}">{$crosswalk->getName()|escape}</label></td>
				<td colspan="2" class="value">
					<select id="crosswalk-{$crosswalkId}" name="crosswalk-{$crosswalkId}[]" multiple class="selectMenu" size="5">
						<option value="">{translate key="search.select.none"}</option>
						{foreach from=$crosswalkOptions item=option}{if !empty($option)}
							<option value="{$option|escape}" {if (is_array($crosswalkValues) && in_array($option, $crosswalkValues)) || ($crosswalkValues == $option)}selected="selected" {/if}>{$option|escape|truncate:80:"..."}</option>
						{/if}{/foreach}
					</select>
				</td>

		{else}{* FIELD_TYPE_TEXT *}
			{assign var=crosswalkValueVar value=crosswalk-$crosswalkId}
			<tr valign="top">
				<td class="label"><label for="crosswalk-{$crosswalkId}">{$crosswalk->getName()|escape}</label></td>
				<td colspan="2" class="value"><input type="text" id="crosswalk-{$crosswalkId}" name="crosswalk-{$crosswalkId}" size="40" maxlength="255" value="{$crosswalkValueVar|get_value|escape}" class="textField" /></td>
			</tr>
		{/if}
	{/foreach}
	{foreach from=$fields item=field}
		{assign var=fieldType value=$field->getType()}
		{assign var=fieldId value=$field->getFieldId()}
		{if $fieldType == FIELD_TYPE_DATE}
			{assign var=fieldValueFromVar value=field-$fieldId-from}
			{assign var=fieldValueToVar value=field-$fieldId-to}
			<tr valign="top">
				<td rowspan="2" class="label">{$field->getDisplayName()|escape}</td>
				<td class="value">{translate key="common.from"}</td>
				<td class="value">{html_select_date prefix="field-$fieldId-from" time=$fieldValueFromVar|get_value|default:"--" all_extra="class=\"selectMenu\"" year_empty="" month_empty="" day_empty="" start_year="1900" end_year="+10"}</td>
			</tr>
			<tr valign="top">
				<td class="value">{translate key="common.until"}</td>
				<td class="value"{html_select_date prefix="field-$fieldId-to" time=$fieldValueToVar|get_value|default:"--" all_extra="class=\"selectMenu\"" year_empty="" month_empty="" day_empty="" start_year="1900" end_year="+10"}</td>
			</tr>
		{elseif $fieldType == FIELD_TYPE_SELECT}
			{assign var=fieldValueVar value=field-$fieldId}
			{assign var=fieldValues value=$fieldValueVar|get_value}
			{assign var=fieldOptionsVar value=field-options-$fieldId}
			{assign var=fieldOptions value=$fieldOptionsVar|get_value}
			<tr valign="top">
				<td class="label"><label for="field-{$fieldId}">{$field->getDisplayName()|escape}</label></td>
				<td colspan="2" class="value">
					<select id="field-{$fieldId}" name="field-{$fieldId}[]" multiple class="selectMenu" size="5">
						<option value="">{translate key="search.select.none"}</option>
						{foreach from=$fieldOptions item=option}{if !empty($option)}
							<option value="{$option|escape}" {if (is_array($fieldValues) && in_array($option, $fieldValues)) || ($fieldValues == $option)}selected="selected" {/if}>{$option|escape|truncate:80:"..."}</option>
						{/if}{/foreach}
					</select>
				</td>

		{else}
			<tr valign="top">
				<td class="label"><label for="field-{$fieldId}">{$field->getDisplayName()|escape}</label></td>
				<td class="value" colspan="2">
					{assign var=fieldValueVar value=field-$fieldId}
					<input type="text" id="field-{$fieldId}" name="field-{$fieldId}" size="40" maxlength="255" value="{$fieldValueVar|get_value|escape}" class="textField" />
				</td>
			</tr>
		{/if}
	{/foreach}
</table>

<p><input type="submit" value="{translate key="common.search"}" class="button defaultButton" /></p>

</form>

{translate key="search.syntaxInstructions"}

{include file="common/footer.tpl"}
