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

{if $showSpecificFields}
	{assign var=hideSpecificFields value=0}
{else}
	{assign var=hideSpecificFields value=1}
{/if}

<form action="#" name="blinkInfo">
	<input type="hidden" names="blinksRemaining" value="0"/>
	<input type="hidden" names="isBlinking" value="0"/>
</form>

<script type="text/javascript">
{literal}
<!--
function doToggleSpecificFields() {
	document.search.action = "{/literal}{url op="search" escape="false" showSpecificFields=$hideSpecificFields}{literal}";
	document.search.submit();
	return true;
}

function handleBlink() {
	if (document.blinkInfo.blinksRemaining != 0) {
		if (--document.blinkInfo.blinksRemaining % 2) {
			document.search.toggleSpecificFields.setAttribute("class", "flashingButton");
		} else {
			document.search.toggleSpecificFields.setAttribute("class", "button");
		}
		window.setTimeout("handleBlink()", 250);
	} else {
		document.blinkInfo.isBlinking = 0;
	}
}

function handleArchiveSelect() {
	{/literal}{if $showSpecificFields}{literal}
	// Specific fields are currently displayed; the field set should be
	// updated.
	if (document.search.archiveIds.options[0].selected || document.search.archiveIds.selectedIndex == -1) {
		document.search.action = "{/literal}{url op="search" escape="false"}{literal}";
	} else {
		document.search.action = "{/literal}{url op="search" escape="false" showSpecificFields=$showSpecificFields}{literal}";
	}
	document.search.submit();
	return true;

	{/literal}{else}{literal}
	// The specific fields are not currently displayed. Flash the button.
	if (!document.search.archiveIds.options[0].selected) {
		if (!document.blinkInfo.isBlinking) {
			document.blinkInfo.blinksRemaining=5;
			document.blinkInfo.isBlinking=1;
			window.setTimeout("handleBlink()", 250);
		}
	}
	{/literal}{/if}{literal}
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
		<input type="button" name="toggleSpecificFields" class="button" onclick="doToggleSpecificFields()" value="{if $showSpecificFields}{translate key="search.hideSpecificFields"}{else}{translate key="search.showSpecificFields"}{/if}"/>
	</td>
</tr>
</table>

<p><input type="submit" value="{translate key="common.search"}" class="button defaultButton" /></p>

</form>

{translate key="search.syntaxInstructions"}

{include file="common/footer.tpl"}
