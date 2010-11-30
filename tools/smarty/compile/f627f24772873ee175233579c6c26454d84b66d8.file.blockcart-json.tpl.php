<?php /* Smarty version Smarty-3.0.5, created on 2010-11-30 10:10:23
         compiled from "C:\Apache\htdocs\jquery/modules/blockcart/blockcart-json.tpl" */ ?>
<?php /*%%SmartyHeaderCode:80324cf4bf7fd01794-08085159%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'f627f24772873ee175233579c6c26454d84b66d8' => 
    array (
      0 => 'C:\\Apache\\htdocs\\jquery/modules/blockcart/blockcart-json.tpl',
      1 => 1290763410,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '80324cf4bf7fd01794-08085159',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
{
"products": [
<?php if ($_smarty_tpl->getVariable('products')->value){?>
<?php  $_smarty_tpl->tpl_vars['product'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('products')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['product']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['product']->iteration=0;
if ($_smarty_tpl->tpl_vars['product']->total > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['product']->key => $_smarty_tpl->tpl_vars['product']->value){
 $_smarty_tpl->tpl_vars['product']->iteration++;
 $_smarty_tpl->tpl_vars['product']->last = $_smarty_tpl->tpl_vars['product']->iteration === $_smarty_tpl->tpl_vars['product']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['products']['last'] = $_smarty_tpl->tpl_vars['product']->last;
?>
<?php $_smarty_tpl->tpl_vars['productId'] = new Smarty_variable($_smarty_tpl->tpl_vars['product']->value['id_product'], null, null);?>
<?php $_smarty_tpl->tpl_vars['productAttributeId'] = new Smarty_variable($_smarty_tpl->tpl_vars['product']->value['id_product_attribute'], null, null);?>
	{
		"id":            <?php echo $_smarty_tpl->tpl_vars['product']->value['id_product'];?>
,
		"link":          "<?php echo addslashes($_smarty_tpl->getVariable('link')->value->getProductLink($_smarty_tpl->tpl_vars['product']->value['id_product'],$_smarty_tpl->tpl_vars['product']->value['link_rewrite'],$_smarty_tpl->tpl_vars['product']->value['category']));?>
",
		"quantity":      <?php echo $_smarty_tpl->tpl_vars['product']->value['cart_quantity'];?>
,
		"priceByLine":   "<?php if ($_smarty_tpl->getVariable('priceDisplay')->value==@PS_TAX_EXC){?><?php ob_start();?><?php echo Product::displayWtPrice(array('p'=>$_smarty_tpl->tpl_vars['product']->value['total']),$_smarty_tpl);?>
<?php echo html_entity_decode(ob_get_clean(),2,'UTF-8')?><?php }else{ ?><?php ob_start();?><?php echo Product::displayWtPrice(array('p'=>$_smarty_tpl->tpl_vars['product']->value['total_wt']),$_smarty_tpl);?>
<?php echo html_entity_decode(ob_get_clean(),2,'UTF-8')?><?php }?>",
		"name":          "<?php echo smarty_modifier_truncate(addslashes(html_entity_decode($_smarty_tpl->tpl_vars['product']->value['name'],2,'UTF-8')),15,'...',true);?>
",
		"price":         "<?php if ($_smarty_tpl->getVariable('priceDisplay')->value==@PS_TAX_EXC){?><?php ob_start();?><?php echo Product::displayWtPrice(array('p'=>$_smarty_tpl->tpl_vars['product']->value['total']),$_smarty_tpl);?>
<?php echo html_entity_decode(ob_get_clean(),2,'UTF-8')?><?php }else{ ?><?php ob_start();?><?php echo Product::displayWtPrice(array('p'=>$_smarty_tpl->tpl_vars['product']->value['total_wt']),$_smarty_tpl);?>
<?php echo html_entity_decode(ob_get_clean(),2,'UTF-8')?><?php }?>",
		"idCombination": <?php if (isset($_smarty_tpl->tpl_vars['product']->value['attributes_small'])){?><?php echo $_smarty_tpl->getVariable('productAttributeId')->value;?>
<?php }else{ ?>0<?php }?>,
<?php if (isset($_smarty_tpl->tpl_vars['product']->value['attributes_small'])){?>
		"hasAttributes": true,
		"attributes":    "<?php echo addslashes($_smarty_tpl->tpl_vars['product']->value['attributes_small']);?>
",
<?php }else{ ?>
		"hasAttributes": false,
<?php }?>
		"hasCustomizedDatas": <?php if (isset($_smarty_tpl->getVariable('customizedDatas',null,true,false)->value[$_smarty_tpl->getVariable('productId',null,true,false)->value][$_smarty_tpl->getVariable('productAttributeId',null,true,false)->value])){?>true<?php }else{ ?>false<?php }?>,

		"customizedDatas":[
		<?php  $_smarty_tpl->tpl_vars['customization'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['id_customization'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('customizedDatas')->value[$_smarty_tpl->getVariable('productId')->value][$_smarty_tpl->getVariable('productAttributeId')->value]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['customization']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['customization']->iteration=0;
if ($_smarty_tpl->tpl_vars['customization']->total > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['customization']->key => $_smarty_tpl->tpl_vars['customization']->value){
 $_smarty_tpl->tpl_vars['id_customization']->value = $_smarty_tpl->tpl_vars['customization']->key;
 $_smarty_tpl->tpl_vars['customization']->iteration++;
 $_smarty_tpl->tpl_vars['customization']->last = $_smarty_tpl->tpl_vars['customization']->iteration === $_smarty_tpl->tpl_vars['customization']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['customizedDatas']['last'] = $_smarty_tpl->tpl_vars['customization']->last;
?>{

			"customizationId":	<?php echo $_smarty_tpl->tpl_vars['id_customization']->value;?>
,
			"quantity":			<?php echo $_smarty_tpl->tpl_vars['customization']->value['quantity'];?>
,
			"datas": [
				<?php  $_smarty_tpl->tpl_vars['datas'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['type'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['customization']->value['datas']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['datas']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['datas']->iteration=0;
if ($_smarty_tpl->tpl_vars['datas']->total > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['datas']->key => $_smarty_tpl->tpl_vars['datas']->value){
 $_smarty_tpl->tpl_vars['type']->value = $_smarty_tpl->tpl_vars['datas']->key;
 $_smarty_tpl->tpl_vars['datas']->iteration++;
 $_smarty_tpl->tpl_vars['datas']->last = $_smarty_tpl->tpl_vars['datas']->iteration === $_smarty_tpl->tpl_vars['datas']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['customization']['last'] = $_smarty_tpl->tpl_vars['datas']->last;
?>
				{
					"type":	<?php echo $_smarty_tpl->tpl_vars['type']->value;?>
,
					"datas":
					[
					<?php  $_smarty_tpl->tpl_vars['data'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['index'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['datas']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['data']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['data']->iteration=0;
if ($_smarty_tpl->tpl_vars['data']->total > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['data']->key => $_smarty_tpl->tpl_vars['data']->value){
 $_smarty_tpl->tpl_vars['index']->value = $_smarty_tpl->tpl_vars['data']->key;
 $_smarty_tpl->tpl_vars['data']->iteration++;
 $_smarty_tpl->tpl_vars['data']->last = $_smarty_tpl->tpl_vars['data']->iteration === $_smarty_tpl->tpl_vars['data']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['datas']['last'] = $_smarty_tpl->tpl_vars['data']->last;
?>
						{
						"index":			<?php echo $_smarty_tpl->tpl_vars['index']->value;?>
,
						"value":			"<?php echo addslashes($_smarty_tpl->tpl_vars['data']->value['value']);?>
",
						"truncatedValue":	"<?php echo addslashes(smarty_modifier_truncate($_smarty_tpl->tpl_vars['data']->value['value'],28,'...'));?>
"
						}<?php if (!$_smarty_tpl->getVariable('smarty')->value['foreach']['datas']['last']){?>,<?php }?>
					<?php }} ?>]
				}<?php if (!$_smarty_tpl->getVariable('smarty')->value['foreach']['customization']['last']){?>,<?php }?>
				<?php }} ?>
			]
		}<?php if (!$_smarty_tpl->getVariable('smarty')->value['foreach']['customizedDatas']['last']){?>,<?php }?>
		<?php }} ?>
		]


	}<?php if (!$_smarty_tpl->getVariable('smarty')->value['foreach']['products']['last']){?>,<?php }?>
<?php }} ?><?php }?>
],

"discounts": [
<?php if ($_smarty_tpl->getVariable('discounts')->value){?><?php  $_smarty_tpl->tpl_vars['discount'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('discounts')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['discount']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['discount']->iteration=0;
if ($_smarty_tpl->tpl_vars['discount']->total > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['discount']->key => $_smarty_tpl->tpl_vars['discount']->value){
 $_smarty_tpl->tpl_vars['discount']->iteration++;
 $_smarty_tpl->tpl_vars['discount']->last = $_smarty_tpl->tpl_vars['discount']->iteration === $_smarty_tpl->tpl_vars['discount']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['discounts']['last'] = $_smarty_tpl->tpl_vars['discount']->last;
?>
	{
		"id":              "<?php echo $_smarty_tpl->tpl_vars['discount']->value['id_discount'];?>
",
		"name":            "<?php echo addslashes(smarty_modifier_truncate((($_smarty_tpl->tpl_vars['discount']->value['name']).(' : ')).($_smarty_tpl->tpl_vars['discount']->value['description']),18,'...'));?>
",
		"description":     "<?php echo addslashes($_smarty_tpl->tpl_vars['discount']->value['description']);?>
",
		"nameDescription": "<?php echo smarty_modifier_truncate((($_smarty_tpl->tpl_vars['discount']->value['name']).(' : ')).($_smarty_tpl->tpl_vars['discount']->value['description']),18,'...');?>
",
		"link":            "<?php echo $_smarty_tpl->getVariable('link')->value->getPageLink('order.php',true);?>
?deleteDiscount=<?php echo $_smarty_tpl->tpl_vars['discount']->value['id_discount'];?>
",
		"price":           "-<?php if ($_smarty_tpl->tpl_vars['discount']->value['value_real']!='!'){?><?php if ($_smarty_tpl->getVariable('priceDisplay')->value==1){?><?php ob_start();?><?php echo Product::convertPrice(array('price'=>$_smarty_tpl->tpl_vars['discount']->value['value_tax_exc']),$_smarty_tpl);?>
<?php echo html_entity_decode(ob_get_clean(),2,'UTF-8')?><?php }else{ ?><?php ob_start();?><?php echo Product::convertPrice(array('price'=>$_smarty_tpl->tpl_vars['discount']->value['value_real']),$_smarty_tpl);?>
<?php echo html_entity_decode(ob_get_clean(),2,'UTF-8')?><?php }?><?php }?>"
	}
	<?php if (!$_smarty_tpl->getVariable('smarty')->value['foreach']['discounts']['last']){?>,<?php }?>
<?php }} ?><?php }?>
],

"shippingCost": "<?php echo html_entity_decode($_smarty_tpl->getVariable('shipping_cost')->value,2,'UTF-8');?>
",
"wrappingCost": "<?php echo html_entity_decode($_smarty_tpl->getVariable('wrapping_cost')->value,2,'UTF-8');?>
",
"nbTotalProducts": "<?php echo $_smarty_tpl->getVariable('nb_total_products')->value;?>
",
"total": "<?php echo html_entity_decode($_smarty_tpl->getVariable('total')->value,2,'UTF-8');?>
",
"productTotal": "<?php echo html_entity_decode($_smarty_tpl->getVariable('product_total')->value,2,'UTF-8');?>
",

<?php if (isset($_smarty_tpl->getVariable('errors',null,true,false)->value)&&$_smarty_tpl->getVariable('errors')->value){?>
"hasError" : true,
errors : [
<?php  $_smarty_tpl->tpl_vars['error'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['k'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('errors')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['error']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['error']->iteration=0;
if ($_smarty_tpl->tpl_vars['error']->total > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['error']->key => $_smarty_tpl->tpl_vars['error']->value){
 $_smarty_tpl->tpl_vars['k']->value = $_smarty_tpl->tpl_vars['error']->key;
 $_smarty_tpl->tpl_vars['error']->iteration++;
 $_smarty_tpl->tpl_vars['error']->last = $_smarty_tpl->tpl_vars['error']->iteration === $_smarty_tpl->tpl_vars['error']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['errors']['last'] = $_smarty_tpl->tpl_vars['error']->last;
?>
	"<?php echo html_entity_decode(addslashes($_smarty_tpl->tpl_vars['error']->value),2,'UTF-8');?>
"
	<?php if (!$_smarty_tpl->getVariable('smarty')->value['foreach']['errors']['last']){?>,<?php }?>
<?php }} ?>
]
<?php }else{ ?>
"hasError" : false
<?php }?>

}
