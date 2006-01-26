{**
 * sidebar.tpl
 *
 * Copyright (c) 2003-2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site sidebar menu.
 *
 * $Id$
 *}

{if $isUserLoggedIn}
	<div class="block">
		<span class="blockTitle">{translate key="navigation.administration"}</span>
		{translate key="navigation.loggedInAs"}<br />
		<strong>{$loggedInUsername|escape}</strong>

		<ul>
			<li><a href="{url page="admin"}">{translate key="navigation.administration"}</a></li>
			<li><a href="{url page="login" op="signOut"}">{translate key="navigation.logout"}</a></li>
		</ul>
	</div>
{/if}

{if $sidebarTemplate}
	{include file=$sidebarTemplate}
{/if}

{if $enableLanguageToggle}
<div class="block">
	<span class="blockTitle">{translate key="common.language"}</span>
	<form action="#">
		<select size="1" name="locale" onchange="location.href={if $languageToggleNoUser}'{$currentUrl}{if strstr($currentUrl, '?')}&{else}?{/if}setLocale='+this.options[this.selectedIndex].value{else}('{url page="index" op="setLocale" path="LOCALE_NAME" source=$smarty.server.REQUEST_URI escape="false"}'.replace('LOCALE_NAME', this.options[this.selectedIndex].value)){/if}" class="selectMenu">{html_options options=$languageToggleLocales selected=$currentLocale}</select>
	</form>
</div>
{/if}

<div class="block">
	<span class="blockTitle">{translate key="navigation.content"}</span>

	<span class="blockSubtitle">{translate key="navigation.search"}</span>
	<form method="get" action="{url page="search" op="results"}">
		<table>
			<tr>
				<td><input type="text" id="query" name="query" size="15" maxlength="255" value="" class="textField" /></td>
			</tr>
			<tr>
				<td><select name="searchField" size="1" class="selectMenu">
					<option value="FIXME">FIXME</option>
					{html_options_translate options=$articleSearchByOptions}
				</select></td>
			</tr>
			<tr>
				<td><input type="submit" value="{translate key="common.search"}" class="button" /></td>
			</tr>
		</table>
	</form>
</div>

