{**
 * searchResults.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display search results.
 *
 * $Id$
 *}
{strip}
{* Make the list of selected archives available to the quick search sidebar *}
{if is_array($archiveIds) && !in_array('all', $archiveIds)}
	{assign var="theseArchiveIds" value=$archiveIds}
{/if}
{assign var=pageTitle value="search.searchResults"}
{assign var="helpTopicId" value="plugins.generic.mysqlIndex.search"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
{literal}
<!--
function ensureKeyword() {
	if (document.search.query.value == '') {
		alert({/literal}'{translate|escape:"jsparam" key="search.noKeywordError"}'{literal});
		return false;
	}
	document.search.submit();
	return true;
}
// -->
{/literal}
</script>

<br/>

{if $isAdvanced}
	<form method="post" name="revise" action="{url op="index"}">
		<input type="hidden" name="importance" value="{$importance|escape}"/>
		<input type="hidden" name="query" value="{$query|escape}"/>
		{if is_array($archiveIds)}
			{foreach from=$archiveIds item=archiveId}
				<input type="hidden" name="archiveIds[]" value="{$archiveId|escape}" />
			{/foreach}
		{/if}
		{foreach from=$crosswalks item=crosswalk}
			{assign var=crosswalkId value=$crosswalk->getCrosswalkId()}
			{if $crosswalk->getType() == FIELD_TYPE_DATE}
				{assign var=crosswalkValueVar value=crosswalk-$crosswalkId-from}
				<input type="hidden" name="{$crosswalkValueVar}" value="{$crosswalkValueVar|get_value|escape}" />
				{assign var=crosswalkValueVar value=crosswalk-$crosswalkId-to}
				<input type="hidden" name="{$crosswalkValueVar}" value="{$crosswalkValueVar|get_value|escape}" />
			{else}
				{assign var=crosswalkValueVar value=crosswalk-$crosswalkId}
				<input type="hidden" name="crosswalk-{$crosswalkId}" value="{$crosswalkValueVar|get_value|escape}" />
			{/if}
		{/foreach}
		{foreach from=$fields item=field}
			{assign var=fieldId value=$field->getFieldId()}
			{if $field->getType() == FIELD_TYPE_DATE}
				{assign var=fieldValueVar value=field-$fieldId-from}
				<input type="hidden" name="{$fieldValueVar}" value="{$fieldValueVar|get_value|escape}" />
				{assign var=fieldValueVar value=field-$fieldId-to}
				<input type="hidden" name="{$fieldValueVar}" value="{$fieldValueVar|get_value|escape}" />
			{else}
				{assign var=fieldValueVar value=field-$fieldId}
				<input type="hidden" name="{$fieldValueVar}" value="{$fieldValueVar|get_value|escape}" />
			{/if}
		{/foreach}
	</form>
	<a href="javascript:document.revise.submit()" class="action">{translate key="search.reviseSearch"}</a><br />&nbsp;
{/if}

<div id="records">
<ul class="plain">
{iterate from=results item=record}
	{$record->displaySummary()}
{/iterate}
{if $results->wasEmpty()}
	<li>&#187; {translate key="search.noResults"}</li>
	</ul>
{else}
	</ul>
	{page_info iterator=$results}&nbsp;&nbsp;&nbsp;&nbsp;{page_links anchor="records" iterator=$results name="search" query=$query archiveIds=$archiveIds isAdvanced=$isAdvanced params=$forwardParams}
{/if}
</div>

<p>{translate key="search.syntaxInstructions"}</p>

{include file="common/footer.tpl"}
