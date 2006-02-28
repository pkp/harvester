{**
 * searchResults.tpl
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display search results.
 *
 * $Id$
 *}

{assign var=pageTitle value="search.searchResults"}

{include file="common/header.tpl"}

<script type="text/javascript">
{literal}
<!--
function ensureKeyword() {
	if (document.search.query.value == '') {
		alert({/literal}'{translate|escape:"javascript" key="search.noKeywordError"}'{literal});
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
		<input type="hidden" name="query" value="{$query|escape}"/>
		{if is_array($archiveIds)}
			{foreach from=$archiveIds item=archiveId}
				<input type="hidden" name="archiveIds[]" value="{$archiveId|escape}" />
			{/foreach}
		{/if}
	</form>
	<a href="javascript:document.revise.submit()" class="action">{translate key="search.reviseSearch"}</a><br />&nbsp;
{/if}

<ul class="plain">
{iterate from=results item=record}
	{$record->displaySummary()}
{/iterate}
{if $results->wasEmpty()}
	<li>&#187; {translate key="search.noResults"}</li>
	</ul>
{else}
	</ul>
	{page_info iterator=$results}&nbsp;&nbsp;&nbsp;&nbsp;{page_links iterator=$results name="search" query=$query archiveIds=$archiveIds isAdvanced=$isAdvanced}
{/if}

<p>{translate key="search.syntaxInstructions"}</p>

{include file="common/footer.tpl"}
