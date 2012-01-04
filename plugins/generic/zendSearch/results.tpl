{**
 * results.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Search results for Zend Framework's Lucene implementation.
 *
 * $Id$
 *}
{strip}
{url|assign:"currentUrl" op="searchResults" q=$q}
{assign var="pageTitle" value="record.records"}
{assign var="helpTopicId" value="plugins.generic.zendSearch.search"}
{include file="common/header.tpl"}
{/strip}

<div id="results">
<ul class="plain">
{iterate from=results item=record}
	{if $record}<li>&#187; {$record->displaySummary()}</li>{/if}
{/iterate}
</ul>
	{page_info iterator=$results}&nbsp;&nbsp;&nbsp;&nbsp;{page_links anchor="results" name="results" iterator=$results q=$q}
</div>
{include file="common/footer.tpl"}
