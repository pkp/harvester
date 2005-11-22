<?php /* Smarty version 2.6.10, created on 2005-11-20 21:32:28
         compiled from admin/archiveForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'admin/archiveForm.tpl', 27, false),array('function', 'fieldLabel', 'admin/archiveForm.tpl', 45, false),array('function', 'translate', 'admin/archiveForm.tpl', 57, false),array('function', 'call_hook', 'admin/archiveForm.tpl', 74, false),)), $this); ?>

<?php if ($this->_tpl_vars['archiveId']): ?>
	<?php $this->assign('pageTitle', "admin.archives.editArchive");  else: ?>
	<?php $this->assign('pageTitle', "admin.archives.addArchive");  endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<br />

<script type="text/javascript">
<!--

<?php echo '
function selectHarvester() {
	document.archiveForm.action="';  echo ((is_array($_tmp=$this->_tpl_vars['pageUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
/admin/editArchive<?php if ($this->_tpl_vars['archiveId']): ?>/<?php echo $this->_tpl_vars['archiveId'];  endif;  echo '#archiveForm";
	document.archiveForm.submit();
}

'; ?>

// -->
</script>

<a name="archiveForm"/>
<form name="archiveForm" method="post" action="<?php echo $this->_tpl_vars['pageUrl']; ?>
/admin/updateArchive">
<?php if ($this->_tpl_vars['archiveId']): ?>
<input type="hidden" name="archiveId" value="<?php echo $this->_tpl_vars['archiveId']; ?>
" />
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<table class="data" width="100%">
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'title','key' => "archive.title",'required' => 'true'), $this);?>
</td>
		<td width="80%" class="value"><input type="text" id="title" name="title" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="40" maxlength="120" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'description','key' => "archive.description"), $this);?>
</td>
		<td class="value"><textarea name="description" id="description" cols="40" rows="10" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['description'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</textarea></td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'url','key' => "archive.url",'required' => 'true'), $this);?>
</td>
		<td class="value">
			<input type="text" id="url" name="url" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="40" maxlength="120" class="textField" />
			<br/>
			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.archives.form.url.description"), $this);?>

		</td>
	</tr>

		<tr>
			<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'harvester','key' => "archive.harvester",'required' => 'true'), $this);?>
</td>
			<td><select onchange="selectHarvester()" name="harvesterPlugin" id="harvesterPlugin" size="1" class="selectMenu">
				<?php $_from = $this->_tpl_vars['harvesters']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['harvester']):
?>
					<option <?php if ($this->_tpl_vars['harvester']->getName() == $this->_tpl_vars['harvesterPlugin']): ?>selected="selected" <?php endif; ?>value="<?php echo $this->_tpl_vars['harvester']->getName(); ?>
"><?php echo $this->_tpl_vars['harvester']->getProtocolDisplayName(); ?>
</option>
				<?php endforeach; endif; unset($_from); ?>
			</select></td>
		</tr>
		<?php if (! $this->_tpl_vars['harvesterPlugin']): ?>
						<?php $this->assign('harvesterPlugin', $this->_tpl_vars['harvester']->getName()); ?>
		<?php endif; ?>

		<?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Template::Admin::Archives::displayHarvesterForm",'plugin' => $this->_tpl_vars['harvesterPlugin']), $this);?>

</table>

<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
" class="button defaultButton" /> <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_tpl_vars['pageUrl']; ?>
/admin/archives'" /></p>

</form>

<p><span class="formRequired"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>
</span></p>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>