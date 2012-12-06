{**
 * templates/admin/plugins.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List available import/export plugins.
 *}
{strip}
{assign var="helpTopicId" value="admin.plugins"}
{include file="common/header.tpl"}
{/strip}

<!-- Plugin grid -->
{url|assign:pluginGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.plugins.SettingsPluginGridHandler" op="fetchGrid"}
{load_url_in_div id="pluginGridContainer" url="$pluginGridUrl"}

{include file="common/footer.tpl"}
