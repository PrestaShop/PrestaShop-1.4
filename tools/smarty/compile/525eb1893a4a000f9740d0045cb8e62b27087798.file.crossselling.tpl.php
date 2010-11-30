<?php /* Smarty version Smarty-3.0.5, created on 2010-11-30 10:10:20
         compiled from "C:\Apache\htdocs\jquery/modules/crossselling/crossselling.tpl" */ ?>
<?php /*%%SmartyHeaderCode:11454cf4bf7caaa7b7-86382698%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '525eb1893a4a000f9740d0045cb8e62b27087798' => 
    array (
      0 => 'C:\\Apache\\htdocs\\jquery/modules/crossselling/crossselling.tpl',
      1 => 1291108201,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '11454cf4bf7caaa7b7-86382698',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!is_callable('smarty_function_math')) include 'C:\Apache\htdocs\jquery\tools\smarty\plugins\function.math.php';
if (!is_callable('smarty_modifier_escape')) include 'C:\Apache\htdocs\jquery\tools\smarty\plugins\modifier.escape.php';
?><?php if (isset($_smarty_tpl->getVariable('orderProducts',null,true,false)->value)&&count($_smarty_tpl->getVariable('orderProducts')->value)){?>
<div id="crossselling">
	<script type="text/javascript">var middle = <?php echo $_smarty_tpl->getVariable('middlePosition_crossselling')->value;?>
;</script>
	<script type="text/javascript" src="<?php echo $_smarty_tpl->getVariable('content_dir')->value;?>
modules/crossselling/js/crossselling.js"></script>
	<h2><?php echo smartyTranslate(array('s'=>'Customers who bought this product also bought:','mod'=>'crossselling'),$_smarty_tpl);?>
</h2>
	<div id="<?php if (count($_smarty_tpl->getVariable('orderProducts')->value)>5){?>crossselling<?php }else{ ?>crossselling_noscroll<?php }?>">
		<?php if (count($_smarty_tpl->getVariable('orderProducts')->value)>5){?><a id="crossselling_scroll_left" title="<?php echo smartyTranslate(array('s'=>'Previous','mod'=>'crossselling'),$_smarty_tpl);?>
" href="javascript:{}"><?php echo smartyTranslate(array('s'=>'Previous','mod'=>'crossselling'),$_smarty_tpl);?>
</a><?php }?>
		<div id="crossselling_list">
			<ul <?php if (count($_smarty_tpl->getVariable('orderProducts')->value)>5){?>style="width: <?php echo smarty_function_math(array('equation'=>"width * nbImages",'width'=>107,'nbImages'=>($_smarty_tpl->getVariable('orderProducts')->value)."|@count"),$_smarty_tpl);?>
px"<?php }?>>
				<?php  $_smarty_tpl->tpl_vars['orderProduct'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('orderProducts')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['orderProduct']->key => $_smarty_tpl->tpl_vars['orderProduct']->value){
?>
				<li <?php if (count($_smarty_tpl->getVariable('orderProducts')->value)<6){?>style="width: <?php echo smarty_function_math(array('equation'=>"width / nbImages",'width'=>94,'nbImages'=>($_smarty_tpl->getVariable('orderProducts')->value)."|@count"),$_smarty_tpl);?>
%"<?php }?>>
					<a href="<?php echo $_smarty_tpl->tpl_vars['orderProduct']->value['link'];?>
" title="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['orderProduct']->value['name']);?>
">
						<img src="<?php echo $_smarty_tpl->tpl_vars['orderProduct']->value['image'];?>
" alt="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['orderProduct']->value['name']);?>
" />
					</a><br/>
					<a href="<?php echo $_smarty_tpl->tpl_vars['orderProduct']->value['link'];?>
" title="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['orderProduct']->value['name']);?>
">
					<?php echo smarty_modifier_escape(smarty_modifier_truncate($_smarty_tpl->tpl_vars['orderProduct']->value['name'],15,'...'),'htmlall','UTF-8');?>

					</a><br />
					<?php if ($_smarty_tpl->getVariable('crossDisplayPrice')->value&&$_smarty_tpl->tpl_vars['orderProduct']->value['show_price']==1&&!isset($_smarty_tpl->getVariable('restricted_country_mode',null,true,false)->value)){?>
						<span class="price_display">
							<span class="price"><?php echo Product::convertPrice(array('price'=>$_smarty_tpl->tpl_vars['orderProduct']->value['displayed_price']),$_smarty_tpl);?>
</span>
						</span><br />
					<?php }else{ ?>
						<br />
					<?php }?>
					<a title="<?php echo smartyTranslate(array('s'=>'View','mod'=>'crossselling'),$_smarty_tpl);?>
" href="<?php echo $_smarty_tpl->tpl_vars['orderProduct']->value['link'];?>
" class="button_small"><?php echo smartyTranslate(array('s'=>'View','mod'=>'crossselling'),$_smarty_tpl);?>
</a><br />
				</li>
				<?php }} ?>
			</ul>
		</div>
	<?php if (count($_smarty_tpl->getVariable('orderProducts')->value)>5){?><a id="crossselling_scroll_right" title="<?php echo smartyTranslate(array('s'=>'Next','mod'=>'crossselling'),$_smarty_tpl);?>
" href="javascript:{}"><?php echo smartyTranslate(array('s'=>'Next','mod'=>'crossselling'),$_smarty_tpl);?>
</a><?php }?>
	</div>
</div>
<?php }?>