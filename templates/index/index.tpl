{**
 * index.tpl
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Site index.
 *
 * $Id$
 *}
{strip}
{assign var=pageTitleTranslated value=$title}
{include file="common/header.tpl"}
{/strip}

<br />

{if $intro}
<p>{$intro|nl2br}</p>
{/if}

{include file="common/footer.tpl"}
