{**
 * index.tpl
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User index.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="user.userHome"}
{include file="common/header.tpl"}
{/strip}

<h4><a href="{url page="user"}">{$siteTitle|escape}</a></h4>

<ul class="plain">
	{foreach from=$userRoles item=role}
		{assign var="hasRole" value=1}
			<li>&#187; <a href="{url page=$role->getRolePath()}">{translate key=$role->getRoleName()}</a></li>
	{foreachelse}
		<li>
			{translate key="user.noRoles"}
			<ul class="plain">
			{if $enableSubmit}
				{url|assign:"submitUrl" page="add"}
				<li>&#187; <a href="{url op="become" path="submitter" source=$submitUrl}">{translate key="user.noRoles.submitArchive"}</a>
			{else}
				{translate key="user.noRoles.enableSubmitClosed"}
			{/if}
			</ul>
		</li>
	{/foreach}{* $userRoles *}
	{call_hook name="Templates::User::Index::Site"}
</ul>

<h3>{translate key="user.myAccount"}</h3>
<ul class="plain">
	<li>&#187; <a href="{url page="user" op="profile"}">{translate key="user.editMyProfile"}</a></li>
	<li>&#187; <a href="{url page="user" op="changePassword"}">{translate key="user.changeMyPassword"}</a></li>
	<li>&#187; <a href="{url page="login" op="signOut"}">{translate key="user.logOut"}</a></li>
	{call_hook name="Templates::User::Index::MyAccount"}
</ul>

{include file="common/footer.tpl"}
