{**
 * sortOrders.tpl
 *
 * Copyright (c) 2005-2010 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of sort orders for management.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="admin.sortOrders"}
{assign var="helpTopicId" value="admin.sortOrders"}
{assign var="pageId" value="admin.sortOrders"}
{include file="common/header.tpl"}
{/strip}

<div id="sortOrders">
<table width="100%" class="listing">
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td width="60%">{translate key="admin.sortOrders.name"}</td>
		<td width="18%">{translate key="admin.sortOrders.type"}</td>
		<td width="7%" align="center">{translate key="admin.sortOrders.status"}</td>
		<td width="15%">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
{iterate from=sortOrders item=sortOrder}
	<tr valign="top">
		<td>{$sortOrder->getSortOrderName()|escape}</td>
		<td>{$sortOrder->getSortOrderTypeName()|escape}</td>
		<td align="center">
			{if $sortOrder->getIsClean()}
				{icon name="checked"}
			{else}
				{icon name="unchecked"}
			{/if}
		</td>
		<td><a href="{url op="editSortOrder" path=$sortOrder->getSortOrderId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url op="deleteSortOrder" path=$sortOrder->getSortOrderId()}" onclick="return confirm('{translate|escape:"jsparam" key="admin.sortOrders.confirmDelete"}')" class="action">{translate key="common.delete"}</a></td>
	</tr>
	<tr>
		<td colspan="4" class="{if $sortOrders->eof()}end{/if}separator">&nbsp;</td>
	</tr>
{/iterate}
{if $sortOrders->wasEmpty()}
	<tr>
		<td colspan="4" class="nodata">{translate key="admin.sortOrders.noneCreated"}</td>
	</tr>
	<tr>
		<td colspan="4" class="endseparator">&nbsp;</td>
	</tr>
{else}
	<tr>
		<td align="left">{page_info iterator=$sortOrders}</td>
		<td align="right" colspan="3">{page_links anchor="sortOrders" name="sortOrders" iterator=$sortOrders}</td>
	</tr>
{/if}
</table>

<a href="{url op="createSortOrder"}" class="action">{translate key="admin.sortOrders.create"}</a>
</div>

{include file="common/footer.tpl"}
