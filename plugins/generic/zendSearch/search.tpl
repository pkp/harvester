{**
 * plugins/generic/zendSearch/search.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Search page for Zend Framework's Lucene implementation.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="navigation.search"}
{assign var="helpTopicId" value="plugins.generic.zendSearch.search"}
{include file="common/header.tpl"}
{/strip}

<form method="post" action="{url op="searchResults"}">
	
<table class="data" width="100%">
	<tr valign="top">
		<td width="25%" class="label">
			<label for="q">{translate key="search.allFields"}</label>
		</td>
		<td width="75%" class="value">
			<input type="text" id="q" name="q" size="40" maxlength="255" value="{$q|escape}" class="textField" />
		</td>
	</tr>
{iterate from=searchFormElements item=searchFormElement}
	{assign var="searchFormElementId" value=$searchFormElement->getSearchFormElementId()}
	{assign var="searchFormElementType" value=$searchFormElement->getType()}
	{assign var="searchFormElementSymbolic" value=$searchFormElement->getSymbolic()}
	<tr valign="top">
		<td class="label">
			<label for="searchFormElement-{$searchFormElement->getSearchFormElementId()|escape}">{$searchFormElement->getSearchFormElementTitle()|escape}</label>
		</td>
		<td class="value">
			{if $searchFormElementType == $smarty.const.SEARCH_FORM_ELEMENT_TYPE_STRING}
				<input id="searchFormElement-{$searchFormElementId|escape}" name="{$searchFormElementSymbolic|escape}" type="text" class="textField" />
			{elseif $searchFormElementType == $smarty.const.SEARCH_FORM_ELEMENT_TYPE_SELECT}
				<select id="searchFormElement-{$searchFormElementId|escape}" name="{$searchFormElementSymbolic|escape}" class="selectMenu">
					{assign var='searchFormElementOptions' value=$searchFormElement->getOptions()}
					{iterate from=searchFormElementOptions item=option}
						<option value="{$option|escape}">{$option|escape}</option>
					{/iterate}
				</select>
			{else}{* $searchFormElementype == $smarty.const.SEARCH_FORM_ELEMENT_TYPE_DATE *}
				{assign var="startDate" value=$searchFormElement->getRangeStart()|strtotime}
				{assign var="startYear" value=$startDate|date_format:"%Y"}
				{assign var="endDate" value=$searchFormElement->getRangeEnd()|strtotime}
				{assign var="endYear" value=$endDate|date_format:"%Y"}
				{html_select_date time=$startDate prefix="$searchFormElementSymbolic-from" all_extra="class=\"selectMenu\"" year_empty="" month_empty="" day_empty="" start_year="$startYear" end_year="$endYear"}<br/>
				{html_select_date time=$endDate prefix="$searchFormElementSymbolic-to" all_extra="class=\"selectMenu\"" year_empty="" month_empty="" day_empty="" start_year="$startYear" end_year="$endYear"}
				<input type="hidden" name="{$searchFormElementSymbolic|escape}-toHour" value="23" />
				<input type="hidden" name="{$searchFormElementSymbolic|escape}-toMinute" value="59" />
				<input type="hidden" name="{$searchFormElementSymbolic|escape}-toSecond" value="59" />
			{/if}
		</td>
	</tr>
{/iterate}
</table>

<p><input type="submit" value="{translate key="common.search"}" class="button defaultButton" /></p>

</form>

{include file="common/footer.tpl"}
