{**
 * settings.tpl
 *
 * Copyright (c) 2005-2007 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Site settings form.
 *
 * $Id$
 *}

{assign var="pageTitle" value="admin.siteSettings"}
{assign var="helpTopicId" value="admin.siteSettings"}
{include file="common/header.tpl"}

<form method="post" action="{url op="saveSettings"}" enctype="multipart/form-data">
{include file="common/formErrors.tpl"}

<h3>{translate key="admin.settings.general"}</h3>

<table class="data" width="100%">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="title" key="admin.settings.siteTitle" required="true"}</td>
		<td width="80%" class="value"><input type="text" id="title" name="title" value="{$title|escape}" size="40" maxlength="120" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="intro" key="admin.settings.introduction"}</td>
		<td class="value"><textarea name="intro" id="intro" cols="40" rows="10" class="textArea">{$intro|escape}</textarea></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="about" key="admin.settings.aboutDescription"}</td>
		<td class="value"><textarea name="about" id="about" cols="40" rows="10" class="textArea">{$about|escape}</textarea></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel for="customLogo" key="admin.settings.customLogo"}</td>
		<td width="80%" class="value">
			<input type="file" name="customLogo" class="uploadField" /> <input type="submit" name="uploadCustomLogo" value="{translate key="common.upload"}" class="button" />
			{if $customLogo}
				<br />
				{translate key="common.fileName"}: {$customLogo.name} {$customLogo.dateUploaded|date_format:$datetimeFormatShort} <input type="submit" name="deleteCustomLogo" value="{translate key="common.delete"}" class="button" />
				<br />
				<img src="{$publicFilesDir}/{$customLogo.uploadName}" width="{$customLogo.width}" height="{$customLogo.height}" border="0" alt="" />
{/if}
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

<div class="separator"></div>

<h3>{translate key="admin.settings.administration"}</h3>

<table class="data" width="100%">
	<tr valign="top">	
		<td width="20%" class="label">{fieldLabel name="adminUsername" key="user.username"}</td>
		<td width="80%" class="value"><input type="text" name="adminUsername" id="adminUsername" value="{$adminUsername|escape}" size="20" maxlength="32" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="adminPassword" key="user.password"}</td>
		<td class="value"><input type="password" name="adminPassword" id="adminPassword" value="{$adminPassword|escape}" size="20" maxlength="32" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="adminPassword2" key="user.register.repeatPassword"}</td>
		<td class="value"><input type="password" name="adminPassword2" id="adminPassword2" value="{$adminPassword2|escape}" size="20" maxlength="32" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="contactName" key="admin.settings.contactName" required="true"}</td>
		<td class="value"><input type="text" id="contactName" name="contactName" value="{$contactName|escape}" size="40" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="contactEmail" key="admin.settings.contactEmail" required="true"}</td>
		<td class="value"><input type="text" id="contactEmail" name="contactEmail" value="{$contactEmail|escape}" size="40" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{translate key="admin.settings.options"}</td>
		<td class="value">
			<input type="checkbox" {if $enableSubmit}checked {/if}id="enableSubmit" name="enableSubmit" value="1" />&nbsp;
			{fieldLabel name="enableSubmit" key="admin.settings.options.enableSubmit"}
		</td>
	</tr>
</table>

<p><input type="submit" value="{translate key="common.save"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url page="admin"}'" /></p>

</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{include file="common/footer.tpl"}
