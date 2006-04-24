{**
 * rt.tpl
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Reading Tools.
 *
 * $Id$
 *}

{if $version}
<div class="block">

<span class="blockTitle">{translate key="rt.readingTools"}</span>

<span class="rtSubtitle">{translate key="rt.forThisRecord"}</span>
<ul>
	{foreach from=$version->getContexts() item=context}
		{if $context->getDefineTerms()}
			<li><a href="javascript:openRTWindowWithToolbar('{url page="rt" op="context" path=$record->getRecordId()|to_array:$context->getContextId()}');">{$context->getTitle()|escape}</a></li>
		{/if}
	{/foreach}
</ul>
<br />

<span class="rtSubtitle">{translate key="rt.relatedItems"}</span>
<ul>
{foreach from=$version->getContexts() item=context}
	{if !$context->getDefineTerms()}
		<li><a href="javascript:openRTWindowWithToolbar('{url page="rt" op="context" path=$record->getRecordId()|to_array:$context->getContextId()}');">{$context->getTitle()|escape}</a></li>
	{/if}
{/foreach}
</ul>

<br />

<span class="rtSubtitle">{translate key="rt.thisArchive"}</span>
<form method="post" action="{url page="search" op="results" archiveIds=$archive->getArchiveId()|to_array}" target="_parent">
<table>
<tr>
	<td><input type="text" id="query" name="query" size="15" maxlength="255" value="" class="textField" /></td>
</tr>
<tr>
	<td><input type="submit" value="{translate key="common.search"}" class="button" /></td>
</tr>
</table>
</form>

</div>
{/if}
