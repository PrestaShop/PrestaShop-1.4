<?php /* Smarty version Smarty-3.0.5, created on 2010-11-30 10:10:20
         compiled from "C:\Apache\htdocs\jquery/modules/blockcurrencies/blockcurrencies.tpl" */ ?>
<?php /*%%SmartyHeaderCode:204544cf4bf7c306ba7-66806512%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '9d21133aa1545f9e686dc8032afc6cef35a0dc74' => 
    array (
      0 => 'C:\\Apache\\htdocs\\jquery/modules/blockcurrencies/blockcurrencies.tpl',
      1 => 1290763401,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '204544cf4bf7c306ba7-66806512',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<!-- Block currencies module -->
<div id="currencies_block_top">
	<form id="setCurrency" action="<?php echo $_smarty_tpl->getVariable('request_uri')->value;?>
" method="post">
		<ul>
			<?php  $_smarty_tpl->tpl_vars['f_currency'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['k'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('currencies')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['f_currency']->key => $_smarty_tpl->tpl_vars['f_currency']->value){
 $_smarty_tpl->tpl_vars['k']->value = $_smarty_tpl->tpl_vars['f_currency']->key;
?>
				<li <?php if ($_smarty_tpl->getVariable('id_currency_cookie')->value==$_smarty_tpl->tpl_vars['f_currency']->value['id_currency']){?>class="selected"<?php }?>>
					<a href="javascript:setCurrency(<?php echo $_smarty_tpl->tpl_vars['f_currency']->value['id_currency'];?>
);" title="<?php echo $_smarty_tpl->tpl_vars['f_currency']->value['name'];?>
"><?php echo $_smarty_tpl->tpl_vars['f_currency']->value['sign'];?>
</a>
				</li>
			<?php }} ?>
		</ul>
		<p>
				<input type="hidden" name="id_currency" id="id_currency" value=""/>
				<input type="hidden" name="SubmitCurrency" value="" />
			<?php echo smartyTranslate(array('s'=>'Currency','mod'=>'blockcurrencies'),$_smarty_tpl);?>

		</p>
	</form>
</div>
<!-- /Block currencies module -->