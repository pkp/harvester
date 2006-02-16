{**
 * searchableFieldForm.tpl
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Basic searchableField settings under site administration.
 *
 * $Id$
 *}

{if $searchableFieldId}
	{assign var="pageTitle" value="admin.indexing.editSearchableField"}
{else}
	{assign var="pageTitle" value="admin.indexing.addSearchableField"}
{/if}

{include file="common/header.tpl"}

<br />

<script type="text/javascript">
<!--

{literal}
function selectIndexer() {
	document.searchableFieldForm.action="{/literal}{if $searchableFieldId}{url op="editSearchableField" anchor="searchableFieldForm" path=$searchableFieldId escape=false}{else}{url op="editSearchableField" anchor="searchableFieldForm" escape=false}{/if}{literal}";
	document.searchableFieldForm.submit();
}

{/literal}
// -->
</script>

<a name="searchableFieldForm"/>
<form name="searchableFieldForm" method="post" action="{url op="updateSearchableField"}">
{if $searchableFieldId}
<input type="hidden" name="searchableFieldId" value="{$searchableFieldId}" />
{/if}

{include file="common/formErrors.tpl"}

<table class="data" width="100%">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="name" key="admin.indexing.searchableField.name" required="true"}</td>
		<td width="80%" class="value"><input type="text" id="name" name="name" value="{$name|escape}" size="40" maxlength="120" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="description" key="admin.indexing.searchableField.description" required="true"}</td>
		<td class="value"><textarea name="description" id="description" cols="40" rows="10" class="textArea">{$description|escape}</textarea></td>
	</tr>

	{iterate from=indexers item=indexer}
		<tr valign="top">
			<td>{$indexer->getPluginDisplayName()}</td>
			<td>{$indexer->displayAdminForm()}</td>
		</tr>
	{/iterate}

	{call_hook name="Template::Admin::SearchableFields::displayHarvesterForm" plugin=$harvesterPlugin}
</table>

<p><input type="submit" value="{translate key="common.save"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="indexing"}'" /></p>

</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{include file="common/footer.tpl"}
