{**
 * archiveForm.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Basic archive settings under site administration.
 *
 * $Id$
 *}
	<tr valign="top">
		<td class="label">{fieldLabel name="harvesterUrl" key="plugins.harvesters.junk.archive.form.harvesterUrl" required="true"}</td>
		<td class="value">
			<input type="text" id="harvesterUrl" name="harvesterUrl" value="{$harvesterUrl|escape}" size="40" maxlength="120" class="textField" />
			<br/>
			{translate key="plugins.harvesters.junk.archive.form.harvesterUrl.description"}
		</td>
	</tr>
