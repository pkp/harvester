{**
 * plugins/harvesters/oai/archiveInfo.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Public archive info page (extends the default template)
 *
 *}

<table id="archiveListing" class="listing" width="100%">
	<tr valign="top">
		<td width="20%" class="label">{translate key="archive.type"}</td>
		<td width="80%" colspan="2" class="value">{translate key="plugins.harvesters.oai.protocolName"}</td>
	</tr>
	<tr valign="top"><td colspan="3" class="separator">&nbsp;</td></tr>
	<tr valign="top">
		<td width="20%" class="label">{translate key="plugins.harvesters.oai.archive.form.harvesterUrl"}</td>
		<td width="80%" colspan="2" class="value">{$archive->getSetting("harvesterUrl")|escape}</td>
	</tr>
</table>
