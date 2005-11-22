<?php /* Smarty version 2.6.10, created on 2005-11-20 17:42:02
         compiled from admin/languages.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'admin/languages.tpl', 17, false),array('modifier', 'escape', 'admin/languages.tpl', 25, false),)), $this); ?>

<?php $this->assign('pageTitle', "common.languages");  $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<form method="post" action="<?php echo $this->_tpl_vars['pageUrl']; ?>
/admin/saveLanguageSettings">

<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.languages.languageSettings"), $this);?>
</h3>

<table class="form">
<tr valign="top">
	<td width="20%" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "locale.primary"), $this);?>
</td>
	<td width="80%" class="value">
		<select name="primaryLocale" id="primaryLocale" size="1" class="selectMenu">
		<?php $_from = $this->_tpl_vars['installedLocales']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['localeKey']):
?>
			<option value="<?php echo $this->_tpl_vars['localeKey']; ?>
"<?php if ($this->_tpl_vars['localeKey'] == $this->_tpl_vars['primaryLocale']): ?> selected="selected"<?php endif; ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['localeNames'][$this->_tpl_vars['localeKey']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</option>
		<?php endforeach; endif; unset($_from); ?>
		</select>
		<br />
		<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.languages.primaryLocaleInstructions"), $this);?>
</span>
	</td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "locale.supported"), $this);?>
</td>
	<td>
		<table width="100%">
		<?php $_from = $this->_tpl_vars['installedLocales']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['localeKey']):
?>
		<tr valign="top">
			<td width="5%"><input type="checkbox" name="supportedLocales[]" id="supportedLocales-<?php echo $this->_tpl_vars['localeKey']; ?>
" value="<?php echo $this->_tpl_vars['localeKey']; ?>
"<?php if (in_array ( $this->_tpl_vars['localeKey'] , $this->_tpl_vars['supportedLocales'] )): ?> checked="checked"<?php endif; ?> /></td>
			<td width="95%"><label for="supportedLocales-<?php echo $this->_tpl_vars['localeKey']; ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['localeNames'][$this->_tpl_vars['localeKey']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</label></td>
		</tr>
		<?php endforeach; endif; unset($_from); ?>
		</table>
		<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.languages.supportedLocalesInstructions"), $this);?>
</span>
	</td>
</tr>
</table>

<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
" class="button defaultButton" /> <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_tpl_vars['pageUrl']; ?>
/admin'" /></p>

</form>

<div class="separator"></div>

<form method="post" action="<?php echo $this->_tpl_vars['pageUrl']; ?>
/admin/installLocale">

<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.languages.installLanguages"), $this);?>
</h3>
<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.languages.installedLocales"), $this);?>
</h4>
<ul>
<table class="data" width="100%">
<?php $_from = $this->_tpl_vars['installedLocales']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['localeKey']):
?>
<tr valign="top">
	<td width="20%"><li><?php echo ((is_array($_tmp=$this->_tpl_vars['localeNames'][$this->_tpl_vars['localeKey']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
 (<?php echo $this->_tpl_vars['localeKey']; ?>
)</li></td>
	<td width="80%"><a href="<?php echo $this->_tpl_vars['pageUrl']; ?>
/admin/reloadLocale?locale=<?php echo $this->_tpl_vars['localeKey']; ?>
" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.languages.confirmReload"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : smarty_modifier_escape($_tmp, 'javascript'));?>
')" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.languages.reload"), $this);?>
</a><?php if ($this->_tpl_vars['localeKey'] != $this->_tpl_vars['primaryLocale']): ?> <a href="<?php echo $this->_tpl_vars['pageUrl']; ?>
/admin/uninstallLocale?locale=<?php echo $this->_tpl_vars['localeKey']; ?>
" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.languages.confirmUninstall"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : smarty_modifier_escape($_tmp, 'javascript'));?>
')" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.languages.uninstall"), $this);?>
</a><?php endif; ?></td>
</tr>
<?php endforeach; endif; unset($_from); ?>
</table>
</ul>

<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.languages.installNewLocales"), $this);?>
</h4>
<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.languages.installNewLocalesInstructions"), $this);?>
</p>
<?php $_from = $this->_tpl_vars['uninstalledLocales']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['localeKey']):
?>
<input type="checkbox" name="installLocale[]" id="installLocale-<?php echo $this->_tpl_vars['localeKey']; ?>
" value="<?php echo $this->_tpl_vars['localeKey']; ?>
" /> <label for="installLocale-<?php echo $this->_tpl_vars['localeKey']; ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['localeNames'][$this->_tpl_vars['localeKey']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
 (<?php echo $this->_tpl_vars['localeKey']; ?>
)</label><br />
<?php endforeach; else:  $this->assign('noLocalesToInstall', '1'); ?>
<span class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.languages.noLocalesAvailable"), $this);?>
</span>
<?php endif; unset($_from); ?>

<?php if (! $this->_tpl_vars['noLocalesToInstall']): ?>
<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.languages.installLocales"), $this);?>
" class="button defaultButton" /> <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_tpl_vars['pageUrl']; ?>
/admin'" /></p>
<?php endif; ?>

</form>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>