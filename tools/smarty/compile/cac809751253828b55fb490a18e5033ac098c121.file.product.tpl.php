<?php /* Smarty version Smarty-3.0.5, created on 2010-11-30 10:10:20
         compiled from "C:\Apache\htdocs\jquery/themes/prestashop/product.tpl" */ ?>
<?php /*%%SmartyHeaderCode:116284cf4bf7cc9e2d1-31278498%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'cac809751253828b55fb490a18e5033ac098c121' => 
    array (
      0 => 'C:\\Apache\\htdocs\\jquery/themes/prestashop/product.tpl',
      1 => 1291072812,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '116284cf4bf7cc9e2d1-31278498',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!is_callable('smarty_modifier_escape')) include 'C:\Apache\htdocs\jquery\tools\smarty\plugins\modifier.escape.php';
if (!is_callable('smarty_modifier_date_format')) include 'C:\Apache\htdocs\jquery\tools\smarty\plugins\modifier.date_format.php';
if (!is_callable('smarty_function_math')) include 'C:\Apache\htdocs\jquery\tools\smarty\plugins\function.math.php';
if (!is_callable('smarty_function_counter')) include 'C:\Apache\htdocs\jquery\tools\smarty\plugins\function.counter.php';
?><?php $_template = new Smarty_Internal_Template(($_smarty_tpl->getVariable('tpl_dir')->value)."./errors.tpl", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php $_template->updateParentVariables(0);?><?php unset($_template);?>
<?php if (count($_smarty_tpl->getVariable('errors')->value)==0){?>
<script type="text/javascript">
// <![CDATA[

// PrestaShop internal settings
var currencySign = '<?php echo html_entity_decode($_smarty_tpl->getVariable('currencySign')->value,2,"UTF-8");?>
';
var currencyRate = '<?php echo floatval($_smarty_tpl->getVariable('currencyRate')->value);?>
';
var currencyFormat = '<?php echo intval($_smarty_tpl->getVariable('currencyFormat')->value);?>
';
var currencyBlank = '<?php echo intval($_smarty_tpl->getVariable('currencyBlank')->value);?>
';
var taxRate = <?php echo floatval($_smarty_tpl->getVariable('tax_rate')->value);?>
;
var jqZoomEnabled = <?php if ($_smarty_tpl->getVariable('jqZoomEnabled')->value){?>true<?php }else{ ?>false<?php }?>;

//JS Hook
var oosHookJsCodeFunctions = new Array();

// Parameters
var id_product = '<?php echo intval($_smarty_tpl->getVariable('product')->value->id);?>
';
var productHasAttributes = <?php if (isset($_smarty_tpl->getVariable('groups',null,true,false)->value)){?>true<?php }else{ ?>false<?php }?>;
var quantitiesDisplayAllowed = <?php if ($_smarty_tpl->getVariable('display_qties')->value==1){?>true<?php }else{ ?>false<?php }?>;
var quantityAvailable = <?php if ($_smarty_tpl->getVariable('display_qties')->value==1&&$_smarty_tpl->getVariable('product')->value->quantity){?><?php echo $_smarty_tpl->getVariable('product')->value->quantity;?>
<?php }else{ ?>0<?php }?>;
var allowBuyWhenOutOfStock = <?php if ($_smarty_tpl->getVariable('allow_oosp')->value==1){?>true<?php }else{ ?>false<?php }?>;
var availableNowValue = '<?php echo smarty_modifier_escape($_smarty_tpl->getVariable('product')->value->available_now,'quotes','UTF-8');?>
';
var availableLaterValue = '<?php echo smarty_modifier_escape($_smarty_tpl->getVariable('product')->value->available_later,'quotes','UTF-8');?>
';
var productPriceWithoutReduction = <?php echo (($tmp = @$_smarty_tpl->getVariable('product')->value->getPriceWithoutReduct())===null||$tmp==='' ? 'null' : $tmp);?>
;
var reduction_percent = <?php if ($_smarty_tpl->getVariable('product')->value->specificPrice&&$_smarty_tpl->getVariable('product')->value->specificPrice['reduction']&&$_smarty_tpl->getVariable('product')->value->specificPrice['reduction_type']=='percentage'){?><?php echo $_smarty_tpl->getVariable('product')->value->specificPrice['reduction']*100;?>
<?php }else{ ?>0<?php }?>;
var reduction_price = <?php if ($_smarty_tpl->getVariable('product')->value->specificPrice&&$_smarty_tpl->getVariable('product')->value->specificPrice['reduction']&&$_smarty_tpl->getVariable('product')->value->specificPrice['reduction_type']=='amount'){?><?php echo $_smarty_tpl->getVariable('product')->value->specificPrice['reduction'];?>
<?php }else{ ?>0<?php }?>;
var group_reduction = '<?php echo $_smarty_tpl->getVariable('group_reduction')->value;?>
';
var default_eco_tax = <?php echo $_smarty_tpl->getVariable('product')->value->ecotax;?>
;
var currentDate = '<?php echo smarty_modifier_date_format(time(),'%Y-%m-%d %H:%M:%S');?>
';
var maxQuantityToAllowDisplayOfLastQuantityMessage = <?php echo $_smarty_tpl->getVariable('last_qties')->value;?>
;
var noTaxForThisProduct = <?php if ($_smarty_tpl->getVariable('no_tax')->value==1){?>true<?php }else{ ?>false<?php }?>;
var displayPrice = <?php echo $_smarty_tpl->getVariable('priceDisplay')->value;?>
;
var productReference = '<?php echo smarty_modifier_escape($_smarty_tpl->getVariable('product')->value->reference,'htmlall','UTF-8');?>
';
var productAvailableForOrder = <?php if (isset($_smarty_tpl->getVariable('restricted_country_mode',null,true,false)->value)&&$_smarty_tpl->getVariable('restricted_country_mode')->value){?>'0'<?php }else{ ?>'<?php echo $_smarty_tpl->getVariable('product')->value->available_for_order;?>
'<?php }?>;
var productShowPrice = '<?php echo $_smarty_tpl->getVariable('product')->value->show_price;?>
';
var productUnitPrice = '<?php echo $_smarty_tpl->getVariable('product')->value->unit_price;?>
';

// Customizable field
var img_ps_dir = '<?php echo $_smarty_tpl->getVariable('img_ps_dir')->value;?>
';
var customizationFields = new Array();
<?php $_smarty_tpl->tpl_vars['imgIndex'] = new Smarty_variable(0, null, null);?>
<?php $_smarty_tpl->tpl_vars['textFieldIndex'] = new Smarty_variable(0, null, null);?>
<?php  $_smarty_tpl->tpl_vars['field'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('customizationFields')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['customizationFields']['index']=-1;
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['field']->key => $_smarty_tpl->tpl_vars['field']->value){
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['customizationFields']['index']++;
?>
	<?php $_smarty_tpl->tpl_vars["key"] = new Smarty_variable("pictures_".($_smarty_tpl->getVariable('product')->value->id)."_".($_smarty_tpl->tpl_vars['field']->value['id_customization_field']), null, null);?>
	customizationFields[<?php echo intval($_smarty_tpl->getVariable('smarty')->value['foreach']['customizationFields']['index']);?>
] = new Array();
	customizationFields[<?php echo intval($_smarty_tpl->getVariable('smarty')->value['foreach']['customizationFields']['index']);?>
][0] = '<?php if (intval($_smarty_tpl->tpl_vars['field']->value['type'])==0){?>img<?php echo $_smarty_tpl->getVariable('imgIndex')->value++;?>
<?php }else{ ?>textField<?php echo $_smarty_tpl->getVariable('textFieldIndex')->value++;?>
<?php }?>';
	customizationFields[<?php echo intval($_smarty_tpl->getVariable('smarty')->value['foreach']['customizationFields']['index']);?>
][1] = <?php if (intval($_smarty_tpl->tpl_vars['field']->value['type'])==0&&isset($_smarty_tpl->getVariable('pictures',null,true,false)->value[$_smarty_tpl->getVariable('key',null,true,false)->value])&&$_smarty_tpl->getVariable('pictures')->value[$_smarty_tpl->getVariable('key')->value]){?>2<?php }else{ ?><?php echo intval($_smarty_tpl->tpl_vars['field']->value['required']);?>
<?php }?>;
<?php }} ?>

// Images
var img_prod_dir = '<?php echo $_smarty_tpl->getVariable('img_prod_dir')->value;?>
';
var combinationImages = new Array();
<?php  $_smarty_tpl->tpl_vars['combination'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['combinationId'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('combinationImages')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['combination']->key => $_smarty_tpl->tpl_vars['combination']->value){
 $_smarty_tpl->tpl_vars['combinationId']->value = $_smarty_tpl->tpl_vars['combination']->key;
?>
	combinationImages[<?php echo $_smarty_tpl->tpl_vars['combinationId']->value;?>
] = new Array();
	<?php  $_smarty_tpl->tpl_vars['image'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['combination']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['f_combinationImage']['index']=-1;
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['image']->key => $_smarty_tpl->tpl_vars['image']->value){
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['f_combinationImage']['index']++;
?>
		combinationImages[<?php echo $_smarty_tpl->tpl_vars['combinationId']->value;?>
][<?php echo $_smarty_tpl->getVariable('smarty')->value['foreach']['f_combinationImage']['index'];?>
] = <?php echo intval($_smarty_tpl->tpl_vars['image']->value['id_image']);?>
;
	<?php }} ?>
<?php }} ?>

combinationImages[0] = new Array();
<?php  $_smarty_tpl->tpl_vars['image'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('images')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['f_defaultImages']['index']=-1;
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['image']->key => $_smarty_tpl->tpl_vars['image']->value){
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['f_defaultImages']['index']++;
?>
	combinationImages[0][<?php echo $_smarty_tpl->getVariable('smarty')->value['foreach']['f_defaultImages']['index'];?>
] = <?php echo $_smarty_tpl->tpl_vars['image']->value['id_image'];?>
;
<?php }} ?>

// Translations
var doesntExist = '<?php echo smartyTranslate(array('s'=>'The product does not exist in this model. Please choose another.','js'=>1),$_smarty_tpl);?>
';
var doesntExistNoMore = '<?php echo smartyTranslate(array('s'=>'This product is no longer in stock','js'=>1),$_smarty_tpl);?>
';
var doesntExistNoMoreBut = '<?php echo smartyTranslate(array('s'=>'with those attributes but is available with others','js'=>1),$_smarty_tpl);?>
';
var uploading_in_progress = '<?php echo smartyTranslate(array('s'=>'Uploading in progress, please wait...','js'=>1),$_smarty_tpl);?>
';
var fieldRequired = '<?php echo smartyTranslate(array('s'=>'Please fill all required fields','js'=>1),$_smarty_tpl);?>
';


<?php if (isset($_smarty_tpl->getVariable('groups',null,true,false)->value)){?>
	// Combinations
	<?php  $_smarty_tpl->tpl_vars['combination'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['idCombination'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('combinations')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['combination']->key => $_smarty_tpl->tpl_vars['combination']->value){
 $_smarty_tpl->tpl_vars['idCombination']->value = $_smarty_tpl->tpl_vars['combination']->key;
?>
		addCombination(<?php echo intval($_smarty_tpl->tpl_vars['idCombination']->value);?>
, new Array(<?php echo $_smarty_tpl->tpl_vars['combination']->value['list'];?>
), <?php echo $_smarty_tpl->tpl_vars['combination']->value['quantity'];?>
, <?php echo $_smarty_tpl->tpl_vars['combination']->value['price'];?>
, <?php echo $_smarty_tpl->tpl_vars['combination']->value['ecotax'];?>
, <?php echo $_smarty_tpl->tpl_vars['combination']->value['id_image'];?>
, '<?php echo addslashes($_smarty_tpl->tpl_vars['combination']->value['reference']);?>
', <?php echo $_smarty_tpl->tpl_vars['combination']->value['unit_impact'];?>
);
	<?php }} ?>
	// Colors
	<?php if (count($_smarty_tpl->getVariable('colors')->value)>0){?>
		<?php if ($_smarty_tpl->getVariable('product')->value->id_color_default){?>var id_color_default = <?php echo intval($_smarty_tpl->getVariable('product')->value->id_color_default);?>
;<?php }?>
	<?php }?>
<?php }?>

//]]>
</script>

<?php $_template = new Smarty_Internal_Template(($_smarty_tpl->getVariable('tpl_dir')->value)."./breadcrumb.tpl", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php $_template->updateParentVariables(0);?><?php unset($_template);?>

<div id="primary_block" class="clearfix">
	<h2><?php echo smarty_modifier_escape($_smarty_tpl->getVariable('product')->value->name,'htmlall','UTF-8');?>
</h2>

	<?php if (isset($_smarty_tpl->getVariable('adminActionDisplay',null,true,false)->value)&&$_smarty_tpl->getVariable('adminActionDisplay')->value){?>
	<div id="admin-action">
		<p><?php echo smartyTranslate(array('s'=>'This product is not visible by your customers.'),$_smarty_tpl);?>

		<input type="hidden" id="admin-action-product-id" value="<?php echo $_smarty_tpl->getVariable('product')->value->id;?>
" />
		<input type="submit" value="<?php echo smartyTranslate(array('s'=>'publish'),$_smarty_tpl);?>
" class="exclusive" onclick="submitPublishProduct('<?php echo $_smarty_tpl->getVariable('base_dir')->value;?>
<?php echo $_GET['ad'];?>
', 0)"/>			
		<input type="submit" value="<?php echo smartyTranslate(array('s'=>'back'),$_smarty_tpl);?>
" class="exclusive" onclick="submitPublishProduct('<?php echo $_smarty_tpl->getVariable('base_dir')->value;?>
<?php echo $_GET['ad'];?>
', 1)"/>			
		</p>
		<div class="clear" ></div>
		<p id="admin-action-result"></p>
		</p>
	</div>
	<?php }?>
	
	<?php if (isset($_smarty_tpl->getVariable('confirmation',null,true,false)->value)&&$_smarty_tpl->getVariable('confirmation')->value){?>
	<p class="confirmation">
		<?php echo $_smarty_tpl->getVariable('confirmation')->value;?>

	</p>
	<?php }?>

	<!-- right infos-->
	<div id="pb-right-column">
		<!-- product img-->
		<div id="image-block">
		<?php if ($_smarty_tpl->getVariable('have_image')->value){?>
			<img src="<?php echo $_smarty_tpl->getVariable('link')->value->getImageLink($_smarty_tpl->getVariable('product')->value->link_rewrite,$_smarty_tpl->getVariable('cover')->value['id_image'],'large');?>
" <?php if ($_smarty_tpl->getVariable('jqZoomEnabled')->value){?>class="jqzoom" <?php }else{ ?> title="<?php echo smarty_modifier_escape($_smarty_tpl->getVariable('product')->value->name,'htmlall','UTF-8');?>
" alt="<?php echo smarty_modifier_escape($_smarty_tpl->getVariable('product')->value->name,'htmlall','UTF-8');?>
" <?php }?> id="bigpic" width="<?php echo $_smarty_tpl->getVariable('largeSize')->value['width'];?>
" height="<?php echo $_smarty_tpl->getVariable('largeSize')->value['height'];?>
" />
		<?php }else{ ?>
			<img src="<?php echo $_smarty_tpl->getVariable('img_prod_dir')->value;?>
<?php echo $_smarty_tpl->getVariable('lang_iso')->value;?>
-default-large.jpg" id="bigpic" alt="" title="<?php echo smarty_modifier_escape($_smarty_tpl->getVariable('product')->value->name,'htmlall','UTF-8');?>
" />
		<?php }?>
		</div>

		<?php if (count($_smarty_tpl->getVariable('images')->value)>0){?>
		<!-- thumbnails -->
		<div id="views_block" <?php if (count($_smarty_tpl->getVariable('images')->value)<2){?>class="hidden"<?php }?>>
		<?php if (count($_smarty_tpl->getVariable('images')->value)>3){?><span class="view_scroll_spacer"><a id="view_scroll_left" class="hidden" title="<?php echo smartyTranslate(array('s'=>'Other views'),$_smarty_tpl);?>
" href="javascript:{}"><?php echo smartyTranslate(array('s'=>'Previous'),$_smarty_tpl);?>
</a></span><?php }?>
		<div id="thumbs_list">
			<ul style="width: <?php echo smarty_function_math(array('equation'=>"width * nbImages",'width'=>80,'nbImages'=>count($_smarty_tpl->getVariable('images')->value)),$_smarty_tpl);?>
px" id="thumbs_list_frame">
				<?php  $_smarty_tpl->tpl_vars['image'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('images')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['image']->index=-1;
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['image']->key => $_smarty_tpl->tpl_vars['image']->value){
 $_smarty_tpl->tpl_vars['image']->index++;
 $_smarty_tpl->tpl_vars['image']->first = $_smarty_tpl->tpl_vars['image']->index === 0;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['thumbnails']['first'] = $_smarty_tpl->tpl_vars['image']->first;
?>
				<?php $_smarty_tpl->tpl_vars['imageIds'] = new Smarty_variable(($_smarty_tpl->getVariable('product')->value->id)."-".($_smarty_tpl->tpl_vars['image']->value['id_image']), null, null);?>
				<li id="thumbnail_<?php echo $_smarty_tpl->tpl_vars['image']->value['id_image'];?>
">
					<a href="<?php echo $_smarty_tpl->getVariable('link')->value->getImageLink($_smarty_tpl->getVariable('product')->value->link_rewrite,$_smarty_tpl->getVariable('imageIds')->value,'thickbox');?>
" rel="other-views" class="thickbox <?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['thumbnails']['first']){?>shown<?php }?>" title="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['image']->value['legend']);?>
">
						<img id="thumb_<?php echo $_smarty_tpl->tpl_vars['image']->value['id_image'];?>
" src="<?php echo $_smarty_tpl->getVariable('link')->value->getImageLink($_smarty_tpl->getVariable('product')->value->link_rewrite,$_smarty_tpl->getVariable('imageIds')->value,'medium');?>
" alt="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['image']->value['legend']);?>
" height="<?php echo $_smarty_tpl->getVariable('mediumSize')->value['height'];?>
" width="<?php echo $_smarty_tpl->getVariable('mediumSize')->value['width'];?>
" />
					</a>
				</li>
				<?php }} ?>
			</ul>
		</div>
		<?php if (count($_smarty_tpl->getVariable('images')->value)>3){?><a id="view_scroll_right" title="<?php echo smartyTranslate(array('s'=>'Other views'),$_smarty_tpl);?>
" href="javascript:{}"><?php echo smartyTranslate(array('s'=>'Next'),$_smarty_tpl);?>
</a><?php }?>
		</div>
		<?php }?>
		<?php if (count($_smarty_tpl->getVariable('images')->value)>1){?><p class="align_center clear"><span id="wrapResetImages" style="display:none;"><img src="<?php echo $_smarty_tpl->getVariable('img_dir')->value;?>
icon/cancel_16x18.gif" alt="<?php echo smartyTranslate(array('s'=>'Cancel'),$_smarty_tpl);?>
" width="16" height="18"/> <a id="resetImages" href="<?php echo $_smarty_tpl->getVariable('link')->value->getProductLink($_smarty_tpl->getVariable('product')->value);?>
" onclick="$('span#wrapResetImages').hide('slow');return (false);"><?php echo smartyTranslate(array('s'=>'Display all pictures'),$_smarty_tpl);?>
</a></span></p><?php }?>
		<!-- usefull links-->
		<ul id="usefull_link_block">
			<?php if ($_smarty_tpl->getVariable('HOOK_EXTRA_LEFT')->value){?><?php echo $_smarty_tpl->getVariable('HOOK_EXTRA_LEFT')->value;?>
<?php }?>
			<li><a href="javascript:print();"><?php echo smartyTranslate(array('s'=>'Print'),$_smarty_tpl);?>
</a><br class="clear" /></li>
			<?php if ($_smarty_tpl->getVariable('have_image')->value&&!$_smarty_tpl->getVariable('jqZoomEnabled')->value){?>
			<li><span id="view_full_size" class="span_link"><?php echo smartyTranslate(array('s'=>'View full size'),$_smarty_tpl);?>
</span></li>
			<?php }?>
		</ul>
	</div>

	<!-- left infos-->
	<div id="pb-left-column">
		<?php if ($_smarty_tpl->getVariable('product')->value->description_short||count($_smarty_tpl->getVariable('packItems')->value)>0){?>
		<div id="short_description_block">
			<?php if ($_smarty_tpl->getVariable('product')->value->description_short){?>
				<div id="short_description_content" class="rte align_justify"><?php echo $_smarty_tpl->getVariable('product')->value->description_short;?>
</div>
			<?php }?>
			<?php if ($_smarty_tpl->getVariable('product')->value->description){?>
			<p class="buttons_bottom_block"><a href="javascript:{}" class="button"><?php echo smartyTranslate(array('s'=>'More details'),$_smarty_tpl);?>
</a></p>
			<?php }?>
			<?php if (count($_smarty_tpl->getVariable('packItems')->value)>0){?>
				<h3><?php echo smartyTranslate(array('s'=>'Pack content'),$_smarty_tpl);?>
</h3>
				<?php  $_smarty_tpl->tpl_vars['packItem'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('packItems')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['packItem']->key => $_smarty_tpl->tpl_vars['packItem']->value){
?>
					<div class="pack_content">
						<?php echo $_smarty_tpl->tpl_vars['packItem']->value['pack_quantity'];?>
 x <a href="<?php echo $_smarty_tpl->getVariable('link')->value->getProductLink($_smarty_tpl->tpl_vars['packItem']->value['id_product'],$_smarty_tpl->tpl_vars['packItem']->value['link_rewrite'],$_smarty_tpl->tpl_vars['packItem']->value['category']);?>
"><?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['packItem']->value['name'],'htmlall','UTF-8');?>
</a>
						<p><?php echo $_smarty_tpl->tpl_vars['packItem']->value['description_short'];?>
</p>
					</div>
				<?php }} ?>
			<?php }?>
		</div>
		<?php }?>

		<?php if ($_smarty_tpl->getVariable('colors')->value){?>
		<!-- colors -->
		<div id="color_picker">
			<p><?php echo smartyTranslate(array('s'=>'Pick a color:','js'=>1),$_smarty_tpl);?>
</p>
			<div class="clear"></div>
			<ul id="color_to_pick_list">
			<?php  $_smarty_tpl->tpl_vars['color'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['id_attribute'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('colors')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['color']->key => $_smarty_tpl->tpl_vars['color']->value){
 $_smarty_tpl->tpl_vars['id_attribute']->value = $_smarty_tpl->tpl_vars['color']->key;
?>
				<li><a id="color_<?php echo intval($_smarty_tpl->tpl_vars['id_attribute']->value);?>
" class="color_pick" style="background: <?php echo $_smarty_tpl->tpl_vars['color']->value['value'];?>
;" onclick="updateColorSelect(<?php echo intval($_smarty_tpl->tpl_vars['id_attribute']->value);?>
);$('#wrapResetImages').show('slow');" title="<?php echo $_smarty_tpl->tpl_vars['color']->value['name'];?>
"><?php if (file_exists((($_smarty_tpl->getVariable('col_img_dir')->value).($_smarty_tpl->tpl_vars['id_attribute']->value)).('.jpg'))){?><img src="<?php echo $_smarty_tpl->getVariable('img_col_dir')->value;?>
<?php echo $_smarty_tpl->tpl_vars['id_attribute']->value;?>
.jpg" alt="<?php echo $_smarty_tpl->tpl_vars['color']->value['name'];?>
" width="20" height="20" /><?php }?></a></li>
			<?php }} ?>
			</ul>
			<div class="clear"></div>
		</div>
		<?php }?>

		<!-- add to cart form-->
		<form id="buy_block" action="<?php echo $_smarty_tpl->getVariable('link')->value->getPageLink('cart.php');?>
" method="post">

			<!-- hidden datas -->
			<p class="hidden">
				<input type="hidden" name="token" value="<?php echo $_smarty_tpl->getVariable('static_token')->value;?>
" />
				<input type="hidden" name="id_product" value="<?php echo intval($_smarty_tpl->getVariable('product')->value->id);?>
" id="product_page_product_id" />
				<input type="hidden" name="add" value="1" />
				<input type="hidden" name="id_product_attribute" id="idCombination" value="" />
			</p>

			<!-- prices -->
			<?php if ($_smarty_tpl->getVariable('product')->value->show_price&&!isset($_smarty_tpl->getVariable('restricted_country_mode',null,true,false)->value)){?>
			<p class="price">
				<?php if ($_smarty_tpl->getVariable('product')->value->on_sale){?>
					<img src="<?php echo $_smarty_tpl->getVariable('img_dir')->value;?>
onsale_<?php echo $_smarty_tpl->getVariable('lang_iso')->value;?>
.gif" alt="<?php echo smartyTranslate(array('s'=>'On sale'),$_smarty_tpl);?>
" class="on_sale_img"/>
					<span class="on_sale"><?php echo smartyTranslate(array('s'=>'On sale!'),$_smarty_tpl);?>
</span>
				<?php }elseif($_smarty_tpl->getVariable('product')->value->specificPrice&&$_smarty_tpl->getVariable('product')->value->specificPrice['reduction']){?>
					<span class="discount"><?php echo smartyTranslate(array('s'=>'Price lowered!'),$_smarty_tpl);?>
</span>
				<?php }?>
				<br />
				<span class="our_price_display">
				<?php if (!$_smarty_tpl->getVariable('priceDisplay')->value||$_smarty_tpl->getVariable('priceDisplay')->value==2){?>
					<span id="our_price_display"><?php echo Product::convertPrice(array('price'=>$_smarty_tpl->getVariable('product')->value->getPrice(true,@NULL)),$_smarty_tpl);?>
</span>
						<?php if ($_smarty_tpl->getVariable('tax_enabled')->value){?><?php echo smartyTranslate(array('s'=>'tax incl.'),$_smarty_tpl);?>
<?php }?>
				<?php }?>
				<?php if ($_smarty_tpl->getVariable('priceDisplay')->value==1){?>
					<span id="our_price_display"><?php echo Product::convertPrice(array('price'=>$_smarty_tpl->getVariable('product')->value->getPrice(false,@NULL)),$_smarty_tpl);?>
</span>
						<?php if ($_smarty_tpl->getVariable('tax_enabled')->value){?><?php echo smartyTranslate(array('s'=>'tax excl.'),$_smarty_tpl);?>
<?php }?>
				<?php }?>
				</span>
				<?php if ($_smarty_tpl->getVariable('priceDisplay')->value==2){?>
					<br />
					<span id="pretaxe_price"><span id="pretaxe_price_display"><?php echo Product::convertPrice(array('price'=>$_smarty_tpl->getVariable('product')->value->getPrice(false,@NULL)),$_smarty_tpl);?>
</span>&nbsp;<?php echo smartyTranslate(array('s'=>'tax excl.'),$_smarty_tpl);?>
</span>
				<?php }?>
				<br />
				</p>
				<?php if ($_smarty_tpl->getVariable('product')->value->specificPrice&&$_smarty_tpl->getVariable('product')->value->specificPrice['reduction']){?>
					<p id="old_price"><span class="bold">
					<?php if (!$_smarty_tpl->getVariable('priceDisplay')->value||$_smarty_tpl->getVariable('priceDisplay')->value==2){?>
						<span id="old_price_display"><?php echo Product::convertPrice(array('price'=>$_smarty_tpl->getVariable('product')->value->getPriceWithoutReduct()),$_smarty_tpl);?>
</span>
							<?php if ($_smarty_tpl->getVariable('tax_enabled')->value){?><?php echo smartyTranslate(array('s'=>'tax incl.'),$_smarty_tpl);?>
<?php }?>
					<?php }?>
					<?php if ($_smarty_tpl->getVariable('priceDisplay')->value==1){?>
						<span id="old_price_display"><?php echo Product::convertPrice(array('price'=>$_smarty_tpl->getVariable('product')->value->getPriceWithoutReduct(true)),$_smarty_tpl);?>
</span>
							<?php if ($_smarty_tpl->getVariable('tax_enabled')->value){?><?php echo smartyTranslate(array('s'=>'tax excl.'),$_smarty_tpl);?>
<?php }?>
					<?php }?>
					</span>
					</p>
				<?php }?>
				<?php if ($_smarty_tpl->getVariable('product')->value->specificPrice&&$_smarty_tpl->getVariable('product')->value->specificPrice['reduction_type']=='percentage'){?>
					<p id="reduction_percent"><?php echo smartyTranslate(array('s'=>'(price reduced by'),$_smarty_tpl);?>
 <span id="reduction_percent_display"><?php echo $_smarty_tpl->getVariable('product')->value->specificPrice['reduction']*100;?>
</span> %<?php echo smartyTranslate(array('s'=>')'),$_smarty_tpl);?>
</p>
				<?php }?>
				<?php if (count($_smarty_tpl->getVariable('packItems')->value)){?>
					<p class="pack_price"><?php echo smartyTranslate(array('s'=>'instead of'),$_smarty_tpl);?>
 <span style="text-decoration: line-through;"><?php echo Product::convertPrice(array('price'=>$_smarty_tpl->getVariable('product')->value->getNoPackPrice()),$_smarty_tpl);?>
</span></p>
					<br class="clear" />
				<?php }?>
				<?php if ($_smarty_tpl->getVariable('product')->value->ecotax!=0){?>
					<p class="price-ecotax"><?php echo smartyTranslate(array('s'=>'include'),$_smarty_tpl);?>
 <span id="ecotax_price_display"><?php echo Product::convertPrice(array('price'=>$_smarty_tpl->getVariable('product')->value->ecotax),$_smarty_tpl);?>
</span> <?php echo smartyTranslate(array('s'=>'for green tax'),$_smarty_tpl);?>
</p>
				<?php }?>
				<?php if (!empty($_smarty_tpl->getVariable('product')->value->unity)&&$_smarty_tpl->getVariable('unit_price')->value>0.000000){?>
					<p class="unit-price"><span id="unit_price_display"><?php echo Product::convertPrice(array('price'=>$_smarty_tpl->getVariable('unit_price')->value),$_smarty_tpl);?>
</span> <?php echo smartyTranslate(array('s'=>'per'),$_smarty_tpl);?>
 <?php echo smarty_modifier_escape($_smarty_tpl->getVariable('product')->value->unity,'htmlall','UTF-8');?>
</p>
				<?php }?>
			<?php }?>
			
			<?php if (isset($_smarty_tpl->getVariable('groups',null,true,false)->value)){?>
			<!-- attributes -->
			<div id="attributes">
			<?php  $_smarty_tpl->tpl_vars['group'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['id_attribute_group'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('groups')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['group']->key => $_smarty_tpl->tpl_vars['group']->value){
 $_smarty_tpl->tpl_vars['id_attribute_group']->value = $_smarty_tpl->tpl_vars['group']->key;
?>
			<?php if (count($_smarty_tpl->tpl_vars['group']->value['attributes'])){?>
			<p>
				<label for="group_<?php echo intval($_smarty_tpl->tpl_vars['id_attribute_group']->value);?>
"><?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['group']->value['name'],'htmlall','UTF-8');?>
 :</label>
				<?php $_smarty_tpl->tpl_vars["groupName"] = new Smarty_variable("group_".($_smarty_tpl->tpl_vars['id_attribute_group']->value), null, null);?>
				<select name="<?php echo $_smarty_tpl->getVariable('groupName')->value;?>
" id="group_<?php echo intval($_smarty_tpl->tpl_vars['id_attribute_group']->value);?>
" onchange="javascript:findCombination();<?php if (count($_smarty_tpl->getVariable('colors')->value)>0){?>$('#wrapResetImages').show('slow');<?php }?>;">
					<?php  $_smarty_tpl->tpl_vars['group_attribute'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['id_attribute'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['group']->value['attributes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['group_attribute']->key => $_smarty_tpl->tpl_vars['group_attribute']->value){
 $_smarty_tpl->tpl_vars['id_attribute']->value = $_smarty_tpl->tpl_vars['group_attribute']->key;
?>
						<option value="<?php echo intval($_smarty_tpl->tpl_vars['id_attribute']->value);?>
"<?php if ((isset($_GET[$_smarty_tpl->getVariable('groupName',null,true,false)->value])&&intval($_GET[$_smarty_tpl->getVariable('groupName')->value])==$_smarty_tpl->tpl_vars['id_attribute']->value)||$_smarty_tpl->tpl_vars['group']->value['default']==$_smarty_tpl->tpl_vars['id_attribute']->value){?> selected="selected"<?php }?> title="<?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['group_attribute']->value,'htmlall','UTF-8');?>
"><?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['group_attribute']->value,'htmlall','UTF-8');?>
</option>
					<?php }} ?>
				</select>
			</p>
			<?php }?>
			<?php }} ?>
			</div>
			<?php }?>

			<p id="product_reference" <?php if (isset($_smarty_tpl->getVariable('groups',null,true,false)->value)||!$_smarty_tpl->getVariable('product')->value->reference){?>style="display:none;"<?php }?>><label for="product_reference"><?php echo smartyTranslate(array('s'=>'Reference :'),$_smarty_tpl);?>
 </label><span class="editable"><?php echo smarty_modifier_escape($_smarty_tpl->getVariable('product')->value->reference);?>
</span></p>

			<?php if ($_smarty_tpl->getVariable('product')->value->minimal_quantity>1){?>
			<!-- minimal quantity JS -->
			<script type="text/javascript">
				var minimal_quantity = <?php echo $_smarty_tpl->getVariable('product')->value->minimal_quantity;?>
;
				
				function checkMinimalQuantity()
				{
					if ($('#quantity_wanted').val() < minimal_quantity)
					{
						$('#quantity_wanted').css('border', '1px solid red');
						$('#minimal_quantity_wanted_p').css('color', 'red');
					}
					else
					{
						$('#quantity_wanted').css('border', '1px solid #BDC2C9');
						$('#minimal_quantity_wanted_p').css('color', '#374853');
					}
				}
				
			</script>
			<?php }?>
			
			<!-- quantity wanted -->
			<p id="quantity_wanted_p"<?php if ((!$_smarty_tpl->getVariable('allow_oosp')->value&&$_smarty_tpl->getVariable('product')->value->quantity==0)||$_smarty_tpl->getVariable('virtual')->value||!$_smarty_tpl->getVariable('product')->value->available_for_order){?> style="display:none;"<?php }?>>
				<label><?php echo smartyTranslate(array('s'=>'Quantity :'),$_smarty_tpl);?>
</label>
				<input type="text" name="qty" id="quantity_wanted" class="text" value="<?php if (isset($_smarty_tpl->getVariable('quantityBackup',null,true,false)->value)){?><?php echo intval($_smarty_tpl->getVariable('quantityBackup')->value);?>
<?php }else{ ?><?php if ($_smarty_tpl->getVariable('product')->value->minimal_quantity>1){?><?php echo $_smarty_tpl->getVariable('product')->value->minimal_quantity;?>
<?php }else{ ?>1<?php }?><?php }?>" size="2" maxlength="3" <?php if ($_smarty_tpl->getVariable('product')->value->minimal_quantity>1){?>onkeyup="checkMinimalQuantity();"<?php }?> />
			</p>

			<!-- minimal quantity wanted -->
			<p id="minimal_quantity_wanted_p"<?php if ($_smarty_tpl->getVariable('product')->value->minimal_quantity<=1||!$_smarty_tpl->getVariable('product')->value->available_for_order){?> style="display:none;"<?php }?>><?php echo smartyTranslate(array('s'=>'You need add '),$_smarty_tpl);?>
<b><?php echo $_smarty_tpl->getVariable('product')->value->minimal_quantity;?>
</b><?php echo smartyTranslate(array('s'=>' quantities minimum for buy this product.'),$_smarty_tpl);?>
</p>
			<?php if ($_smarty_tpl->getVariable('product')->value->minimal_quantity>1){?>
			<script type="text/javascript">
				checkMinimalQuantity();
			</script>
			<?php }?>

			<!-- availability -->
			<p id="availability_statut"<?php if (($_smarty_tpl->getVariable('product')->value->quantity==0&&!$_smarty_tpl->getVariable('product')->value->available_later)||($_smarty_tpl->getVariable('product')->value->quantity!=0&&!$_smarty_tpl->getVariable('product')->value->available_now)||!$_smarty_tpl->getVariable('product')->value->available_for_order){?> style="display:none;"<?php }?>>
				<span id="availability_label"><?php echo smartyTranslate(array('s'=>'Availability:'),$_smarty_tpl);?>
</span>
				<span id="availability_value"<?php if ($_smarty_tpl->getVariable('product')->value->quantity==0){?> class="warning-inline"<?php }?>>
					<?php if ($_smarty_tpl->getVariable('product')->value->quantity==0){?><?php if ($_smarty_tpl->getVariable('allow_oosp')->value){?><?php echo $_smarty_tpl->getVariable('product')->value->available_later;?>
<?php }else{ ?><?php echo smartyTranslate(array('s'=>'This product is no longer in stock'),$_smarty_tpl);?>
<?php }?><?php }else{ ?><?php echo $_smarty_tpl->getVariable('product')->value->available_now;?>
<?php }?>
				</span>
			</p>

			<!-- number of item in stock -->
			<p id="pQuantityAvailable"<?php if ($_smarty_tpl->getVariable('display_qties')->value!=1||$_smarty_tpl->getVariable('product')->value->quantity<=0||!$_smarty_tpl->getVariable('product')->value->available_for_order){?> style="display:none;"<?php }?>>
				<span id="quantityAvailable"><?php echo intval($_smarty_tpl->getVariable('product')->value->quantity);?>
</span>
				<span <?php if ($_smarty_tpl->getVariable('product')->value->quantity>1){?> style="display:none;"<?php }?> id="quantityAvailableTxt"><?php echo smartyTranslate(array('s'=>'item in stock'),$_smarty_tpl);?>
</span>
				<span <?php if ($_smarty_tpl->getVariable('product')->value->quantity==1){?> style="display:none;"<?php }?> id="quantityAvailableTxtMultiple"><?php echo smartyTranslate(array('s'=>'items in stock'),$_smarty_tpl);?>
</span>
			</p>
			
			<!-- Out of stock hook -->
			<p id="oosHook"<?php if ($_smarty_tpl->getVariable('product')->value->quantity>0){?> style="display:none;"<?php }?>>
				<?php echo $_smarty_tpl->getVariable('HOOK_PRODUCT_OOS')->value;?>

			</p>

			<p class="warning_inline" id="last_quantities"<?php if (($_smarty_tpl->getVariable('product')->value->quantity>$_smarty_tpl->getVariable('last_qties')->value||$_smarty_tpl->getVariable('product')->value->quantity==0)||$_smarty_tpl->getVariable('allow_oosp')->value||!$_smarty_tpl->getVariable('product')->value->available_for_order){?> style="display:none;"<?php }?> ><?php echo smartyTranslate(array('s'=>'Warning: Last items in stock!'),$_smarty_tpl);?>
</p>

			<?php if ($_smarty_tpl->getVariable('product')->value->online_only){?>
				<p><?php echo smartyTranslate(array('s'=>'Online only'),$_smarty_tpl);?>
</p>
			<?php }?>
			
			<p<?php if ((!$_smarty_tpl->getVariable('allow_oosp')->value&&$_smarty_tpl->getVariable('product')->value->quantity==0)||!$_smarty_tpl->getVariable('product')->value->available_for_order||(isset($_smarty_tpl->getVariable('restricted_country_mode',null,true,false)->value)&&$_smarty_tpl->getVariable('restricted_country_mode')->value)){?> style="display:none;"<?php }?> id="add_to_cart" class="buttons_bottom_block"><input type="submit" name="Submit" value="<?php echo smartyTranslate(array('s'=>'Add to cart'),$_smarty_tpl);?>
" class="exclusive" /></p>
			<?php if ($_smarty_tpl->getVariable('HOOK_PRODUCT_ACTIONS')->value){?>
				<?php echo $_smarty_tpl->getVariable('HOOK_PRODUCT_ACTIONS')->value;?>

			<?php }?>
			<div class="clear"></div>
		</form>
		<?php if ($_smarty_tpl->getVariable('HOOK_EXTRA_RIGHT')->value){?><?php echo $_smarty_tpl->getVariable('HOOK_EXTRA_RIGHT')->value;?>
<?php }?>
	</div>
</div>

<?php if ($_smarty_tpl->getVariable('quantity_discounts')->value){?>
<!-- quantity discount -->
<ul class="idTabs">
	<li><a style="cursor: pointer" class="selected"><?php echo smartyTranslate(array('s'=>'Quantity discount'),$_smarty_tpl);?>
</a></li>
</ul>
<div id="quantityDiscount">
	<table class="std">
		<tr>
			<?php  $_smarty_tpl->tpl_vars['quantity_discount'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('quantity_discounts')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['quantity_discount']->key => $_smarty_tpl->tpl_vars['quantity_discount']->value){
?>
				<th><?php echo intval($_smarty_tpl->tpl_vars['quantity_discount']->value['quantity']);?>
 
				<?php if (intval($_smarty_tpl->tpl_vars['quantity_discount']->value['quantity'])>1){?>
					<?php echo smartyTranslate(array('s'=>'quantities'),$_smarty_tpl);?>

				<?php }else{ ?>
					<?php echo smartyTranslate(array('s'=>'quantity'),$_smarty_tpl);?>

				<?php }?>
				</th>
			<?php }} ?>
		</tr>
		<tr>
			<?php  $_smarty_tpl->tpl_vars['quantity_discount'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('quantity_discounts')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['quantity_discount']->key => $_smarty_tpl->tpl_vars['quantity_discount']->value){
?>
				<td>
				<?php if (intval($_smarty_tpl->tpl_vars['quantity_discount']->value['id_discount_type'])==1){?>
					-<?php echo floatval($_smarty_tpl->tpl_vars['quantity_discount']->value['value']);?>
%
				<?php }else{ ?>
					-<?php echo Product::convertPrice(array('price'=>floatval($_smarty_tpl->tpl_vars['quantity_discount']->value['real_value'])),$_smarty_tpl);?>

				<?php }?>
				</td>
			<?php }} ?>
		</tr>
	</table>
</div>
<?php }?>

<?php echo $_smarty_tpl->getVariable('HOOK_PRODUCT_FOOTER')->value;?>


<!-- description and features -->
<?php if ($_smarty_tpl->getVariable('product')->value->description||$_smarty_tpl->getVariable('features')->value||$_smarty_tpl->getVariable('accessories')->value||$_smarty_tpl->getVariable('HOOK_PRODUCT_TAB')->value||$_smarty_tpl->getVariable('attachments')->value){?>
<div id="more_info_block" class="clear">
	<ul id="more_info_tabs" class="idTabs idTabsShort">
		<?php if ($_smarty_tpl->getVariable('product')->value->description){?><li><a id="more_info_tab_more_info" href="#idTab1"><?php echo smartyTranslate(array('s'=>'More info'),$_smarty_tpl);?>
</a></li><?php }?>
		<?php if ($_smarty_tpl->getVariable('features')->value){?><li><a id="more_info_tab_data_sheet" href="#idTab2"><?php echo smartyTranslate(array('s'=>'Data sheet'),$_smarty_tpl);?>
</a></li><?php }?>
		<?php if ($_smarty_tpl->getVariable('attachments')->value){?><li><a id="more_info_tab_attachments" href="#idTab9"><?php echo smartyTranslate(array('s'=>'Download'),$_smarty_tpl);?>
</a></li><?php }?>
		<?php if (isset($_smarty_tpl->getVariable('accessories',null,true,false)->value)&&$_smarty_tpl->getVariable('accessories')->value){?><li><a href="#idTab4"><?php echo smartyTranslate(array('s'=>'Accessories'),$_smarty_tpl);?>
</a></li><?php }?>
		<?php echo $_smarty_tpl->getVariable('HOOK_PRODUCT_TAB')->value;?>

	</ul>
	<div id="more_info_sheets" class="sheets align_justify">
	<?php if ($_smarty_tpl->getVariable('product')->value->description){?>
		<!-- full description -->
		<div id="idTab1" class="rte"><?php echo $_smarty_tpl->getVariable('product')->value->description;?>
</div>
	<?php }?>
	<?php if ($_smarty_tpl->getVariable('features')->value){?>
		<!-- product's features -->
		<ul id="idTab2" class="bullet">
		<?php  $_smarty_tpl->tpl_vars['feature'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('features')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['feature']->key => $_smarty_tpl->tpl_vars['feature']->value){
?>
			<li><span><?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['feature']->value['name'],'htmlall','UTF-8');?>
</span> <?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['feature']->value['value'],'htmlall','UTF-8');?>
</li>
		<?php }} ?>
		</ul>
	<?php }?>
	<?php if ($_smarty_tpl->getVariable('attachments')->value){?>
		<ul id="idTab9" class="bullet">
		<?php  $_smarty_tpl->tpl_vars['attachment'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('attachments')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['attachment']->key => $_smarty_tpl->tpl_vars['attachment']->value){
?>
			<li><a href="<?php echo $_smarty_tpl->getVariable('base_dir')->value;?>
attachment.php?id_attachment=<?php echo $_smarty_tpl->tpl_vars['attachment']->value['id_attachment'];?>
"><?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['attachment']->value['name'],'htmlall','UTF-8');?>
</a><br /><?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['attachment']->value['description'],'htmlall','UTF-8');?>
</li>
		<?php }} ?>
		</ul>
	<?php }?>
	<?php if (isset($_smarty_tpl->getVariable('accessories',null,true,false)->value)&&$_smarty_tpl->getVariable('accessories')->value){?>
		<!-- accessories -->
		<ul id="idTab4" class="bullet">
			<div class="block products_block accessories_block clearfix">
				<div class="block_content">
					<ul>
					<?php  $_smarty_tpl->tpl_vars['accessory'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('accessories')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['accessory']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['accessory']->iteration=0;
 $_smarty_tpl->tpl_vars['accessory']->index=-1;
if ($_smarty_tpl->tpl_vars['accessory']->total > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['accessory']->key => $_smarty_tpl->tpl_vars['accessory']->value){
 $_smarty_tpl->tpl_vars['accessory']->iteration++;
 $_smarty_tpl->tpl_vars['accessory']->index++;
 $_smarty_tpl->tpl_vars['accessory']->first = $_smarty_tpl->tpl_vars['accessory']->index === 0;
 $_smarty_tpl->tpl_vars['accessory']->last = $_smarty_tpl->tpl_vars['accessory']->iteration === $_smarty_tpl->tpl_vars['accessory']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['accessories_list']['first'] = $_smarty_tpl->tpl_vars['accessory']->first;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['accessories_list']['last'] = $_smarty_tpl->tpl_vars['accessory']->last;
?>
						<?php $_smarty_tpl->tpl_vars['accessoryLink'] = new Smarty_variable($_smarty_tpl->getVariable('link')->value->getProductLink($_smarty_tpl->tpl_vars['accessory']->value['id_product'],$_smarty_tpl->tpl_vars['accessory']->value['link_rewrite'],$_smarty_tpl->tpl_vars['accessory']->value['category']), null, null);?>
						<li class="ajax_block_product <?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['accessories_list']['first']){?>first_item<?php }elseif($_smarty_tpl->getVariable('smarty')->value['foreach']['accessories_list']['last']){?>last_item<?php }else{ ?>item<?php }?> product_accessories_description">
							<h5><a href="<?php echo smarty_modifier_escape($_smarty_tpl->getVariable('accessoryLink')->value,'htmlall','UTF-8');?>
"><?php echo smarty_modifier_escape(smarty_modifier_truncate($_smarty_tpl->tpl_vars['accessory']->value['name'],22,'...',true),'htmlall','UTF-8');?>
</a></h5>
							<p class="product_desc">
								<a href="<?php echo smarty_modifier_escape($_smarty_tpl->getVariable('accessoryLink')->value,'htmlall','UTF-8');?>
" title="<?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['accessory']->value['legend'],'htmlall','UTF-8');?>
" class="product_image"><img src="<?php echo $_smarty_tpl->getVariable('link')->value->getImageLink($_smarty_tpl->tpl_vars['accessory']->value['link_rewrite'],$_smarty_tpl->tpl_vars['accessory']->value['id_image'],'medium');?>
" alt="<?php echo smarty_modifier_escape($_smarty_tpl->tpl_vars['accessory']->value['legend'],'htmlall','UTF-8');?>
" /></a>
								<a href="<?php echo smarty_modifier_escape($_smarty_tpl->getVariable('accessoryLink')->value,'htmlall','UTF-8');?>
" title="<?php echo smartyTranslate(array('s'=>'More'),$_smarty_tpl);?>
" class="product_description"><?php echo smarty_modifier_truncate(preg_replace('!<[^>]*?>!', ' ', $_smarty_tpl->tpl_vars['accessory']->value['description_short']),100,'...');?>
</a>
							</p>
							<p class="product_accessories_price">
								<?php if ($_smarty_tpl->tpl_vars['accessory']->value['show_price']&&!isset($_smarty_tpl->getVariable('restricted_country_mode',null,true,false)->value)){?><span class="price"><?php echo Product::displayWtPrice(array('p'=>$_smarty_tpl->tpl_vars['accessory']->value['price']),$_smarty_tpl);?>
</span><?php }?>
								<a class="button" href="<?php echo smarty_modifier_escape($_smarty_tpl->getVariable('accessoryLink')->value,'htmlall','UTF-8');?>
" title="<?php echo smartyTranslate(array('s'=>'View'),$_smarty_tpl);?>
"><?php echo smartyTranslate(array('s'=>'View'),$_smarty_tpl);?>
</a>
								<?php if ($_smarty_tpl->tpl_vars['accessory']->value['available_for_order']&&!isset($_smarty_tpl->getVariable('restricted_country_mode',null,true,false)->value)){?><a class="exclusive button ajax_add_to_cart_button" href="<?php echo $_smarty_tpl->getVariable('link')->value->getPageLink('cart.php');?>
?qty=1&amp;id_product=<?php echo intval($_smarty_tpl->tpl_vars['accessory']->value['id_product']);?>
&amp;token=<?php echo $_smarty_tpl->getVariable('static_token')->value;?>
&amp;add" rel="ajax_id_product_<?php echo intval($_smarty_tpl->tpl_vars['accessory']->value['id_product']);?>
" title="<?php echo smartyTranslate(array('s'=>'Add to cart'),$_smarty_tpl);?>
"><?php echo smartyTranslate(array('s'=>'Add to cart'),$_smarty_tpl);?>
</a><?php }?>
							</p>
						</li>
					<?php }} ?>
					</ul>
				</div>
			</div>
		</ul>
	<?php }?>
	<?php echo $_smarty_tpl->getVariable('HOOK_PRODUCT_TAB_CONTENT')->value;?>

	</div>
</div>
<?php }?>

<!-- Customizable products -->
<?php if ($_smarty_tpl->getVariable('product')->value->customizable){?>
	<ul class="idTabs">
		<li><a style="cursor: pointer"><?php echo smartyTranslate(array('s'=>'Product customization'),$_smarty_tpl);?>
</a></li>
	</ul>
	<div class="customization_block">
		<form method="post" action="<?php echo $_smarty_tpl->getVariable('customizationFormTarget')->value;?>
" enctype="multipart/form-data" id="customizationForm">
			<p>
				<img src="<?php echo $_smarty_tpl->getVariable('img_dir')->value;?>
icon/infos.gif" alt="Informations" />
				<?php echo smartyTranslate(array('s'=>'After saving your customized product, do not forget to add it to your cart.'),$_smarty_tpl);?>

				<?php if ($_smarty_tpl->getVariable('product')->value->uploadable_files){?><br /><?php echo smartyTranslate(array('s'=>'Allowed file formats are: GIF, JPG, PNG'),$_smarty_tpl);?>
<?php }?>
			</p>
			<?php if (intval($_smarty_tpl->getVariable('product')->value->uploadable_files)){?>
			<h2><?php echo smartyTranslate(array('s'=>'Pictures'),$_smarty_tpl);?>
</h2>
			<ul id="uploadable_files">
				<?php echo smarty_function_counter(array('start'=>0,'assign'=>'customizationField'),$_smarty_tpl);?>

				<?php  $_smarty_tpl->tpl_vars['field'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('customizationFields')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['customizationFields']['index']=-1;
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['field']->key => $_smarty_tpl->tpl_vars['field']->value){
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['customizationFields']['index']++;
?>
					<?php if ($_smarty_tpl->tpl_vars['field']->value['type']==0){?>
						<li class="customizationUploadLine<?php if ($_smarty_tpl->tpl_vars['field']->value['required']){?> required<?php }?>"><?php $_smarty_tpl->tpl_vars['key'] = new Smarty_variable(((('pictures_').($_smarty_tpl->getVariable('product')->value->id)).('_')).($_smarty_tpl->tpl_vars['field']->value['id_customization_field']), null, null);?>
							<?php if (isset($_smarty_tpl->getVariable('pictures',null,true,false)->value[$_smarty_tpl->getVariable('key',null,true,false)->value])){?><div class="customizationUploadBrowse"><img src="<?php echo $_smarty_tpl->getVariable('pic_dir')->value;?>
<?php echo $_smarty_tpl->getVariable('pictures')->value[$_smarty_tpl->getVariable('key')->value];?>
_small" alt="" /><a href="<?php echo $_smarty_tpl->getVariable('link')->value->getUrlWith('deletePicture',$_smarty_tpl->tpl_vars['field']->value['id_customization_field']);?>
"><img src="<?php echo $_smarty_tpl->getVariable('img_dir')->value;?>
icon/delete.gif" alt="<?php echo smartyTranslate(array('s'=>'delete'),$_smarty_tpl);?>
" class="customization_delete_icon" /></a></div><?php }?>
							<div class="customizationUploadBrowse"><input type="file" name="file<?php echo $_smarty_tpl->tpl_vars['field']->value['id_customization_field'];?>
" id="img<?php echo $_smarty_tpl->getVariable('customizationField')->value;?>
" class="customization_block_input <?php if (isset($_smarty_tpl->getVariable('pictures',null,true,false)->value[$_smarty_tpl->getVariable('key',null,true,false)->value])){?>filled<?php }?>" /><?php if ($_smarty_tpl->tpl_vars['field']->value['required']){?><sup>*</sup><?php }?>
							<div class="customizationUploadBrowseDescription"><?php if (!empty($_smarty_tpl->tpl_vars['field']->value['name'])){?><?php echo $_smarty_tpl->tpl_vars['field']->value['name'];?>
<?php }else{ ?><?php echo smartyTranslate(array('s'=>'Please select an image file from your hard drive'),$_smarty_tpl);?>
<?php }?></div></div>
						</li>
						<?php echo smarty_function_counter(array(),$_smarty_tpl);?>

					<?php }?>
				<?php }} ?>
			</ul>
			<?php }?>
			<div class="clear"></div>
			<?php if (intval($_smarty_tpl->getVariable('product')->value->text_fields)){?>
			<h2><?php echo smartyTranslate(array('s'=>'Texts'),$_smarty_tpl);?>
</h2>
			<ul id="text_fields">
				<?php echo smarty_function_counter(array('start'=>0,'assign'=>'customizationField'),$_smarty_tpl);?>

				<?php  $_smarty_tpl->tpl_vars['field'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('customizationFields')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['customizationFields']['index']=-1;
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['field']->key => $_smarty_tpl->tpl_vars['field']->value){
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['customizationFields']['index']++;
?>
					<?php if ($_smarty_tpl->tpl_vars['field']->value['type']==1){?>
						<li class="customizationUploadLine<?php if ($_smarty_tpl->tpl_vars['field']->value['required']){?> required<?php }?>"><?php $_smarty_tpl->tpl_vars['key'] = new Smarty_variable(((('textFields_').($_smarty_tpl->getVariable('product')->value->id)).('_')).($_smarty_tpl->tpl_vars['field']->value['id_customization_field']), null, null);?>
							<?php if (!empty($_smarty_tpl->tpl_vars['field']->value['name'])){?><?php echo $_smarty_tpl->tpl_vars['field']->value['name'];?>
<?php }?><?php if ($_smarty_tpl->tpl_vars['field']->value['required']){?><sup>*</sup><?php }?><textarea type="text" name="textField<?php echo $_smarty_tpl->tpl_vars['field']->value['id_customization_field'];?>
" id="textField<?php echo $_smarty_tpl->getVariable('customizationField')->value;?>
" rows="1" cols="40" class="customization_block_input" /><?php if (isset($_smarty_tpl->getVariable('textFields',null,true,false)->value[$_smarty_tpl->getVariable('key',null,true,false)->value])){?><?php echo stripslashes($_smarty_tpl->getVariable('textFields')->value[$_smarty_tpl->getVariable('key')->value]);?>
<?php }?></textarea>
						</li>
						<?php echo smarty_function_counter(array(),$_smarty_tpl);?>

					<?php }?>
				<?php }} ?>
			</ul>
			<?php }?>
			<p style="clear: left;" id="customizedDatas">
				<input type="hidden" name="quantityBackup" id="quantityBackup" value="" />
				<input type="hidden" name="submitCustomizedDatas" value="1" />
				<input type="button" class="button" value="<?php echo smartyTranslate(array('s'=>'Save'),$_smarty_tpl);?>
" onclick="javascript:saveCustomization()" />
			</p>
		</form>
		<p class="clear required"><sup>*</sup> <?php echo smartyTranslate(array('s'=>'required fields'),$_smarty_tpl);?>
</p>
	</div>
<?php }?>

<?php if (count($_smarty_tpl->getVariable('packItems')->value)>0){?>
	<div>
		<h2><?php echo smartyTranslate(array('s'=>'Pack content'),$_smarty_tpl);?>
</h2>
		<?php $_template = new Smarty_Internal_Template(($_smarty_tpl->getVariable('tpl_dir')->value)."./product-list.tpl", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
$_template->assign('products',$_smarty_tpl->getVariable('packItems')->value); echo $_template->getRenderedTemplate();?><?php $_template->updateParentVariables(0);?><?php unset($_template);?>
	</div>
<?php }?>

<?php }?>