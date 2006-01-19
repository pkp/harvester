{**
 * header.tpl
 *
 * Copyright (c) 2003-2004 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site header.
 *
 * $Id$
 *}

{if !$pageTitleTranslated}{translate|assign:"pageTitleTranslated" key=$pageTitle}{/if}
{if $pageCrumbTitle}{translate|assign:"pageCrumbTitleTranslated" key=$pageCrumbTitle}{elseif !$pageCrumbTitleTranslated}{assign var="pageCrumbTitleTranslated" value=$pageTitleTranslated}{/if}
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset}" />
	<title>{$pageTitleTranslated}</title>
	<meta name="description" content="{$metaSearchDescription}" />
	<meta name="keywords" content="{$metaSearchKeywords}" />
	{$metaCustomHeaders}
	<link rel="stylesheet" href="{$baseUrl}/styles/common.css" type="text/css" />
	{foreach from=$stylesheets item=cssFile}
	<link rel="stylesheet" href="{$baseUrl}/styles/{$cssFile}" type="text/css" />
	{/foreach}
	{if $pageStyleSheet}
	<link rel="stylesheet" href="{$publicFilesDir}/{$pageStyleSheet.uploadName}" type="text/css" />
	{/if}
	<script type="text/javascript" src="{$baseUrl}/js/general.js"></script>
	{$additionalHeadData}
</head>
<body>
<div id="container">

<div id="header">
<h1>
	<img src="{$publicFilesDir}/logo.png" width="331" height="52" border="0" alt="{translate key="common.harvester2"}" />
</h1>
</div>

<div id="body">

	<div id="sidebar">
		{include file="common/sidebar.tpl"}
	</div>

<div id="main">
<div id="navbar">
	<ul class="menu">
		<li><a href="{url page="index"}">{translate key="navigation.home"}</a></li>
		<li><a href="{url page="about"}">{translate key="navigation.about"}</a></li>
		{if $isUserLoggedIn}
			<li><a href="{url page="admin"}">{translate key="navigation.administration"}</a></li>
		{else}
			<li><a href="{url page="login"}">{translate key="navigation.login"}</a></li>
		{/if}
		<li><a href="{url page="search"}">{translate key="navigation.search"}</a></li>
		{foreach from=$navMenuItems item=navItem}
		<li><a href="{if $navItem.isAbsolute}{$navItem.url|escape}{else}{url page=""}{$navItem.url|escape}{/if}">{if $navItem.isLiteral}{$navItem.name|escape}{else}{translate key=$navItem.name}{/if}</a></li>
		{/foreach}
		<li><a href="javascript:openHelp('{if $helpTopicId}{get_help_id key="$helpTopicId" url="true"}{else}{url page="help"}{/if}')">{translate key="navigation.help"}</a></li>
	</ul>
</div>

<div id="breadcrumb">
	<a href="{url page="index"}">{translate key="navigation.home"}</a> &gt;
	{foreach from=$pageHierarchy item=hierarchyLink}
		<a href="{$hierarchyLink[0]}" class="hierarchyLink">{if not $hierarchyLink[2]}{translate key=$hierarchyLink[1]}{else}{$hierarchyLink[1]}{/if}</a> &gt;
	{/foreach}
	<a href="{$currentUrl}" class="current">{$pageCrumbTitleTranslated}</a>
</div>

<h2>{$pageTitleTranslated}</h2>

<div id="content">
