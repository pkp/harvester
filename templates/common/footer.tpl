{**
 * footer.tpl
 *
 * Copyright (c) 2005-2007 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site footer.
 *
 * $Id$
 *}
</div>
</div>
</div>

<div id="footer">
{if $footer}{$footer|nl2br}{/if}

{get_debug_info}
{if $enableDebugStats}
<div class="debugStats">
	{translate key="debug.executionTime"}: {$debugExecutionTime|string_format:"%.4f"}s<br />
	{translate key="debug.databaseQueries"}: {$debugNumDatabaseQueries}<br/>
	{if $debugNotes}
		<strong>{translate key="debug.notes"}</strong><br/>
		{foreach from=$debugNotes item=note}
			{translate key=$note[0] params=$note[1]}<br/>
		{/foreach}
	{/if}
</div>
{/if}
</div>

</div>
</body>
</html>
