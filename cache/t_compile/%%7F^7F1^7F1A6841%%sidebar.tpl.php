<?php /* Smarty version 2.6.10, created on 2005-11-15 16:40:48
         compiled from index/sidebar.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'index/sidebar.tpl', 13, false),)), $this); ?>

<div class="block">
	<span class="blockTitle"><img src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/stats.png" align="right" width="25" height="25"/><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "sidebar.harvesterStats"), $this);?>
</span>
	<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "sidebar.harvesterStats.description"), $this);?>

</div>

<div class="block">
	<span class="blockTitle"><img src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/add.png" align="right" width="25" height="25"/><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "sidebar.addYourArchive"), $this);?>
</span>
	<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "sidebar.addYourArchive.description",'addUrl' => ($this->_tpl_vars['pageUrl'])."/add"), $this);?>

</div>