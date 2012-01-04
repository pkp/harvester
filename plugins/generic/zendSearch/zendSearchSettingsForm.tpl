{**
 * zendSearchSettingsForm.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Zend Search settings form.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="plugins.generic.zendSearch.settings"}
{assign var="helpTopicId" value="plugins.generic.zendSearch.settings"}
{assign var="pageId" value="manager.searchFormElements.zendSearchSettingsForm"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
<!--
{literal}
function changeElementType() {
	changeFormAction('searchFormElement', '{/literal}{$searchFormElementUrl|escape:"javascript"}{literal}');
}
{/literal}
// -->
</script>

<br/>

<form name="zendSearchSettings" method="post" action="{url op="saveSettings"}">

{include file="common/formErrors.tpl"}

<table class="data" width="100%">
{* Currently no locale fields in this form.
{if count($formLocales) > 1}
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
		<td width="80%" class="value">
			{url|assign:"zendSearchSettingsFormUrl" op="settings" escape=false}
			{form_language_chooser form="zendSearchSettings" url=$zendSearchSettingsFormUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
{else}
	<input type="hidden" name="formLocale" value="{$formLocale|escape}" />
{/if}
*}
<tr valign="top">
	<td width="20%" class="label">{fieldLabel name="solrUrl" required="true" key="plugins.generic.zendSearch.solrUrl"}</td>
	<td width="80%" class="value">
		<input type="text" name="solrUrl" value="{$solrUrl|escape}" size="40" id="solrUrl" maxlength="80" class="textField" /><br/>
		<span class="instruct">{translate key="plugins.generic.zendSearch.solrUrl.description}</span>
	</td>
</tr>
</table>

<p><input type="submit" value="{translate key="common.save"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="index" escape=false}'" /></p>

</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{include file="common/footer.tpl"}
