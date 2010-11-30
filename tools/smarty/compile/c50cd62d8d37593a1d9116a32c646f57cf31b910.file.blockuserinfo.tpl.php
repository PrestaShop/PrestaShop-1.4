<?php /* Smarty version Smarty-3.0.5, created on 2010-11-30 10:10:20
         compiled from "C:\Apache\htdocs\jquery/modules/blockuserinfo/blockuserinfo.tpl" */ ?>
<?php /*%%SmartyHeaderCode:11384cf4bf7c56fef2-78239925%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c50cd62d8d37593a1d9116a32c646f57cf31b910' => 
    array (
      0 => 'C:\\Apache\\htdocs\\jquery/modules/blockuserinfo/blockuserinfo.tpl',
      1 => 1290763411,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '11384cf4bf7c56fef2-78239925',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<!-- Block user information module HEADER -->
<div id="header_user">
	<p id="header_user_info">
		<?php echo smartyTranslate(array('s'=>'Welcome','mod'=>'blockuserinfo'),$_smarty_tpl);?>
,
		<?php if ($_smarty_tpl->getVariable('logged')->value){?>
			<span><?php echo $_smarty_tpl->getVariable('customerName')->value;?>
</span> (<a href="<?php echo $_smarty_tpl->getVariable('link')->value->getPageLink('index.php');?>
?mylogout" title="<?php echo smartyTranslate(array('s'=>'Log me out','mod'=>'blockuserinfo'),$_smarty_tpl);?>
"><?php echo smartyTranslate(array('s'=>'Log out','mod'=>'blockuserinfo'),$_smarty_tpl);?>
</a>)
		<?php }else{ ?>
			<a href="<?php echo $_smarty_tpl->getVariable('link')->value->getPageLink('my-account.php',true);?>
"><?php echo smartyTranslate(array('s'=>'Log in','mod'=>'blockuserinfo'),$_smarty_tpl);?>
</a>
		<?php }?>
	</p>
	<ul id="header_nav">
		<li id="shopping_cart">
			<a href="<?php echo $_smarty_tpl->getVariable('link')->value->getPageLink(($_smarty_tpl->getVariable('order_process')->value).".php",true);?>
" title="<?php echo smartyTranslate(array('s'=>'Your Shopping Cart','mod'=>'blockuserinfo'),$_smarty_tpl);?>
"><?php echo smartyTranslate(array('s'=>'Cart:','mod'=>'blockuserinfo'),$_smarty_tpl);?>
</a>
			<span class="ajax_cart_quantity<?php if ($_smarty_tpl->getVariable('cart_qties')->value==0){?> hidden<?php }?>"><?php echo $_smarty_tpl->getVariable('cart_qties')->value;?>
</span>
			<span class="ajax_cart_product_txt<?php if ($_smarty_tpl->getVariable('cart_qties')->value!=1){?> hidden<?php }?>"><?php echo smartyTranslate(array('s'=>'product','mod'=>'blockuserinfo'),$_smarty_tpl);?>
</span>
			<span class="ajax_cart_product_txt_s<?php if ($_smarty_tpl->getVariable('cart_qties')->value<2){?> hidden<?php }?>"><?php echo smartyTranslate(array('s'=>'products','mod'=>'blockuserinfo'),$_smarty_tpl);?>
</span>
			<?php if ($_smarty_tpl->getVariable('cart_qties')->value>0){?>
				<span class="ajax_cart_total<?php if ($_smarty_tpl->getVariable('cart_qties')->value==0){?> hidden<?php }?>">
					<?php if ($_smarty_tpl->getVariable('priceDisplay')->value==1){?>
						<?php echo Product::convertPrice(array('price'=>$_smarty_tpl->getVariable('cart')->value->getOrderTotal(false,4)),$_smarty_tpl);?>

					<?php }else{ ?>
						<?php echo Product::convertPrice(array('price'=>$_smarty_tpl->getVariable('cart')->value->getOrderTotal(true,4)),$_smarty_tpl);?>

					<?php }?>
				</span>
			<?php }?>
			<span class="ajax_cart_no_product<?php if ($_smarty_tpl->getVariable('cart_qties')->value>0){?> hidden<?php }?>"><?php echo smartyTranslate(array('s'=>'(empty)','mod'=>'blockuserinfo'),$_smarty_tpl);?>
</span>
		</li>
		<li id="your_account"><a href="<?php echo $_smarty_tpl->getVariable('link')->value->getPageLink('my-account.php',true);?>
" title="<?php echo smartyTranslate(array('s'=>'Your Account','mod'=>'blockuserinfo'),$_smarty_tpl);?>
"><?php echo smartyTranslate(array('s'=>'Your Account','mod'=>'blockuserinfo'),$_smarty_tpl);?>
</a></li>
	</ul>
</div>
<!-- /Block user information module HEADER -->
