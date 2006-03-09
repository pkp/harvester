{**
 * index.tpl
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Search form.
 *
 * $Id$
 *}

{assign var="pageTitle" value="navigation.search"}
{include file="common/header.tpl"}

<form action="#" name="blinkInfo">
	<input type="hidden" names="blinksRemaining" value="0"/>
	<input type="hidden" names="isBlinking" value="0"/>
</form>

<script type="text/javascript">
{literal}
<!--
function handleArchiveSelect() {
	// Specific fields are currently displayed; the field set should be
	// updated.
	document.search.action = "{/literal}{url op="search" escape="false"}{literal}";
	document.search.submit();
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
	<td width="75%" class="value"><input type="text" id="query" name="query" size="40" maxlength="255" value="{$query|escape}" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label"><label for="archiveIds">{translate key="archive.archives"}</label></td>
	<td class="value">
		<select onchange="handleArchiveSelect()" multiple id="archiveIds" name="archiveIds[]" size="5" class="selectMenu">
			{if (is_array($archiveIds) && in_array('all', $archiveIds)) || (!is_array($archiveIds) && ($archiveIds == 'all' || $archiveIds == ''))}
				{assign var=searchAll value=1}
			{else}
				{assign var=searchAll value=0}
			{/if}
			<option {if $searchAll}selected {/if}value="all">{translate key="search.allArchives"}</option>
			{iterate from=archives item=archive}
				<option {if !$searchAll && ((is_array($archiveIds) && in_array($archive->getArchiveId(), $archiveIds)) || (!is_array($archiveIds) && $archiveIds == $archive->getArchiveId()))}selected {/if}value="{$archive->getArchiveId()}">{$archive->getTitle()|escape}</option>
			{/iterate}
		</select><br />
	</td>
	{foreach from=$crosswalks item=crosswalk}
		{assign var=crosswalkId value=$crosswalk->getCrosswalkId()}
		{assign var=crosswalkValueVar value=crosswalk-$crosswalkId}
		<tr valign="top">
			<td class="label">{$crosswalk->getName()}</td>
			<td class="value"><input type="text" id="crosswalk-{$crosswalkId}" name="crosswalk-{$crosswalkId}" size="40" maxlength="255" value="{$crosswalkValueVar|get_value|escape}" class="textField" /></td>
		</tr>
	{/foreach}
	{foreach from=$fields item=field}
		{assign var=fieldId value=$field->getFieldId()}
		{assign var=fieldValueVar value=field-$fieldId}
		<tr valign="top">
			<td class="label">{$field->getDisplayName()}</td>
			<td class="value"><input type="text" id="field-{$fieldId}" name="field-{$fieldId}" size="40" maxlength="255" value="{$fieldValueVar|get_value|escape}" class="textField" /></td>
		</tr>
	{/foreach}
</tr>
</table>

<p><input type="submit" value="{translate key="common.search"}" class="button defaultButton" /></p>

</form>

{translate key="search.syntaxInstructions"}

{include file="common/footer.tpl"}
