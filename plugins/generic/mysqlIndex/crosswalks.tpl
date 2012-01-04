{**
 * plugins/generic/mysqlIndex/crosswalks.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of searchable fields in site administration.
 *
 * $Id$
 *}
{assign var="pageTitle" value="plugins.generic.mysqlIndex.crosswalks"}
{assign var="helpTopicId" value="plugins.generic.mysqlIndex.crosswalks"}
{include file="common/header.tpl"}

<p>{translate key="plugins.generic.mysqlIndex.crosswalks.description"}</p>

<div id="crosswalks">
<table width="100%" class="listing">
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
	<tr valign="top" class="heading">
		<td width="25%">{translate key="plugins.generic.mysqlIndex.crosswalk.name"}</td>
		<td width="60%">{translate key="plugins.generic.mysqlIndex.crosswalk.description"}</td>
		<td width="15%" align="right">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
	{iterate from=crosswalks item=crosswalk}
	<tr valign="top">
		<td>{$crosswalk->getCrosswalkName()|escape}</td>
		<td>{$crosswalk->getCrosswalkDescription()|escape}</td>
		<td align="right"><a href="{url op="editCrosswalk" crosswalkId=$crosswalk->getCrosswalkId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url op="deleteCrosswalk" path=$crosswalk->getCrosswalkId()}" onclick="return confirm('{translate|escape:"jsparam" key="plugins.generic.mysqlIndex.confirmDelete"}')" class="action">{translate key="common.delete"}</a></td>
	</tr>
	<tr>
		<td colspan="4" class="{if $smarty.foreach.crosswalks.last}end{/if}separator">&nbsp;</td>
	</tr>
	{/iterate}
	{if $crosswalks->wasEmpty()}
	<tr>
		<td colspan="4" class="nodata">{translate key="plugins.generic.mysqlIndex.crosswalks.noneCreated"}</td>
	</tr>
	<tr>
		<td colspan="4" class="endseparator">&nbsp;</td>
	<tr>
	{else}
		<tr>
			<td colspan="2" align="left">{page_info iterator=$crosswalks}</td>
			<td colspan="2" align="right">{page_links anchor="crosswalks" name="crosswalks" iterator=$crosswalks}</td>
		</tr>
	{/if}
</table>

<p><a href="{url op="createCrosswalk"}" class="action">{translate key="plugins.generic.mysqlIndex.addCrosswalk"}</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="#" class="action" onclick="confirmAction('{url op="resetCrosswalks"}', '{translate|escape:"jsparam" key="plugins.generic.mysqlIndex.confirmReset"}')">{translate key="plugins.generic.mysqlIndex.reset"}</a></p>
</div>
{include file="common/footer.tpl"}
