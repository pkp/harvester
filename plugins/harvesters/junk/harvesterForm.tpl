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
		<td class="label">{fieldLabel name="junkUrl" key="plugins.harvesters.junk.archive.form.junkUrl" required="true"}</td>
		<td class="value">
			<input type="text" id="junkUrl" name="junkUrl" value="{$junkUrl|escape}" size="40" maxlength="120" class="textField" />
			<br/>
			{translate key="plugins.harvesters.junk.archive.form.junkUrl.description"}
		</td>
	</tr>
