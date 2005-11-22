<?php /* Smarty version 2.6.10, created on 2005-11-15 14:53:06
         compiled from common/header.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'assign_translate', 'common/header.tpl', 12, false),array('function', 'translate', 'common/header.tpl', 41, false),array('function', 'get_help_id', 'common/header.tpl', 65, false),array('modifier', 'escape', 'common/header.tpl', 63, false),)), $this); ?>

<?php if (! $this->_tpl_vars['pageTitleTranslated']):  echo $this->_plugins['function']['assign_translate'][0][0]->smartyAssignTranslate(array('var' => 'pageTitleTranslated','key' => $this->_tpl_vars['pageTitle']), $this); endif;  if ($this->_tpl_vars['pageCrumbTitle']):  echo $this->_plugins['function']['assign_translate'][0][0]->smartyAssignTranslate(array('var' => 'pageCrumbTitleTranslated','key' => $this->_tpl_vars['pageCrumbTitle']), $this); elseif (! $this->_tpl_vars['pageCrumbTitleTranslated']):  $this->assign('pageCrumbTitleTranslated', $this->_tpl_vars['pageTitleTranslated']);  endif;  echo '<?xml'; ?>
 version="1.0" encoding="UTF-8"<?php echo '?>'; ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->_tpl_vars['defaultCharset']; ?>
" />
	<title><?php echo $this->_tpl_vars['pageTitleTranslated']; ?>
</title>
	<meta name="description" content="<?php echo $this->_tpl_vars['metaSearchDescription']; ?>
" />
	<meta name="keywords" content="<?php echo $this->_tpl_vars['metaSearchKeywords']; ?>
" />
	<?php echo $this->_tpl_vars['metaCustomHeaders']; ?>

	<link rel="stylesheet" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/styles/common.css" type="text/css" />
	<?php $_from = $this->_tpl_vars['stylesheets']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['cssFile']):
?>
	<link rel="stylesheet" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/styles/<?php echo $this->_tpl_vars['cssFile']; ?>
" type="text/css" />
	<?php endforeach; endif; unset($_from); ?>
	<?php if ($this->_tpl_vars['pageStyleSheet']): ?>
	<link rel="stylesheet" href="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo $this->_tpl_vars['pageStyleSheet']['uploadName']; ?>
" type="text/css" />
	<?php endif; ?>
	<script type="text/javascript" src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/js/general.js"></script>
	<?php echo $this->_tpl_vars['additionalHeadData']; ?>

</head>
<body>
<div id="container">

<div id="header">
<h1>
	<img src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/logo.png" width="331" height="52" border="0" alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.harvester2"), $this);?>
" />
</h1>
</div>

<div id="body">

	<div id="sidebar">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/sidebar.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</div>

<div id="main">
<div id="navbar">
	<ul class="menu">
		<li><a href="<?php echo $this->_tpl_vars['pageUrl']; ?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.home"), $this);?>
</a></li>
		<li><a href="<?php echo $this->_tpl_vars['pageUrl']; ?>
/about"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.about"), $this);?>
</a></li>
		<?php if ($this->_tpl_vars['isUserLoggedIn']): ?>
			<li><a href="<?php echo $this->_tpl_vars['pageUrl']; ?>
/admin"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.administration"), $this);?>
</a></li>
		<?php else: ?>
			<li><a href="<?php echo $this->_tpl_vars['pageUrl']; ?>
/login"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.login"), $this);?>
</a></li>
		<?php endif; ?>
		<li><a href="<?php echo $this->_tpl_vars['pageUrl']; ?>
/search"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.search"), $this);?>
</a></li>
		<?php $_from = $this->_tpl_vars['navMenuItems']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['navItem']):
?>
		<li><a href="<?php if ($this->_tpl_vars['navItem']['isAbsolute']):  echo ((is_array($_tmp=$this->_tpl_vars['navItem']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp));  else:  echo $this->_tpl_vars['pageUrl'];  echo ((is_array($_tmp=$this->_tpl_vars['navItem']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp));  endif; ?>"><?php if ($this->_tpl_vars['navItem']['isLiteral']):  echo ((is_array($_tmp=$this->_tpl_vars['navItem']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp));  else:  echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['navItem']['name']), $this); endif; ?></a></li>
		<?php endforeach; endif; unset($_from); ?>
		<li><a href="javascript:openHelp('<?php if ($this->_tpl_vars['helpTopicId']):  echo $this->_plugins['function']['get_help_id'][0][0]->smartyGetHelpId(array('key' => ($this->_tpl_vars['helpTopicId']),'url' => 'true'), $this); else:  echo $this->_tpl_vars['pageUrl']; ?>
/help<?php endif; ?>')"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.help"), $this);?>
</a></li>
	</ul>
</div>

<div id="breadcrumb">
	<a href="<?php echo $this->_tpl_vars['pageUrl']; ?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.home"), $this);?>
</a> &gt;
	<?php $_from = $this->_tpl_vars['pageHierarchy']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['hierarchyLink']):
?>
		<a href="<?php echo $this->_tpl_vars['pageUrl']; ?>
/<?php echo $this->_tpl_vars['hierarchyLink'][0]; ?>
" class="hierarchyLink"><?php if (! $this->_tpl_vars['hierarchyLink'][2]):  echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['hierarchyLink'][1]), $this); else:  echo $this->_tpl_vars['hierarchyLink'][1];  endif; ?></a> &gt;
	<?php endforeach; endif; unset($_from); ?>
	<a href="<?php echo $this->_tpl_vars['currentUrl']; ?>
" class="current"><?php echo $this->_tpl_vars['pageCrumbTitleTranslated']; ?>
</a>
</div>

<h2><?php echo $this->_tpl_vars['pageTitleTranslated']; ?>
</h2>

<div id="content">