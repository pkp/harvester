{**
 * formErrors.tpl
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List errors that occurred during form processing.
 *
 * $Id$
 *}
{if $isError}
<p>
	<div id="formErrors">
	<span class="formError">{translate key="form.errorsOccurred"}:</span>
	<ul class="formErrorList">
	{foreach key=field item=message from=$errors}
		<li>{$message}</li>
	{/foreach}
	</ul>
	</div>
</p>
<script type="text/javascript">
{literal}
<!--
// Jump to form errors.
window.location.hash="formErrors";
// -->
{/literal}
</script>
{/if}
