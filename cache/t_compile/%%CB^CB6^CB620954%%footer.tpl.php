<?php /* Smarty version 2.6.10, created on 2005-11-15 14:53:06
         compiled from common/footer.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'nl2br', 'common/footer.tpl', 17, false),array('modifier', 'string_format', 'common/footer.tpl', 22, false),array('function', 'get_debug_info', 'common/footer.tpl', 19, false),array('function', 'translate', 'common/footer.tpl', 22, false),)), $this); ?>

</div>
</div>
</div>

<div id="footer">
<?php if ($this->_tpl_vars['footer']):  echo ((is_array($_tmp=$this->_tpl_vars['footer'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp));  endif; ?>

<?php echo $this->_plugins['function']['get_debug_info'][0][0]->smartyGetDebugInfo(array(), $this);?>

<?php if ($this->_tpl_vars['enableDebugStats']): ?>
<div class="debugStats">
	<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "debug.executionTime"), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['debugExecutionTime'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.4f") : smarty_modifier_string_format($_tmp, "%.4f")); ?>
s<br />
	<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "debug.databaseQueries"), $this);?>
: <?php echo $this->_tpl_vars['debugNumDatabaseQueries']; ?>
<br/>
	<?php if ($this->_tpl_vars['debugNotes']): ?>
		<strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "debug.notes"), $this);?>
</strong><br/>
		<?php $_from = $this->_tpl_vars['debugNotes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['note']):
?>
			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['note'][0],'params' => $this->_tpl_vars['note'][1]), $this);?>
<br/>
		<?php endforeach; endif; unset($_from); ?>
	<?php endif; ?>
</div>
<?php endif; ?>
</div>

</div>
</body>
</html>