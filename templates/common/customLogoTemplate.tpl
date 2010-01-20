{**
 * customLogoTemplate.tpl
 *
 * Copyright (c) 2005-2010 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Harvester's logo template
 *
 *}
{if $useCustomLogo}
	<img src="{$publicFilesDir}/{$useCustomLogo.uploadName}" type="text/css" />
{else}
	<img src="{$publicFilesDir}/logo.png" width="331" height="52" border="0" alt="{translate key="common.harvester2"}" />
{/if}