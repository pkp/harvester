{**
 * archiveForm.tpl
 *
 * Copyright (c) 2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Basic archive settings under site administration.
 *
 * $Id$
 *}

	<tr valign="top">
		<td class="label">{fieldLabel name="harvesterUrl" key="plugins.harvesters.oai.archive.form.harvesterUrl" required="true"}</td>
		<td class="value">
			<input type="text" id="harvesterUrl" name="harvesterUrl" value="{$harvesterUrl|escape}" size="40" maxlength="120" class="textField" />
			<br/>
			{translate key="plugins.harvesters.oai.archive.form.harvesterUrl.description"}
		</td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="oaiIndexMethod" key="plugins.harvesters.oai.archive.form.oaiIndexMethod" required="true"}</td>
		<td class="value">
			<select class="selectMenu" name="oaiIndexMethod" id="oaiIndexMethod">
				{html_options options=$oaiIndexMethods selected=$oaiIndexMethod}
			</select>
		</td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="metadataFormat" key="plugins.harvesters.oai.archive.form.metadataFormat" required="true"}</td>
		<td class="value">
			<select class="selectMenu" name="metadataFormat" id="metadataFormat">
				{html_options options=$metadataFormats selected=$metadataFormat}
			</select>&nbsp;<input type="button" class="button" value="{translate key="common.refresh"}" onclick="selectHarvester()"/>
		</td>
	</tr>
