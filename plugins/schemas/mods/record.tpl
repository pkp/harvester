{**
 * record.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * View a MODS record.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="record.viewRecord"}
{include file="common/header.tpl"}
{/strip}

{assign var=contents value=$record->getParsedContents()}
<h4>{$archive->getTitle()|escape}</h4>

<a href="{url page="browse" op="archiveInfo" path=$archive->getArchiveId()}" class="action">{translate key="browse.archiveInfo"}</a><br/>&nbsp;

<h3>{translate key="plugins.schemas.mods.metadata"}</h3>
<table width="100%" class="listing">
	<tr>
		<td colspan="2" class="headseparator">&nbsp;</td>
	</tr>
	<tr valign="top" class="heading">
		<td width="25%">{translate key="record.field"}</td>
		<td width="75%">{translate key="record.value"}</td>
	</tr>
	<tr>
		<td colspan="2" class="headseparator">&nbsp;</td>
	</tr>
	{foreach from="title"|to_array:"subTitle":"partNumber":"partName":"nonSort" item=nodeName}	
		{if $contents.$nodeName}
			<tr valign="top">
				<td class="label">{translate key="plugins.schemas.mods.fields.$nodeName.name}</td>
				<td class="value">{$contents.$nodeName|escape|nl2br}</td>
			</tr>
		{/if}
	{/foreach}
	<tr>
		<td colspan="2" class="headseparator">&nbsp;</td>
	</tr>
	<tr valign="top">
		<td class="label">{translate key="plugins.schemas.mods.names"}</td>
		<td class="value">
			{foreach from=$contents.names item=name name=names}
				{$name.namePart}
				{foreach from=$name.roles item=role name=roles}
					{if $smarty.foreach.roles.first}({/if}{$role.term}{if $smarty.foreach.roles.last}){else}, {/if}
				{/foreach}
				<br/>
			{/foreach}
		</td>
	</tr>
	{foreach from="typeOfResourceCollection"|to_array:"typeOfResourceManuscript" item=nodeName}	
		{if $contents.$nodeName}
			<tr valign="top">
				<td class="label">{translate key="plugins.schemas.mods.fields.$nodeName.name}</td>
				<td class="value">{$contents.$nodeName|escape|nl2br}</td>
			</tr>
		{/if}
	{/foreach}
	{foreach from="publisher"|to_array:"edition":"issuance":"frequency" item=nodeName}	
		{if $contents.originInfo.$nodeName}
			<tr valign="top">
				<td class="label">{translate key="plugins.schemas.mods.fields.$nodeName.name}</td>
				<td class="value">{$contents.originInfo.$nodeName|escape|nl2br}</td>
			</tr>
		{/if}
	{/foreach}
	{foreach from="dateIssued"|to_array:"dateCreated":"dateCaptured":"dateValid":"dateModified":"copyrightDate":"dateOther" item=nodeName}	
		{if $contents.originInfo.$nodeName}
			<tr valign="top">
				<td class="label">{translate key="plugins.schemas.mods.fields.$nodeName.name}</td>
				<td class="value">{$contents.originInfo.$nodeName.value|escape|nl2br} ({$contents.originInfo.$nodeName.encoding|escape|nl2br})</td>
			</tr>
		{/if}
	{/foreach}
	{foreach from=$contents.originInfo.places item=place}
		<tr valign="top">
			<td class="label">{translate key="plugins.schemas.mods.fields.place.name"}</td>
			<td class="value">{$place.term|escape|nl2br}</td>
		</tr>
	{/foreach}
	{foreach from=$contents.languages item=language}
		<tr valign="top">
			<td class="label">{translate key="plugins.schemas.mods.fields.language.name"}</td>
			<td class="value">{$language.term|escape|nl2br}</td>
		</tr>
	{/foreach}
	{foreach from="form"|to_array:"reformattingQuality":"internetMediaType":"extent":"digitalOrigin":"note":"abstract":"genre":"tableOfContents":"targetAudience":"classification":"accessCondition":"extension":"subjectTopic":"subjectGeographic":"subjectTemporal":"subjectGeographicCode":"subjectGenre":"subjectOccupation" item=nodeName}	
		{if $contents.$nodeName}
			<tr valign="top">
				<td class="label">{translate key="plugins.schemas.mods.fields.$nodeName.name}</td>
				<td class="value">{$contents.$nodeName|escape|nl2br}</td>
			</tr>
		{/if}
	{/foreach}
	{if $contents.identifier}
		<tr valign="top">
			<td class="label">{translate key="plugins.schemas.mods.fields.identifier.name"}</td>
			<td class="value"><a href="{$contents.identifier}">{$contents.identifier|escape}</a></td>
		</tr>
	{/if}
	{foreach from=$contents.locations item=location}
		{foreach from="physicalLocation"|to_array:"shelfLocator":"holdingExternal":"url" item=nodeName}
			{if $location.$nodeName}
				<tr valign="top">
					<td class="label">{translate key="plugins.schemas.mods.fields.$nodeName.name"}</td>
					<td class="value">{$location.$nodeName|escape|nl2br}</td>
				</tr>
			{/if}
		{/foreach}
	{/foreach}
	{foreach from=$contents.relatedItems item=relatedItem}
		<tr valign="top">
			<td class="label">{translate key="plugins.schemas.mods.fields.relatedItem.name"}</td>
			<td class="value">
				{$relatedItem.title|escape|nl2br}
			</td>
		</tr>
	{/foreach}
</table>



{if $defineTermsContextId}
<script type="text/javascript">
{literal}
<!--
	// Open "Define Terms" context when double-clicking any text
	function openSearchTermWindow(url) {
		var term;
		if (window.getSelection) {
			term = window.getSelection();
		} else if (document.getSelection) {
			term = document.getSelection();
		} else if(document.selection && document.selection.createRange && document.selection.type.toLowerCase() == 'text') {
			var range = document.selection.createRange();
			term = range.text;
		}
		if (url.indexOf('?') > -1) openRTWindowWithToolbar(url + '&defineTerm=' + term);
		else openRTWindowWithToolbar(url + '?defineTerm=' + term);
	}

	if(document.captureEvents) {
		document.captureEvents(Event.DBLCLICK);
	}
	document.ondblclick = new Function("openSearchTermWindow('{/literal}{url page="rt" op="context" path=$record->getRecordId()|to_array:$defineTermsContextId}{literal}')");
// -->
{/literal}
</script>
{/if}

{include file="common/footer.tpl"}
