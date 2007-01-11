{**
 * sidebar.tpl
 *
 * Copyright (c) 2005-2006 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Sidebar for site index.
 *
 * $Id$
 *}

<div class="block">
	<span class="blockTitle"><img alt="{translate key="sidebar.harvesterStats"}" src="{$publicFilesDir}/stats.png" align="right" width="25" height="25"/>{translate key="sidebar.harvesterStats"}</span>
	{translate key="sidebar.harvesterStats.description" recordCount=$recordCount archiveCount=$archiveCount}
</div>

{if $enableSubmit}
	<div class="block">
		<span class="blockTitle"><img alt="{translate key="sidebar.addYourArchive"}" src="{$publicFilesDir}/add.png" align="right" width="25" height="25"/>{translate key="sidebar.addYourArchive"}</span>
		{url|assign:"addUrl" page="add"}
		{translate key="sidebar.addYourArchive.description" addUrl=$addUrl}
	</div>
{/if}
