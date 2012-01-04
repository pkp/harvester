{**
 * settings.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Site settings form.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="admin.siteSettings"}
{assign var="helpTopicId" value="admin.siteSettings"}
{include file="common/header.tpl"}
{/strip}

<form method="post" action="{url op="saveSettings"}" enctype="multipart/form-data">
{include file="common/formErrors.tpl"}
<div id="general">
<h3>{translate key="admin.settings.general"}</h3>

<table class="data" width="100%">
{if count($formLocales) > 1}
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
		<td colspan="2" width="80%" class="value">
			{url|assign:"settingsUrl" op="settings" escape=false}
			{form_language_chooser form="settings" url=$settingsUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
{/if}
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="title" key="admin.settings.siteTitle" required="true"}</td>
		<td width="80%" class="value"><input type="text" id="title" name="title[{$formLocale|escape}]" value="{$title[$formLocale]|escape}" size="40" maxlength="120" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="intro" key="admin.settings.introduction"}</td>
		<td class="value"><textarea name="intro[{$formLocale|escape}]" id="intro" cols="40" rows="10" class="textArea">{$intro[$formLocale]|escape}</textarea></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="aboutField" key="admin.settings.aboutDescription"}</td>
		<td class="value"><textarea name="about[{$formLocale|escape}]" id="aboutField" cols="40" rows="10" class="textArea">{$about[$formLocale]|escape}</textarea></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel for="customLogo" key="admin.settings.customLogo"}</td>
		<td width="80%" class="value">
			<input type="file" name="customLogo" class="uploadField" /> <input type="submit" name="uploadCustomLogo" value="{translate key="common.upload"}" class="button" />
			{if $customLogo}
				<br />
				{translate key="common.fileName"}: {$customLogo.uploadName} {$customLogo.dateUploaded|date_format:$datetimeFormatShort} <input type="submit" name="deleteCustomLogo" value="{translate key="common.delete"}" class="button" />
				<br />
				<img src="{$publicFilesDir}/{$customLogo.uploadName}" width="{$customLogo.width}" height="{$customLogo.height}" border="0" alt="" />
{/if}
		</td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="minPasswordLength" key="admin.settings.minPasswordLength" required="true"}</td>
		<td colspan="2" class="value"><input type="text" id="minPasswordLength" name="minPasswordLength" value="{$minPasswordLength|escape}" size="4" maxlength="2" class="textField" /> {translate key="admin.settings.passwordCharacters"}</td>
	</tr>
	<tr>
		<td width="20%" class="label"><label for="theme">{translate key="admin.settings.theme"}</label></td>
		<td width="80%" class="value">
			<select name="theme" class="selectMenu" id="theme"{if empty($themes)} disabled="disabled"{/if}>
				<option value="">{translate key="common.none"}</option>
				{foreach from=$themes key=path item=themePlugin}
					<option value="{$path|escape}"{if $path == $theme} selected="selected"{/if}>{$themePlugin->getDisplayName()}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	<tr valign="top">
		<td class="label">{translate key="admin.settings.styleSheet"}</td>
		<td class="value">
			<input type="file" name="styleSheet" class="uploadField" /> <input type="submit" name="uploadStyleSheet" value="{translate key="common.upload"}" class="button" />
			{if $styleSheet}
				<br />
				{translate key="common.fileName"}: <a href="{$publicFilesDir}/{$styleSheet.uploadName}" class="file">{$styleSheet.name}</a> {$styleSheet.dateUploaded|date_format:$datetimeFormatShort} <input type="submit" name="deleteStyleSheet" value="{translate key="common.delete"}" class="button" />
			{/if}
		</td>
	</tr>
</table>
</div>
<div class="separator"></div>
<div id="administration">
<h3>{translate key="admin.settings.administration"}</h3>

<table class="data" width="100%">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="contactName" key="admin.settings.contactName" required="true"}</td>
		<td width="80%" class="value"><input type="text" id="contactName" name="contactName[{$formLocale|escape}]" value="{$contactName[$formLocale]|escape}" size="40" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="contactEmail" key="admin.settings.contactEmail" required="true"}</td>
		<td class="value"><input type="text" id="contactEmail" name="contactEmail[{$formLocale|escape}]" value="{$contactEmail[$formLocale]|escape}" size="40" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td rowspan="2" class="label">{translate key="admin.settings.options"}</td>
		<td class="value">
			<input type="checkbox" {if $enableSubmit}checked="checked" {/if}id="enableSubmit" name="enableSubmit" value="1" />&nbsp;
			{fieldLabel name="enableSubmit" key="admin.settings.options.enableSubmit"}
		</td>
	</tr>
	<tr valign="top">
		<td class="value">
			<input type="checkbox" {if $disableSubmissions}checked="checked" {/if}id="disableSubmissions" name="disableSubmissions" value="1" />&nbsp;
			{fieldLabel name="disableSubmissions" key="admin.settings.options.disableSubmissions"}
		</td>
	</tr>
</table>
</div>
<p><input type="submit" value="{translate key="common.save"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url page="admin"}'" /></p>

</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{include file="common/footer.tpl"}
