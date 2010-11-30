<?php /* Smarty version Smarty-3.0.5, created on 2010-11-30 10:10:19
         compiled from "C:\Apache\htdocs\jquery/modules/blockcategories/category-tree-branch.tpl" */ ?>
<?php /*%%SmartyHeaderCode:191414cf4bf7bd68477-71873637%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '6d62150eb0963e72cb880da551585d30c7387ac7' => 
    array (
      0 => 'C:\\Apache\\htdocs\\jquery/modules/blockcategories/category-tree-branch.tpl',
      1 => 1291059068,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '191414cf4bf7bd68477-71873637',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!is_callable('smarty_modifier_escape')) include 'C:\Apache\htdocs\jquery\tools\smarty\plugins\modifier.escape.php';
?><li <?php if (isset($_smarty_tpl->getVariable('last',null,true,false)->value)&&$_smarty_tpl->getVariable('last')->value=='true'){?>class="last"<?php }?>>
	<a href="<?php echo smarty_modifier_escape($_smarty_tpl->getVariable('node')->value['link'],'html','UTF-8');?>
" <?php if (isset($_smarty_tpl->getVariable('currentCategoryId',null,true,false)->value)&&($_smarty_tpl->getVariable('node')->value['id']==$_smarty_tpl->getVariable('currentCategoryId')->value)){?>class="selected"<?php }?> title="<?php echo smarty_modifier_escape($_smarty_tpl->getVariable('node')->value['desc'],'html','UTF-8');?>
"><?php echo smarty_modifier_escape($_smarty_tpl->getVariable('node')->value['name'],'html','UTF-8');?>
</a>
	<?php if (count($_smarty_tpl->getVariable('node')->value['children'])>0){?>
		<ul>
		<?php  $_smarty_tpl->tpl_vars['child'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('node')->value['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['child']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['child']->iteration=0;
if ($_smarty_tpl->tpl_vars['child']->total > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['child']->key => $_smarty_tpl->tpl_vars['child']->value){
 $_smarty_tpl->tpl_vars['child']->iteration++;
 $_smarty_tpl->tpl_vars['child']->last = $_smarty_tpl->tpl_vars['child']->iteration === $_smarty_tpl->tpl_vars['child']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['categoryTreeBranch']['last'] = $_smarty_tpl->tpl_vars['child']->last;
?>
			<?php if (isset($_smarty_tpl->getVariable('smarty',null,true,false)->value['foreach']['categoryTreeBranch'])&&$_smarty_tpl->getVariable('smarty')->value['foreach']['categoryTreeBranch']['last']){?>
				<?php $_template = new Smarty_Internal_Template(($_smarty_tpl->getVariable('branche_tpl_path')->value), $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null);
$_template->assign('node',$_smarty_tpl->tpl_vars['child']->value);$_template->assign('last','true'); echo $_template->getRenderedTemplate();?><?php $_template->updateParentVariables(0);?><?php unset($_template);?>
			<?php }else{ ?>
				<?php $_template = new Smarty_Internal_Template(($_smarty_tpl->getVariable('branche_tpl_path')->value), $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null);
$_template->assign('node',$_smarty_tpl->tpl_vars['child']->value);$_template->assign('last','false'); echo $_template->getRenderedTemplate();?><?php $_template->updateParentVariables(0);?><?php unset($_template);?>
			<?php }?>
		<?php }} ?>
		</ul>
	<?php }?>
</li>