<?php /* Smarty version 2.6.10, created on 2005-11-15 14:53:06
         compiled from common/sidebar.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'common/sidebar.tpl', 14, false),array('function', 'html_options', 'common/sidebar.tpl', 36, false),array('function', 'html_options_translate', 'common/sidebar.tpl', 53, false),array('modifier', 'escape', 'common/sidebar.tpl', 16, false),)), $this); ?>

<?php if ($this->_tpl_vars['isUserLoggedIn']): ?>
	<div class="block">
		<span class="blockTitle"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.administration"), $this);?>
</span>
		<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.loggedInAs"), $this);?>
<br />
		<strong><?php echo ((is_array($_tmp=$this->_tpl_vars['loggedInUsername'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</strong>

		<ul>
			<li><a href="<?php echo $this->_tpl_vars['pageUrl']; ?>
/admin"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.administration"), $this);?>
</a></li>
			<li><a href="<?php echo $this->_tpl_vars['pageUrl']; ?>
/login/signOut"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.logout"), $this);?>
</a></li>
		<?php if ($this->_tpl_vars['userSession']->getSessionVar('signedInAs')): ?>
			<li><a href="<?php echo $this->_tpl_vars['pageUrl']; ?>
/manager/signOutAsUser"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.people.signOutAsUser"), $this);?>
</a></li>
		<?php endif; ?>
		</ul>
	</div>
<?php endif; ?>

<?php if ($this->_tpl_vars['sidebarTemplate']): ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['sidebarTemplate'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  endif; ?>

<?php if ($this->_tpl_vars['enableLanguageToggle']): ?>
	<div class="block">
		<span class="blockTitle"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.language"), $this);?>
</span>
		<form action="#">
			<select size="1" onchange="location.href=<?php if ($this->_tpl_vars['languageToggleNoUser']): ?>'<?php echo $this->_tpl_vars['currentUrl'];  if (strstr ( $this->_tpl_vars['currentUrl'] , '?' )): ?>&<?php else: ?>?<?php endif; ?>setLocale='+this.options[this.selectedIndex].value<?php else: ?>'<?php echo $this->_tpl_vars['pageUrl']; ?>
/index/setLocale/'+this.options[this.selectedIndex].value+'?source=<?php echo ((is_array($_tmp=$_SERVER['REQUEST_URI'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : smarty_modifier_escape($_tmp, 'javascript')); ?>
'<?php endif; ?>" class="selectMenu"><?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['languageToggleLocales'],'selected' => $this->_tpl_vars['currentLocale']), $this);?>
</select>
		</form>
	</div>
<?php endif; ?>

<div class="block">
	<span class="blockTitle"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.content"), $this);?>
</span>

	<span class="blockSubtitle"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.search"), $this);?>
</span>
	<form method="get" action="<?php echo $this->_tpl_vars['pageUrl']; ?>
/search/results">
		<table>
			<tr>
				<td><input type="text" id="query" name="query" size="15" maxlength="255" value="" class="textField" /></td>
			</tr>
			<tr>
				<td><select name="searchField" size="1" class="selectMenu">
					<option value="FIXME">FIXME</option>
					<?php echo $this->_plugins['function']['html_options_translate'][0][0]->smartyHtmlOptionsTranslate(array('options' => $this->_tpl_vars['articleSearchByOptions']), $this);?>

				</select></td>
			</tr>
			<tr>
				<td><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.search"), $this);?>
" class="button" /></td>
			</tr>
		</table>
	</form>
</div>
