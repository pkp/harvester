{**
 * plugins/generic/zendSearch/searchForm.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Search form construction for Zend Framework's Lucene implementation.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="plugins.generic.zendSearch.searchForm"}
{assign var="helpTopicId" value="plugins.generic.zendSearch.searchForm"}
{include file="common/header.tpl"}
{/strip}

<br />

<div id="searchFormElements">
<table width="100%" class="listing">
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
	<tr valign="top" class="heading">
		<td>{translate key="plugins.generic.zendSearch.formElement.title"}</td>
		<td width="15%">{translate key="plugins.generic.zendSearch.formElement.type"}</td>
		<td width="7%" align="center">{translate key="plugins.generic.zendSearch.formElement.status"}</td>
		<td width="15%" align="right">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
	{iterate from=searchFormElements item=formElement}
	<tr valign="top">
		<td>{$formElement->getSearchFormElementTitle()|escape}</td>
		<td>{$formElement->getTypeName()|translate}</td>
		<td align="center">
			{if $formElement->getIsClean()}
				{icon name="checked"}
			{else}
				{icon name="unchecked"}
			{/if}
		</td>
		<td align="right"><a href="{url op="editSearchFormElement" path=$formElement->getSearchFormElementId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a class="action" href="{url op="deleteSearchFormElement" path=$formElement->getSearchFormElementId() formElementPage=$formElementPage}" onclick="return confirm('{translate|escape:"jsparam" key="plugins.generic.zendSearch.formElement.confirmDelete"}')">{translate key="common.delete"}</a></td>
	</tr>
	<tr>
		<td colspan="4" class="{if $searchFormElements->eof()}end{/if}separator">&nbsp;</td>
	</tr>
	{/iterate}
	{if $searchFormElements->wasEmpty()}
	<tr>
		<td colspan="4" class="nodata">{translate key="plugins.generic.zendSearch.formElement.noneCreated"}</td>
	</tr>
	<tr>
		<td colspan="4" class="endseparator">&nbsp;</td>
	<tr>
	{else}
		<tr>
			<td align="left">{page_info iterator=$searchFormElements}</td>
			<td colspan="3" align="right">{page_links anchor="searchFormElements" name="searchFormElements" iterator=$searchFormElements}</td>
		</tr>
	{/if}
</table>

<p><a href="{url op="createSearchFormElement"}" class="action">{translate key="plugins.generic.zendSearch.formElement.create"}</a></p>
</div>
{include file="common/footer.tpl"}
