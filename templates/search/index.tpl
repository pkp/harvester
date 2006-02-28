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
		<select multiple id="archiveIds" name="archiveIds[]" size="5" class="selectMenu">
			{if (is_array($archiveIds) && in_array('all', $archiveIds)) || (!is_array($archiveIds) && ($archiveIds == 'all' || $archiveIds == ''))}
				{assign var=searchAll value=1}
			{else}
				{assign var=searchAll value=0}
			{/if}
			<option {if $searchAll}selected {/if}value="all">{translate key="search.allArchives"}</option>
			{iterate from=archives item=archive}
				<option {if !$searchAll && ((is_array($archiveIds) && in_array($archive->getArchiveId(), $archiveIds)) || (!is_array($archiveIds) && $archiveIds == $archive->getArchiveId()))}selected {/if}value="{$archive->getArchiveId()}">{$archive->getTitle()|escape}</option>
			{/iterate}
		</select>
	</td>
	{if $showSpecificFields}
		FIXME: SHOULD BE SHOWING SPECIFIC FIELDS FOR THE SELECTED
		ARCHIVES.
	{else}
		FIXME: SHOULD BE SHOWING CROSSWALK FIELDS HERE.
	{/if}
</tr>
</table>

<p><input type="submit" value="{translate key="common.search"}" class="button defaultButton" /></p>

</form>

{translate key="search.syntaxInstructions"}

{include file="common/footer.tpl"}
