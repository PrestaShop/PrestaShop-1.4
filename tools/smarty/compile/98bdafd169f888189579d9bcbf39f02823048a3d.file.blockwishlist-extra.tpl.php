<?php /* Smarty version Smarty-3.0.5, created on 2010-11-30 10:10:20
         compiled from "C:\Apache\htdocs\jquery/modules/blockwishlist/blockwishlist-extra.tpl" */ ?>
<?php /*%%SmartyHeaderCode:301424cf4bf7cc053f9-31579808%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '98bdafd169f888189579d9bcbf39f02823048a3d' => 
    array (
      0 => 'C:\\Apache\\htdocs\\jquery/modules/blockwishlist/blockwishlist-extra.tpl',
      1 => 1290783984,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '301424cf4bf7cc053f9-31579808',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<p class="buttons_bottom_block"><a href="#" id="wishlist_button" class="button" onclick="WishlistCart('wishlist_block_list', 'add', '<?php echo intval($_smarty_tpl->getVariable('id_product')->value);?>
', $('#idCombination').val(), document.getElementById('quantity_wanted').value); return false;"><?php echo smartyTranslate(array('s'=>'Add to my wishlist','mod'=>'blockwishlist'),$_smarty_tpl);?>
</a></p>