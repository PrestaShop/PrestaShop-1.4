<?php /* Smarty version Smarty-3.0.5, created on 2010-11-30 10:10:20
         compiled from "C:\Apache\htdocs\jquery/modules/productscategory/productscategory.tpl" */ ?>
<?php /*%%SmartyHeaderCode:320644cf4bf7c8e3d50-48973614%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'b011b20c92887cbb6232a18b83bf6b341d27ee2c' => 
    array (
      0 => 'C:\\Apache\\htdocs\\jquery/modules/productscategory/productscategory.tpl',
      1 => 1290763392,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '320644cf4bf7c8e3d50-48973614',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!is_callable('smarty_function_math')) include 'C:\Apache\htdocs\jquery\tools\smarty\plugins\function.math.php';
if (!is_callable('smarty_modifier_escape')) include 'C:\Apache\htdocs\jquery\tools\smarty\plugins\modifier.escape.php';
?><?php if (count($_smarty_tpl->getVariable('categoryProducts')->value)>0){?>
<script type="text/javascript">var middle = <?php echo $_smarty_tpl->getVariable('middlePosition')->value;?>
;</script>
<script type="text/javascript" src="<?php echo $_smarty_tpl->getVariable('content_dir')->value;?>
modules/productscategory/js/productscategory.js"></script>
<ul class="idTabs">
	<li><a href="#idTab3"><?php echo smartyTranslate(array('s'=>'In the same category','mod'=>'productscategory'),$_smarty_tpl);?>
</a></li>
</ul>

<div id="<?php if (count($_smarty_tpl->getVariable('categoryProducts')->value)>5){?>productscategory<?php }else{ ?>productscategory_noscroll<?php }?>">
<?php if (count($_smarty_tpl->getVariable('categoryProducts')->value)>5){?><a id="productscategory_scroll_left" title="<?php echo smartyTranslate(array('s'=>'Previous','mod'=>'productscategory'),$_smarty_tpl);?>
" href="javascript:{}"><?php echo smartyTranslate(array('s'=>'Previous','mod'=>'productscategory'),$_smarty_tpl);?>
</a><?php }?>
<div id="productscategory_list">
	<ul <?php if (count($_smarty_tpl->getVariable('categoryProducts')->value)>5){?>style="width: <?php echo smarty_function_math(array('equation'=>"width * nbImages",'width'=>107,'nbImages'=>count($_smarty_tpl->getVariable('categoryProducts')->value)),$_smarty_tpl);?>
px"<?php }?>>
		<?php  $_smarty_tpl->tpl_vars['categoryProduct'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('categoryProducts')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['categoryProduct']->key => $_smarty_tpl->tpl_vars['categoryProduct']->value){
?>
		<li <?php if (count($_smarty_tpl->getVariable('categoryProducts')->value)<6){?>style="width: <?php echo smarty_function_math(array('equation'=>"width / nbImages",'width'=>94,'nbImages'=>count($_smarty_tpl->getVariable('categoryProducts')->value)),$_smarty_tpl);?>
%"<?php }?>>
			<a href="<?php echo $_smarty_tpl->getVariable('link')->value->getProductLink($_smarty_tpl->tpl_vars['categoryProduct']->value['id_product'],$_smarty_tpl->tpl_vars['categoryProduct']->value['link_rewrite'],$_smarty_tpl->tpl_vars['categoryProduct']->value['category']);?>
" title="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['categoryProduct']->value['name']);?>
">
				<img src="<?php echo $_smarty_tpl->getVariable('link')->value->getImageLink($_smarty_tpl->tpl_vars['categoryProduct']->value['link_rewrite'],$_smarty_tpl->tpl_vars['categoryProduct']->value['id_image'],'medium');?>
" alt="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['categoryProduct']->value['name']);?>
" />
			</a><br/>
			<a href="<?php echo $_smarty_tpl->getVariable('link')->value->getProductLink($_smarty_tpl->tpl_vars['categoryProduct']->value['id_product'],$_smarty_tpl->tpl_vars['categoryProduct']->value['link_rewrite'],$_smarty_tpl->tpl_vars['categoryProduct']->value['category'],$_smarty_tpl->tpl_vars['categoryProduct']->value['ean13']);?>
" title="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['categoryProduct']->value['name']);?>
">
			<?php echo smarty_modifier_escape(smarty_modifier_truncate($_smarty_tpl->tpl_vars['categoryProduct']->value['name'],15,'...'),'htmlall','UTF-8');?>

			</a><br />
			<?php if ($_smarty_tpl->getVariable('ProdDisplayPrice')->value&&$_smarty_tpl->tpl_vars['categoryProduct']->value['show_price']==1&&!isset($_smarty_tpl->getVariable('restricted_country_mode',null,true,false)->value)){?>
				<span class="price_display">
					<span class="price"><?php echo Product::convertPrice(array('price'=>$_smarty_tpl->tpl_vars['categoryProduct']->value['displayed_price']),$_smarty_tpl);?>
</span>
				</span><br />
			<?php }else{ ?>
				<br />
			<?php }?>
			<a title="<?php echo smartyTranslate(array('s'=>'View','mod'=>'productscategory'),$_smarty_tpl);?>
" href="<?php echo $_smarty_tpl->getVariable('link')->value->getProductLink($_smarty_tpl->tpl_vars['categoryProduct']->value['id_product'],$_smarty_tpl->tpl_vars['categoryProduct']->value['link_rewrite'],$_smarty_tpl->tpl_vars['categoryProduct']->value['category'],$_smarty_tpl->tpl_vars['categoryProduct']->value['ean13']);?>
" class="button_small"><?php echo smartyTranslate(array('s'=>'View','mod'=>'productscategory'),$_smarty_tpl);?>
</a><br />
		</li>
		<?php }} ?>
	</ul>
</div>
<?php if (count($_smarty_tpl->getVariable('categoryProducts')->value)>5){?><a id="productscategory_scroll_right" title="<?php echo smartyTranslate(array('s'=>'Next','mod'=>'productscategory'),$_smarty_tpl);?>
" href="javascript:{}"><?php echo smartyTranslate(array('s'=>'Next','mod'=>'productscategory'),$_smarty_tpl);?>
</a><?php }?>
</div>
<?php }?>
