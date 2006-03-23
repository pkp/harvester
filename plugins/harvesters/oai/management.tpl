{**
 * management.tpl
 *
 * Copyright (c) 2005-2006 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Archive management.
 *
 * $Id$
 *}

	<tr valign="top">
		<td class="label"><label for="sets">{translate key="plugins.harvesters.oai.archive.form.sets"}</td>
		<td class="value">
			<select class="selectMenu" size="5" multiple name="sets[]" id="sets">
				<option {if empty($selectedSets)}selected {/if}value="">{translate key="plugins.harvesters.oai.archive.form.allSets"}</option>
				{foreach from=$availableSets key=setSpec item=setName}
					<option {if in_array($setSpec, $availableSets)}selected {/if}value="{$setSpec|escape}">{$setName|escape}</option>
				{/foreach}
			</select>&nbsp;<input type="button" class="button" value="{translate key="common.refresh"}" onclick="selectHarvester()"/>
		</td>
	</tr>
