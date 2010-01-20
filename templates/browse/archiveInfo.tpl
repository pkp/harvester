{**
 * archiveInfo.tpl
 *
 * Copyright (c) 2005-2010 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Archive information page.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="browse.archiveInfo"}
{assign var="helpTopicId" value="index.browse"}
{include file="common/header.tpl"}
{/strip}

<h3>{$archive->getTitle()|escape}</h3>

<a href="{$archive->getUrl()|escape}">{$archive->getUrl()|escape}</a><br />

{if $archive->getSetting('description') != ''}<p>{$archive->getSetting('description')|nl2br|strip_unsafe_html}</p>{/if}

{assign var=archiveImage value=$archive->getSetting('archiveImage')}
{if $archiveImage}
	{translate|assign:"defaultAlt" key="archive.image"}
	<img src="{$publicFilesDir}/{$archiveImage.uploadName|escape:"url"}" alt="{$archiveImage.altText|escape|default:$defaultAlt}"/><br />
{/if}

{call_hook name="Template::Browse::ArchiveInfo::DisplayExtendedArchiveInfo" archive=$archive}

<br/>
<a href="{url op="index" path=$archive->getArchiveId()}" class="action">{translate key="browse.browseRecords"}</a>
{include file="common/footer.tpl"}
