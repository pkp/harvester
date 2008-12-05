{**
 * results.tpl
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Search results for Zend Framework's Lucene implementation.
 *
 * $Id$
 *}
{strip}
{url|assign:"currentUrl" op="searchResults" q=$q}
{assign var="pageTitle" value="record.records"}
{assign var="helpTopicId" value="index.browse"}
{include file="common/header.tpl"}
{/strip}

<div id="results">
	<ul class="plain">
	{iterate from=results item=result}
		{assign var=document value=$result->getDocument()}
		{assign var=recordId value=$document->getFieldValue('harvesterRecordId')}
		{assign var=record value=$recordDao->getRecord($recordId)}
		{if $record}<li>&#187; {$record->displaySummary()}</li>{/if}
	{/iterate}
	</ul>
		{page_info iterator=$results}&nbsp;&nbsp;&nbsp;&nbsp;{page_links anchor="results" name="results" iterator=$results q=$q}
</div>
{include file="common/footer.tpl"}
