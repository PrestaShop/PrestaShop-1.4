<?php /* Smarty version Smarty-3.0.5, created on 2010-11-30 10:10:21
         compiled from "C:\Apache\htdocs\jquery/modules/blockcart/blockcart.tpl" */ ?>
<?php /*%%SmartyHeaderCode:54354cf4bf7dd7b704-76160254%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'b2819860c5ddcdccb7d7a7b6f29b4ce3fe08ed41' => 
    array (
      0 => 'C:\\Apache\\htdocs\\jquery/modules/blockcart/blockcart.tpl',
      1 => 1291071630,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '54354cf4bf7dd7b704-76160254',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!is_callable('smarty_modifier_escape')) include 'C:\Apache\htdocs\jquery\tools\smarty\plugins\modifier.escape.php';
if (!is_callable('smarty_modifier_replace')) include 'C:\Apache\htdocs\jquery\tools\smarty\plugins\modifier.replace.php';
?>

<?php if ($_smarty_tpl->getVariable('ajax_allowed')->value){?>
<script type="text/javascript">
var CUSTOMIZE_TEXTFIELD = <?php echo $_smarty_tpl->getVariable('CUSTOMIZE_TEXTFIELD')->value;?>
;
var customizationIdMessage = '<?php echo smartyTranslate(array('s'=>'Customization #','mod'=>'blockcart','js'=>1),$_smarty_tpl);?>
';
var removingLinkText = '<?php echo smartyTranslate(array('s'=>'remove this product from my cart','mod'=>'blockcart','js'=>1),$_smarty_tpl);?>
';
</script>
<?php }?>

<!-- MODULE Block cart -->
<div id="cart_block" class="block exclusive">
	<h4>
		<a href="<?php echo $_smarty_tpl->getVariable('link')->value->getPageLink(($_smarty_tpl->getVariable('order_process')->value).".php",true);?>
"><?php echo smartyTranslate(array('s'=>'Cart','mod'=>'blockcart'),$_smarty_tpl);?>
</a>
		<?php if ($_smarty_tpl->getVariable('ajax_allowed')->value){?>
		<span id="block_cart_expand" <?php if (isset($_smarty_tpl->getVariable('colapseExpandStatus',null,true,false)->value)&&$_smarty_tpl->getVariable('colapseExpandStatus')->value=='expanded'){?>class="hidden"<?php }?>>&nbsp;</span>
		<span id="block_cart_collapse" <?php if (isset($_smarty_tpl->getVariable('colapseExpandStatus',null,true,false)->value)&&$_smarty_tpl->getVariable('colapseExpandStatus')->value=='collapsed'||!isset($_smarty_tpl->getVariable('colapseExpandStatus',null,true,false)->value)){?>class="hidden"<?php }?>>&nbsp;</span>
		<?php }?>
	</h4>
	<div class="block_content">
	<!-- block summary -->
	<div id="cart_block_summary" class="<?php if (isset($_smarty_tpl->getVariable('colapseExpandStatus',null,true,false)->value)&&$_smarty_tpl->getVariable('colapseExpandStatus')->value=='expanded'||!$_smarty_tpl->getVariable('ajax_allowed')->value){?>collapsed<?php }else{ ?>expanded<?php }?>">
		<?php if ($_smarty_tpl->getVariable('cart_qties')->value>0){?><span class="ajax_cart_quantity"><?php echo $_smarty_tpl->getVariable('cart_qties')->value;?>
</span><?php }?>
		<span class="ajax_cart_product_txt_s<?php if ($_smarty_tpl->getVariable('cart_qties')->value<2){?> hidden<?php }?>"><?php echo smartyTranslate(array('s'=>'products','mod'=>'blockcart'),$_smarty_tpl);?>
</span>
		<span class="ajax_cart_product_txt<?php if ($_smarty_tpl->getVariable('cart_qties')->value!=1){?> hidden<?php }?>"><?php echo smartyTranslate(array('s'=>'product','mod'=>'blockcart'),$_smarty_tpl);?>
</span>
		<?php if ($_smarty_tpl->getVariable('cart_qties')->value>0){?><span class="ajax_cart_total"><?php if ($_smarty_tpl->getVariable('priceDisplay')->value==1){?><?php echo Product::convertPrice(array('price'=>$_smarty_tpl->getVariable('cart')->value->getOrderTotal(false)),$_smarty_tpl);?>
<?php }else{ ?><?php echo Product::convertPrice(array('price'=>$_smarty_tpl->getVariable('cart')->value->getOrderTotal(true)),$_smarty_tpl);?>
<?php }?></span><?php }?>
		<?php if ($_smarty_tpl->getVariable('cart_qties')->value==0){?><span class="ajax_cart_no_product"><?php if ($_smarty_tpl->getVariable('cart_qties')->value==0){?><?php echo smartyTranslate(array('s'=>'(empty)','mod'=>'blockcart'),$_smarty_tpl);?>
<?php }?></span><?php }?>
	</div>
	<!-- block list of products -->
	<div id="cart_block_list" class="<?php if (isset($_smarty_tpl->getVariable('colapseExpandStatus',null,true,false)->value)&&$_smarty_tpl->getVariable('colapseExpandStatus')->value=='expanded'||!$_smarty_tpl->getVariable('ajax_allowed')->value){?>expanded<?php }else{ ?>collapsed<?php }?>">
	<?php if ($_smarty_tpl->getVariable('products')->value){?>
		<dl class="products">
		<?php  $_smarty_tpl->tpl_vars['product'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('products')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['product']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['product']->iteration=0;
 $_smarty_tpl->tpl_vars['product']->index=-1;
if ($_smarty_tpl->tpl_vars['product']->total > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['product']->key => $_smarty_tpl->tpl_vars['product']->value){
 $_smarty_tpl->tpl_vars['product']->iteration++;
 $_smarty_tpl->tpl_vars['product']->index++;
 $_smarty_tpl->tpl_vars['product']->first = $_smarty_tpl->tpl_vars['product']->index === 0;
 $_smarty_tpl->tpl_vars['product']->last = $_smarty_tpl->tpl_vars['product']->iteration === $_smarty_tpl->tpl_vars['product']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['myLoop']['first'] = $_smarty_tpl->tpl_vars['product']->first;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['myLoop']['last'] = $_smarty_tpl->tpl_vars['product']->last;
?>
			<?php $_smarty_tpl->tpl_vars['productId'] = new Smarty_variable($_smarty_tpl->tpl_vars['product']->value['id_product'], null, null);?>
			<?php $_smarty_tpl->tpl_vars['productAttributeId'] = new Smarty_variable($_smarty_tpl->tpl_vars['product']->value['id_product_attribute'], null, null);?>
			<dt id="cart_block_product_<?php echo $_smarty_tpl->tpl_vars['product']->value['id_product'];?>
<?php if ($_smarty_tpl->tpl_vars['product']->value['id_product_attribute']){?>_<?php echo $_smarty_tpl->tpl_vars['product']->value['id_product_attribute'];?>
<?php }?>" class="<?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['myLoop']['first']){?>first_item<?php }elseif($_smarty_tpl->getVariable('smarty')->value['foreach']['myLoop']['last']){?>last_item<?php }else{ ?>item<?php }?>">
				<span class="quantity-formated"><span class="quantity"><?php echo $_smarty_tpl->tpl_vars['product']->value['cart_quantity'];?>
</span>x</span>
				<a class="cart_block_product_name" href="<?php echo $_smarty_tpl->getVariable('link')->value->getProductLink($_smarty_tpl->tpl_vars['product']->value['id_product'],$_smarty_tpl->tpl_vars['product']->value['link_rewrite'],$_smarty_tpl->tpl_vars['product']->value['category']);?>
" title="<?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['product']->value['name'],'html','UTF-8');?>
"><?php echo smartyTruncate(array('text'=>$_smarty_tpl->tpl_vars['product']->value['name'],'length'=>'10','encode'=>'true'),$_smarty_tpl);?>
</a>
				<span class="remove_link"><?php if (!isset($_smarty_tpl->getVariable('customizedDatas',null,true,false)->value[$_smarty_tpl->getVariable('productId',null,true,false)->value][$_smarty_tpl->getVariable('productAttributeId',null,true,false)->value])){?><a class="ajax_cart_block_remove_link" href="<?php echo $_smarty_tpl->getVariable('link')->value->getPageLink('cart.php');?>
?delete&amp;id_product=<?php echo $_smarty_tpl->tpl_vars['product']->value['id_product'];?>
&amp;ipa=<?php echo $_smarty_tpl->tpl_vars['product']->value['id_product_attribute'];?>
&amp;token=<?php echo $_smarty_tpl->getVariable('static_token')->value;?>
" title="<?php echo smartyTranslate(array('s'=>'remove this product from my cart','mod'=>'blockcart'),$_smarty_tpl);?>
">&nbsp;</a><?php }?></span>
				<span class="price"><?php if ($_smarty_tpl->getVariable('priceDisplay')->value==@PS_TAX_EXC){?><?php echo Product::displayWtPrice(array('p'=>($_smarty_tpl->tpl_vars['product']->value['total'])),$_smarty_tpl);?>
<?php }else{ ?><?php echo Product::displayWtPrice(array('p'=>($_smarty_tpl->tpl_vars['product']->value['total_wt'])),$_smarty_tpl);?>
<?php }?></span>
			</dt>
			<?php if (isset($_smarty_tpl->tpl_vars['product']->value['attributes_small'])){?>
			<dd id="cart_block_combination_of_<?php echo $_smarty_tpl->tpl_vars['product']->value['id_product'];?>
<?php if ($_smarty_tpl->tpl_vars['product']->value['id_product_attribute']){?>_<?php echo $_smarty_tpl->tpl_vars['product']->value['id_product_attribute'];?>
<?php }?>" class="<?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['myLoop']['first']){?>first_item<?php }elseif($_smarty_tpl->getVariable('smarty')->value['foreach']['myLoop']['last']){?>last_item<?php }else{ ?>item<?php }?>">
				<a href="<?php echo $_smarty_tpl->getVariable('link')->value->getProductLink($_smarty_tpl->tpl_vars['product']->value['id_product'],$_smarty_tpl->tpl_vars['product']->value['link_rewrite'],$_smarty_tpl->tpl_vars['product']->value['category']);?>
" title="<?php echo smartyTranslate(array('s'=>'Product detail'),$_smarty_tpl);?>
"><?php echo $_smarty_tpl->tpl_vars['product']->value['attributes_small'];?>
</a>
			<?php }?>

			<!-- Customizable datas -->
			<?php if (isset($_smarty_tpl->getVariable('customizedDatas',null,true,false)->value[$_smarty_tpl->getVariable('productId',null,true,false)->value][$_smarty_tpl->getVariable('productAttributeId',null,true,false)->value])){?>
				<?php if (!isset($_smarty_tpl->tpl_vars['product']->value['attributes_small'])){?><dd id="cart_block_combination_of_<?php echo $_smarty_tpl->tpl_vars['product']->value['id_product'];?>
<?php if ($_smarty_tpl->tpl_vars['product']->value['id_product_attribute']){?>_<?php echo $_smarty_tpl->tpl_vars['product']->value['id_product_attribute'];?>
<?php }?>" class="<?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['myLoop']['first']){?>first_item<?php }elseif($_smarty_tpl->getVariable('smarty')->value['foreach']['myLoop']['last']){?>last_item<?php }else{ ?>item<?php }?>"><?php }?>
				<ul class="cart_block_customizations" id="customization_<?php echo $_smarty_tpl->getVariable('productId')->value;?>
_<?php echo $_smarty_tpl->getVariable('productAttributeId')->value;?>
">
					<?php  $_smarty_tpl->tpl_vars['customization'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['id_customization'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('customizedDatas')->value[$_smarty_tpl->getVariable('productId')->value][$_smarty_tpl->getVariable('productAttributeId')->value]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['customization']->key => $_smarty_tpl->tpl_vars['customization']->value){
 $_smarty_tpl->tpl_vars['id_customization']->value = $_smarty_tpl->tpl_vars['customization']->key;
?>
						<li name="customization">
							<div class="deleteCustomizableProduct" id="deleteCustomizableProduct_<?php echo intval($_smarty_tpl->tpl_vars['id_customization']->value);?>
_<?php echo intval($_smarty_tpl->tpl_vars['product']->value['id_product']);?>
_<?php echo intval($_smarty_tpl->tpl_vars['product']->value['id_product_attribute']);?>
"><a class="ajax_cart_block_remove_link" href="<?php echo $_smarty_tpl->getVariable('link')->value->getPageLink('cart.php');?>
?delete&amp;id_product=<?php echo intval($_smarty_tpl->tpl_vars['product']->value['id_product']);?>
&amp;ipa=<?php echo intval($_smarty_tpl->tpl_vars['product']->value['id_product_attribute']);?>
&amp;id_customization=<?php echo $_smarty_tpl->tpl_vars['id_customization']->value;?>
&amp;token=<?php echo $_smarty_tpl->getVariable('static_token')->value;?>
"> </a></div>
							<span class="quantity-formated"><span class="quantity"><?php echo $_smarty_tpl->tpl_vars['customization']->value['quantity'];?>
</span>x</span><?php if (isset($_smarty_tpl->tpl_vars['customization']->value['datas'][$_smarty_tpl->getVariable('CUSTOMIZE_TEXTFIELD',null,true,false)->value][0])){?><?php echo smartyTruncate(array('text'=>smarty_modifier_replace($_smarty_tpl->tpl_vars['customization']->value['datas'][$_smarty_tpl->getVariable('CUSTOMIZE_TEXTFIELD')->value][0]['value'],"<br />"," "),'length'=>'28','encode'=>'true'),$_smarty_tpl);?>

							<?php }else{ ?>
							<?php echo smartyTranslate(array('s'=>'Customization #','mod'=>'blockcart'),$_smarty_tpl);?>
<?php echo intval($_smarty_tpl->tpl_vars['id_customization']->value);?>
<?php echo smartyTranslate(array('s'=>':','mod'=>'blockcart'),$_smarty_tpl);?>

							<?php }?>
						</li>
					<?php }} ?>
				</ul>
				<?php if (!isset($_smarty_tpl->tpl_vars['product']->value['attributes_small'])){?></dd><?php }?>
			<?php }?>

			<?php if (isset($_smarty_tpl->tpl_vars['product']->value['attributes_small'])){?></dd><?php }?>

		<?php }} ?>
		</dl>
	<?php }?>
		<p <?php if ($_smarty_tpl->getVariable('products')->value){?>class="hidden"<?php }?> id="cart_block_no_products"><?php echo smartyTranslate(array('s'=>'No products','mod'=>'blockcart'),$_smarty_tpl);?>
</p>
		
		<?php if (count($_smarty_tpl->getVariable('discounts')->value)>0){?><table id="vouchers">
			<tbody>
			<?php  $_smarty_tpl->tpl_vars['discount'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('discounts')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['discount']->key => $_smarty_tpl->tpl_vars['discount']->value){
?>
				<tr class="bloc_cart_voucher" id="bloc_cart_voucher_<?php echo $_smarty_tpl->tpl_vars['discount']->value['id_discount'];?>
">
					<td class="name" title="<?php echo $_smarty_tpl->tpl_vars['discount']->value['description'];?>
"><?php echo smarty_modifier_escape(smarty_modifier_truncate((($_smarty_tpl->tpl_vars['discount']->value['name']).(' : ')).($_smarty_tpl->tpl_vars['discount']->value['description']),18,'...'),'htmlall','UTF-8');?>
</td>
					<td class="price">-<?php if ($_smarty_tpl->tpl_vars['discount']->value['value_real']!='!'){?><?php if ($_smarty_tpl->getVariable('priceDisplay')->value==1){?><?php echo Product::convertPrice(array('price'=>$_smarty_tpl->tpl_vars['discount']->value['value_tax_exc']),$_smarty_tpl);?>
<?php }else{ ?><?php echo Product::convertPrice(array('price'=>$_smarty_tpl->tpl_vars['discount']->value['value_real']),$_smarty_tpl);?>
<?php }?><?php }?></td>
					<td class="delete"><a href="<?php echo $_smarty_tpl->getVariable('link')->value->getPageLink(($_smarty_tpl->getVariable('order_process')->value).".php",true);?>
?deleteDiscount=<?php echo $_smarty_tpl->tpl_vars['discount']->value['id_discount'];?>
" title="<?php echo smartyTranslate(array('s'=>'Delete'),$_smarty_tpl);?>
"><img src="<?php echo $_smarty_tpl->getVariable('img_dir')->value;?>
icon/delete.gif" alt="<?php echo smartyTranslate(array('s'=>'Delete'),$_smarty_tpl);?>
" class="icon" /></a></td>
				</tr>
			<?php }} ?>
			</tbody>
		</table>
		<?php }?>
		
		<p id="cart-prices">
			<span><?php echo smartyTranslate(array('s'=>'Shipping','mod'=>'blockcart'),$_smarty_tpl);?>
</span>
			<span id="cart_block_shipping_cost" class="price ajax_cart_shipping_cost"><?php echo $_smarty_tpl->getVariable('shipping_cost')->value;?>
</span>
			<br/>
			<?php if ($_smarty_tpl->getVariable('show_wrapping')->value){?>
				<span><?php echo smartyTranslate(array('s'=>'Wrapping','mod'=>'blockcart'),$_smarty_tpl);?>
</span>
				<span id="cart_block_wrapping_cost" class="price cart_block_wrapping_cost"><?php if ($_smarty_tpl->getVariable('priceDisplay')->value==1){?><?php echo Product::convertPrice(array('price'=>$_smarty_tpl->getVariable('cart')->value->getOrderTotal(false,6)),$_smarty_tpl);?>
<?php }else{ ?><?php echo Product::convertPrice(array('price'=>$_smarty_tpl->getVariable('cart')->value->getOrderTotal(true,6)),$_smarty_tpl);?>
<?php }?></span>
				<br/>
			<?php }?>
			<?php if ($_smarty_tpl->getVariable('show_tax')->value){?>
				<span><?php echo smartyTranslate(array('s'=>'Tax','mod'=>'blockcart'),$_smarty_tpl);?>
</span>
				<span id="cart_block_tax_cost" class="price ajax_cart_tax_cost"><?php echo $_smarty_tpl->getVariable('tax_cost')->value;?>
</span>
				<br/>
			<?php }?>
			<span><?php echo smartyTranslate(array('s'=>'Total','mod'=>'blockcart'),$_smarty_tpl);?>
</span>
			<span id="cart_block_total" class="price ajax_block_cart_total"><?php echo $_smarty_tpl->getVariable('total')->value;?>
</span>
		</p>
		<?php if ($_smarty_tpl->getVariable('priceDisplay')->value==2){?>
			<p id="cart-price-precisions">
				<?php echo smartyTranslate(array('s'=>'Prices are tax included','mod'=>'blockcart'),$_smarty_tpl);?>

			</p>
		<?php }?>
		<?php if ($_smarty_tpl->getVariable('priceDisplay')->value==1){?>
			<p id="cart-price-precisions">
				<?php echo smartyTranslate(array('s'=>'Prices are tax excluded','mod'=>'blockcart'),$_smarty_tpl);?>

			</p>
		<?php }?>
		<p id="cart-buttons">
			<?php if ($_smarty_tpl->getVariable('order_process')->value=='order'){?><a href="<?php echo $_smarty_tpl->getVariable('link')->value->getPageLink(($_smarty_tpl->getVariable('order_process')->value).".php",true);?>
" class="button_small" title="<?php echo smartyTranslate(array('s'=>'Cart','mod'=>'blockcart'),$_smarty_tpl);?>
"><?php echo smartyTranslate(array('s'=>'Cart','mod'=>'blockcart'),$_smarty_tpl);?>
</a><?php }?>
			<a href="<?php echo $_smarty_tpl->getVariable('link')->value->getPageLink(($_smarty_tpl->getVariable('order_process')->value).".php",true);?>
<?php if ($_smarty_tpl->getVariable('order_process')->value=='order'){?>?step=1<?php }?>" id="button_order_cart" class="exclusive<?php if ($_smarty_tpl->getVariable('order_process')->value=='order-opc'){?>_large<?php }?>" title="<?php echo smartyTranslate(array('s'=>'Check out','mod'=>'blockcart'),$_smarty_tpl);?>
"><?php echo smartyTranslate(array('s'=>'Check out','mod'=>'blockcart'),$_smarty_tpl);?>
</a>
		</p>
	</div>
	</div>
</div>
<!-- /MODULE Block cart -->
