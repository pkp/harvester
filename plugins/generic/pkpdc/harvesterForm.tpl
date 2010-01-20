{**
 * harvesterForm.tpl
 *
 * Copyright (c) 2005-2010 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Extend the form to include an OJS version selector.
 *
 * $Id$
 *}
	<tr valign="top">
		<td class="label">{fieldLabel name="pkpDcHandling" key="plugins.generic.pkpdc.archive.form.pkpDcHandling"}</td>
		<td class="value">
			<select id="pkpDcHandling" name="pkpDcHandling" class="selectMenu"/>
				<option value="">{translate key="plugins.generic.pkpdc.archive.form.pkpDcHandling.disable"}</option>
				<option {if $pkpDcHandling == '2.x'}selected {/if}value="2.x">{translate key="plugins.generic.pkpdc.archive.form.pkpDcHandling.2x"}</option>
				<option {if $pkpDcHandling == '1.x'}selected {/if}value="1.x">{translate key="plugins.generic.pkpdc.archive.form.pkpDcHandling.1x"}</option>
			</select>
			<br/>
			{translate key="plugins.generic.pkpdc.archive.form.pkpDcHandling.description"}
		</td>
	</tr>
