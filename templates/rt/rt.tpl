{**
 * templates/rt/rt.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
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

</div>
{/if}

