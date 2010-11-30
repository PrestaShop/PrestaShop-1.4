<?php /* Smarty version Smarty-3.0.5, created on 2010-11-30 10:10:20
         compiled from "C:\Apache\htdocs\jquery/modules/blocklanguages/blocklanguages.tpl" */ ?>
<?php /*%%SmartyHeaderCode:187274cf4bf7c367214-02595319%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '5eca15870fefd5757794dd5c142f121050623cc0' => 
    array (
      0 => 'C:\\Apache\\htdocs\\jquery/modules/blocklanguages/blocklanguages.tpl',
      1 => 1290763387,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '187274cf4bf7c367214-02595319',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<!-- Block languages module -->
<div id="languages_block_top">
	<ul id="first-languages">
		<?php  $_smarty_tpl->tpl_vars['language'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['k'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('languages')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['language']->key => $_smarty_tpl->tpl_vars['language']->value){
 $_smarty_tpl->tpl_vars['k']->value = $_smarty_tpl->tpl_vars['language']->key;
?>
			<li <?php if ($_smarty_tpl->tpl_vars['language']->value['iso_code']==$_smarty_tpl->getVariable('lang_iso')->value){?>class="selected_language"<?php }?>>
				<?php if ($_smarty_tpl->tpl_vars['language']->value['iso_code']!=$_smarty_tpl->getVariable('lang_iso')->value){?><a href="<?php echo $_smarty_tpl->getVariable('link')->value->getLanguageLink($_smarty_tpl->tpl_vars['language']->value['id_lang'],$_smarty_tpl->tpl_vars['language']->value['name']);?>
" title="<?php echo $_smarty_tpl->tpl_vars['language']->value['name'];?>
"><?php }?>
					<img src="<?php echo $_smarty_tpl->getVariable('img_lang_dir')->value;?>
<?php echo $_smarty_tpl->tpl_vars['language']->value['id_lang'];?>
.jpg" alt="<?php echo $_smarty_tpl->tpl_vars['language']->value['iso_code'];?>
" width="16" height="11" />
				<?php if ($_smarty_tpl->tpl_vars['language']->value['iso_code']!=$_smarty_tpl->getVariable('lang_iso')->value){?></a><?php }?>
			</li>
		<?php }} ?>
	</ul>
</div>
<script type="text/javascript">
	$('ul#first-languages li:not(.selected_language)').css('opacity', 0.3);
	$('ul#first-languages li:not(.selected_language)').hover(function(){
		$(this).css('opacity', 1);
	}, function(){
		$(this).css('opacity', 0.3);
	});
</script>
<!-- /Block languages module -->

