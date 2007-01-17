{**
 * archiveInfo.tpl
 *
 * Copyright (c) 2005-2007 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Archive information page.
 *
 * $Id$
 *}

{* Make this archive known to the quick search sidebar *}
{assign var="theseArchiveIds" value=$archive->getArchiveId()|to_array}

{assign var="pageTitle" value="browse.archiveInfo"}
{assign var="helpTopicId" value="index.browse"}
{include file="common/header.tpl"}

<br />

<table class="listing" width="100%">
	<tr><td class="headseparator" colspan="2">&nbsp;</td></tr>
	<tr valign="top">
		<td class="label" width="20%">{translate key="archive.title"}</td>
		<td class="value" width="80%">{$archive->getTitle()|escape}</td>
	</tr>
	<tr><td class="headseparator" colspan="2">&nbsp;</td></tr>
	<tr valign="top">
		<td class="label" width="20%">{translate key="archive.description"}</td>
		<td class="value" width="80%">{$archive->getDescription()|strip_unsafe_html}</td>
	</tr>
	<tr><td class="separator" colspan="2">&nbsp;</td></tr>
	<tr valign="top">
		<td class="label" width="20%">{translate key="archive.url"}</td>
		<td class="value" width="80%"><a href="{$archive->getUrl()}">{$archive->getUrl()|escape}</a></td>
	</tr>
	<tr><td class="headseparator" colspan="2">&nbsp;</td></tr>
	<tr valign="top">
		<td class="label" width="20%">{translate key="archive.recordCount"}</td>
		<td class="value" width="80%">{$archive->getRecordCount()}</td>
	</tr>
	<tr><td class="separator" colspan="2">&nbsp;</td></tr>
	<tr valign="top">
		<td class="label" width="20%">{translate key="archive.lastIndexed"}</td>
		<td class="value" width="80%">{$archive->getLastIndexedDate()|date_format:$dateFormatShort|default:"&mdash;"}</td>
	</tr>
	<tr><td class="headseparator" colspan="2">&nbsp;</td></tr>
	{call_hook name="Template::Browse::ArchiveInfo::DisplayExtendedArchiveInfo" archive=$archive}
</table>
<br/>
<a href="{url op="index" path=$archive->getArchiveId()}" class="action">{translate key="browse.browseRecords"}</a>
{include file="common/footer.tpl"}
