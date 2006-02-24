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

{if $basicQuery}
	<form method="post" name="search" action="{url op="results"}">
		<input type="text" size="40" maxlength="255" class="textField" name="query" value="{$query|escape}"/>&nbsp;&nbsp;
		<input type="submit" class="button defaultButton" onclick="ensureKeyword();" value="{translate key="common.search"}"/>
	</form>
	<br />
{else}
{/if}

<table width="100%" class="listing">
<tr><td colspan="2" class="headseparator">&nbsp;</td></tr>
<tr class="heading" valign="bottom">
	<td width="40%">FIXME</td>
	<td width="60%">FIXME</td>
</tr>
<tr><td colspan="2" class="headseparator">&nbsp;</td></tr>

{iterate from=results item=record}
	{$record->displaySummary()}
{/iterate}
{if $results->wasEmpty()}
<tr>
<td colspan="2" class="nodata">{translate key="search.noResults"}</td>
</tr>
<tr><td colspan="2" class="endseparator">&nbsp;</td></tr>
{else}
	<tr>
		<td align="left">{page_info iterator=$results}</td>
		<td align="right">{page_links iterator=$results name="search" query=$basicQuery searchField=$searchField}</td>
	</tr>
{/if}
</table>

<p>{translate key="search.syntaxInstructions"}</p>

{include file="common/footer.tpl"}
