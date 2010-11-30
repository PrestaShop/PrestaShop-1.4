<?php /* Smarty version Smarty-3.0.5, created on 2010-11-30 10:10:22
         compiled from "C:\Apache\htdocs\jquery/modules/blocknewproducts/blocknewproducts.tpl" */ ?>
<?php /*%%SmartyHeaderCode:134004cf4bf7e311b27-97427291%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ffaadfbe530ff09b324c8d604ad3467f9c426399' => 
    array (
      0 => 'C:\\Apache\\htdocs\\jquery/modules/blocknewproducts/blocknewproducts.tpl',
      1 => 1290763379,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '134004cf4bf7e311b27-97427291',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!is_callable('smarty_modifier_escape')) include 'C:\Apache\htdocs\jquery\tools\smarty\plugins\modifier.escape.php';
?><!-- MODULE Block new products -->
<div id="new-products_block_right" class="block products_block">
	<h4><a href="<?php echo $_smarty_tpl->getVariable('link')->value->getPageLink('new-products.php');?>
" title="<?php echo smartyTranslate(array('s'=>'New products','mod'=>'blocknewproducts'),$_smarty_tpl);?>
"><?php echo smartyTranslate(array('s'=>'New products','mod'=>'blocknewproducts'),$_smarty_tpl);?>
</a></h4>
	<div class="block_content">
	<?php if ($_smarty_tpl->getVariable('new_products')->value!==false){?>
		<ul class="product_images clearfix">
		<?php  $_smarty_tpl->tpl_vars['product'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('new_products')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['product']->index=-1;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['newProducts']['index']=-1;
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['product']->key => $_smarty_tpl->tpl_vars['product']->value){
 $_smarty_tpl->tpl_vars['product']->index++;
 $_smarty_tpl->tpl_vars['product']->first = $_smarty_tpl->tpl_vars['product']->index === 0;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['newProducts']['first'] = $_smarty_tpl->tpl_vars['product']->first;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['newProducts']['index']++;
?>
			<?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['newProducts']['index']<2){?>
				<li<?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['newProducts']['first']){?> class="first"<?php }?>><a href="<?php echo $_smarty_tpl->tpl_vars['product']->value['link'];?>
" title="<?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['product']->value['legend'],'html','UTF-8');?>
"><img src="<?php echo $_smarty_tpl->getVariable('link')->value->getImageLink($_smarty_tpl->tpl_vars['product']->value['link_rewrite'],$_smarty_tpl->tpl_vars['product']->value['id_image'],'medium');?>
" height="<?php echo $_smarty_tpl->getVariable('mediumSize')->value['height'];?>
" width="<?php echo $_smarty_tpl->getVariable('mediumSize')->value['width'];?>
" alt="<?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['product']->value['legend'],'html','UTF-8');?>
" /></a></li>
			<?php }?>
		<?php }} ?>
		</ul>
		<dl class="products">
		<?php  $_smarty_tpl->tpl_vars['newproduct'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('new_products')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['newproduct']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['newproduct']->iteration=0;
 $_smarty_tpl->tpl_vars['newproduct']->index=-1;
if ($_smarty_tpl->tpl_vars['newproduct']->total > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['newproduct']->key => $_smarty_tpl->tpl_vars['newproduct']->value){
 $_smarty_tpl->tpl_vars['newproduct']->iteration++;
 $_smarty_tpl->tpl_vars['newproduct']->index++;
 $_smarty_tpl->tpl_vars['newproduct']->first = $_smarty_tpl->tpl_vars['newproduct']->index === 0;
 $_smarty_tpl->tpl_vars['newproduct']->last = $_smarty_tpl->tpl_vars['newproduct']->iteration === $_smarty_tpl->tpl_vars['newproduct']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['myLoop']['first'] = $_smarty_tpl->tpl_vars['newproduct']->first;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['myLoop']['last'] = $_smarty_tpl->tpl_vars['newproduct']->last;
?>
			<dt class="<?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['myLoop']['first']){?>first_item<?php }elseif($_smarty_tpl->getVariable('smarty')->value['foreach']['myLoop']['last']){?>last_item<?php }else{ ?>item<?php }?>"><a href="<?php echo $_smarty_tpl->tpl_vars['newproduct']->value['link'];?>
" title="<?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['newproduct']->value['name'],'html','UTF-8');?>
"><?php echo smarty_modifier_escape(preg_replace('!<[^>]*?>!', ' ', $_smarty_tpl->tpl_vars['newproduct']->value['name']),'html','UTF-8');?>
</a></dt>
			<?php if ($_smarty_tpl->tpl_vars['newproduct']->value['description_short']){?><dd class="<?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['myLoop']['first']){?>first_item<?php }elseif($_smarty_tpl->getVariable('smarty')->value['foreach']['myLoop']['last']){?>last_item<?php }else{ ?>item<?php }?>"><a href="<?php echo $_smarty_tpl->tpl_vars['newproduct']->value['link'];?>
"><?php echo smarty_modifier_truncate(strip_tags($_smarty_tpl->tpl_vars['newproduct']->value['description_short']),50,'...');?>
</a>&nbsp;<a href="<?php echo $_smarty_tpl->tpl_vars['newproduct']->value['link'];?>
"><img alt=">>" src="<?php echo $_smarty_tpl->getVariable('img_dir')->value;?>
bullet.gif" width="10" height="7"/></a></dd><?php }?>
		<?php }} ?>
		</dl>
		<p><a href="<?php echo $_smarty_tpl->getVariable('link')->value->getPageLink('new-products.php');?>
" title="<?php echo smartyTranslate(array('s'=>'All new products','mod'=>'blocknewproducts'),$_smarty_tpl);?>
" class="button_large"><?php echo smartyTranslate(array('s'=>'All new products','mod'=>'blocknewproducts'),$_smarty_tpl);?>
</a></p>
	<?php }else{ ?>
		<p><?php echo smartyTranslate(array('s'=>'No new products at this time','mod'=>'blocknewproducts'),$_smarty_tpl);?>
</p>
	<?php }?>
	</div>
</div>
<!-- /MODULE Block new products -->
