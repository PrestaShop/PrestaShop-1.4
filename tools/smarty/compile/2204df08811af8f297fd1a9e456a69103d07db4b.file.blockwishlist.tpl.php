<?php /* Smarty version Smarty-3.0.5, created on 2010-11-30 10:10:22
         compiled from "C:\Apache\htdocs\jquery/modules/blockwishlist/blockwishlist.tpl" */ ?>
<?php /*%%SmartyHeaderCode:158764cf4bf7e729fc3-30453477%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '2204df08811af8f297fd1a9e456a69103d07db4b' => 
    array (
      0 => 'C:\\Apache\\htdocs\\jquery/modules/blockwishlist/blockwishlist.tpl',
      1 => 1290763360,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '158764cf4bf7e729fc3-30453477',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!is_callable('smarty_modifier_escape')) include 'C:\Apache\htdocs\jquery\tools\smarty\plugins\modifier.escape.php';
?><div id="wishlist_block" class="block account">
	<h4>
		<a href="<?php echo $_smarty_tpl->getVariable('base_dir_ssl')->value;?>
/modules/blockwishlist/mywishlist.php"><?php echo smartyTranslate(array('s'=>'Wishlist','mod'=>'blockwishlist'),$_smarty_tpl);?>
</a>
	</h4>
	<div class="block_content">
		<div id="wishlist_block_list" class="expanded">
		<?php if ($_smarty_tpl->getVariable('wishlist_products')->value){?>
			<dl class="products">
			<?php  $_smarty_tpl->tpl_vars['product'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('wishlist_products')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['product']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['product']->iteration=0;
 $_smarty_tpl->tpl_vars['product']->index=-1;
if ($_smarty_tpl->tpl_vars['product']->total > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['product']->key => $_smarty_tpl->tpl_vars['product']->value){
 $_smarty_tpl->tpl_vars['product']->iteration++;
 $_smarty_tpl->tpl_vars['product']->index++;
 $_smarty_tpl->tpl_vars['product']->first = $_smarty_tpl->tpl_vars['product']->index === 0;
 $_smarty_tpl->tpl_vars['product']->last = $_smarty_tpl->tpl_vars['product']->iteration === $_smarty_tpl->tpl_vars['product']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['i']['first'] = $_smarty_tpl->tpl_vars['product']->first;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['i']['last'] = $_smarty_tpl->tpl_vars['product']->last;
?>
				<dt class="<?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['i']['first']){?>first_item<?php }elseif($_smarty_tpl->getVariable('smarty')->value['foreach']['i']['last']){?>last_item<?php }else{ ?>item<?php }?>">
					<span class="quantity-formated"><span class="quantity"><?php echo intval($_smarty_tpl->tpl_vars['product']->value['quantity']);?>
</span>x</span>
					<a class="cart_block_product_name"
					href="<?php echo $_smarty_tpl->getVariable('link')->value->getProductLink($_smarty_tpl->tpl_vars['product']->value['id_product'],$_smarty_tpl->tpl_vars['product']->value['link_rewrite'],$_smarty_tpl->tpl_vars['product']->value['category_rewrite']);?>
" title="<?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['product']->value['name'],'htmlall','UTF-8');?>
"><?php echo smarty_modifier_escape(smarty_modifier_truncate($_smarty_tpl->tpl_vars['product']->value['name'],30,'...'),'htmlall','UTF-8');?>
</a>
					<a class="ajax_cart_block_remove_link" href="javascript:;" onclick="javascript:WishlistCart('wishlist_block_list', 'delete', '<?php echo $_smarty_tpl->tpl_vars['product']->value['id_product'];?>
', <?php echo $_smarty_tpl->tpl_vars['product']->value['id_product_attribute'];?>
, '0', '<?php echo $_smarty_tpl->getVariable('token')->value;?>
');" title="<?php echo smartyTranslate(array('s'=>'remove this product from my wishlist','mod'=>'blockwishlist'),$_smarty_tpl);?>
"><img src="<?php echo $_smarty_tpl->getVariable('img_dir')->value;?>
icon/delete.gif" alt="<?php echo smartyTranslate(array('s'=>'Delete'),$_smarty_tpl);?>
" class="icon" /></a>
				</dt>
				<?php if (isset($_smarty_tpl->tpl_vars['product']->value['attributes_small'])){?>
				<dd class="<?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['myLoop']['first']){?>first_item<?php }elseif($_smarty_tpl->getVariable('smarty')->value['foreach']['myLoop']['last']){?>last_item<?php }else{ ?>item<?php }?>">
					<a href="<?php echo $_smarty_tpl->getVariable('link')->value->getProductLink($_smarty_tpl->tpl_vars['product']->value['id_product'],$_smarty_tpl->tpl_vars['product']->value['link_rewrite'],$_smarty_tpl->tpl_vars['product']->value['category_rewrite']);?>
" title="<?php echo smartyTranslate(array('s'=>'Product detail'),$_smarty_tpl);?>
"><?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['product']->value['attributes_small'],'htmlall','UTF-8');?>
</a>
				</dd>
				<?php }?>
			<?php }} ?>
			</dl>
		<?php }else{ ?>
			<dl class="products">
				<dt><?php echo smartyTranslate(array('s'=>'No products','mod'=>'blockwishlist'),$_smarty_tpl);?>
</dt>
			</dl>
		<?php }?>
		</div>
		<p class="align_center">
		<?php if ($_smarty_tpl->getVariable('wishlists')->value){?>
			<select name="wishlists" id="wishlists" onchange="WishlistChangeDefault('wishlist_block_list', $('#wishlists').val());">
			<?php  $_smarty_tpl->tpl_vars['wishlist'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('wishlists')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['wishlist']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['wishlist']->iteration=0;
 $_smarty_tpl->tpl_vars['wishlist']->index=-1;
if ($_smarty_tpl->tpl_vars['wishlist']->total > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['wishlist']->key => $_smarty_tpl->tpl_vars['wishlist']->value){
 $_smarty_tpl->tpl_vars['wishlist']->iteration++;
 $_smarty_tpl->tpl_vars['wishlist']->index++;
 $_smarty_tpl->tpl_vars['wishlist']->first = $_smarty_tpl->tpl_vars['wishlist']->index === 0;
 $_smarty_tpl->tpl_vars['wishlist']->last = $_smarty_tpl->tpl_vars['wishlist']->iteration === $_smarty_tpl->tpl_vars['wishlist']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['i']['first'] = $_smarty_tpl->tpl_vars['wishlist']->first;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['i']['last'] = $_smarty_tpl->tpl_vars['wishlist']->last;
?>
				<option value="<?php echo $_smarty_tpl->tpl_vars['wishlist']->value['id_wishlist'];?>
"<?php if ($_smarty_tpl->getVariable('id_wishlist')->value==$_smarty_tpl->tpl_vars['wishlist']->value['id_wishlist']||($_smarty_tpl->getVariable('id_wishlist')->value==false&&$_smarty_tpl->getVariable('smarty')->value['foreach']['i']['first'])){?> selected="selected"<?php }?>><?php echo smarty_modifier_escape(smarty_modifier_truncate($_smarty_tpl->tpl_vars['wishlist']->value['name'],22,'...'),'htmlall','UTF-8');?>
</option>
			<?php }} ?>
			</select>
		<?php }?>
			<a href="<?php echo $_smarty_tpl->getVariable('base_dir_ssl')->value;?>
modules/blockwishlist/mywishlist.php" class="exclusive" title="<?php echo smartyTranslate(array('s'=>'My wishlists','mod'=>'blockwishlist'),$_smarty_tpl);?>
"><?php echo smartyTranslate(array('s'=>'My wishlists','mod'=>'blockwishlist'),$_smarty_tpl);?>
</a>
		</p>
	</div>
</div>
