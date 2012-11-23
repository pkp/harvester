{**
 * templates/admin/languages.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to edit site language settings.
 *
 *}
{strip}
{assign var="pageTitle" value="common.languages"}
{assign var="helpTopicId" value="admin.languages"}
{include file="common/header.tpl"}
{/strip}

{url|assign:languagesUrl router=$smarty.const.ROUTE_COMPONENT component="grid.admin.languages.AdminLanguageGridHandler" op="fetchGrid"}
{load_url_in_div id="languageGridContainer" url=$languagesUrl}

{include file="common/footer.tpl"}
