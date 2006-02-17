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

<form method="post" name="search" action="{url op="advancedResults"}">

<table class="data" width="100%">
<tr valign="top">
	<td width="25%" class="label"><label for="advancedQuery">{translate key="search.searchAllCategories"}</label></td>
	<td width="75%" class="value"><input type="text" id="advancedQuery" name="query" size="40" maxlength="255" value="{$query|escape}" class="textField" /></td>
</tr>
</table>

<p><input type="submit" value="{translate key="common.search"}" class="button defaultButton" /></p>

</form>

{translate key="search.syntaxInstructions"}

{include file="common/footer.tpl"}
