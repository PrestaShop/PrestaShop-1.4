<?php /* Smarty version Smarty-3.0.5, created on 2010-11-30 10:10:20
         compiled from "C:\Apache\htdocs\jquery/modules/blockadvertising/blockadvertising.tpl" */ ?>
<?php /*%%SmartyHeaderCode:200334cf4bf7c2c6911-23711501%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '7b2b8680dc9b75699a0786362c1efee403041f93' => 
    array (
      0 => 'C:\\Apache\\htdocs\\jquery/modules/blockadvertising/blockadvertising.tpl',
      1 => 1290763380,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '200334cf4bf7c2c6911-23711501',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<!-- MODULE Block advertising -->
<div class="advertising_block">
	<a href="<?php echo $_smarty_tpl->getVariable('adv_link')->value;?>
" title="<?php echo smartyTranslate(array('s'=>'Advertising','mod'=>'blockadvertising'),$_smarty_tpl);?>
"><img src="<?php echo $_smarty_tpl->getVariable('image')->value;?>
" alt="<?php echo smartyTranslate(array('s'=>'Advertising','mod'=>'blockadvertising'),$_smarty_tpl);?>
" width="155"  height="163" /></a>
</div>
<!-- /MODULE Block advertising -->
