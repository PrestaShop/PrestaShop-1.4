<?php /* Smarty version Smarty-3.0.5, created on 2010-11-30 10:10:20
         compiled from "C:\Apache\htdocs\jquery/themes/prestashop/header.tpl" */ ?>
<?php /*%%SmartyHeaderCode:206754cf4bf7c689a95-89665836%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '823bb797e2bd54ae982a5577c547e2e2565e7bc5' => 
    array (
      0 => 'C:\\Apache\\htdocs\\jquery/themes/prestashop/header.tpl',
      1 => 1291072766,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '206754cf4bf7c689a95-89665836',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!is_callable('smarty_modifier_escape')) include 'C:\Apache\htdocs\jquery\tools\smarty\plugins\modifier.escape.php';
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $_smarty_tpl->getVariable('lang_iso')->value;?>
">
	<head>
		<title><?php echo smarty_modifier_escape($_smarty_tpl->getVariable('meta_title')->value,'htmlall','UTF-8');?>
</title>
<?php if (isset($_smarty_tpl->getVariable('meta_description',null,true,false)->value)&&$_smarty_tpl->getVariable('meta_description')->value){?>
		<meta name="description" content="<?php echo smarty_modifier_escape($_smarty_tpl->getVariable('meta_description')->value,'html','UTF-8');?>
" />
<?php }?>
<?php if (isset($_smarty_tpl->getVariable('meta_keywords',null,true,false)->value)&&$_smarty_tpl->getVariable('meta_keywords')->value){?>
		<meta name="keywords" content="<?php echo smarty_modifier_escape($_smarty_tpl->getVariable('meta_keywords')->value,'html','UTF-8');?>
" />
<?php }?>
		<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
		<meta name="generator" content="PrestaShop" />
		<meta name="robots" content="<?php if (isset($_smarty_tpl->getVariable('nobots',null,true,false)->value)){?>no<?php }?>index,follow" />
		<link rel="icon" type="image/vnd.microsoft.icon" href="<?php echo $_smarty_tpl->getVariable('img_ps_dir')->value;?>
favicon.ico" />
		<link rel="shortcut icon" type="image/x-icon" href="<?php echo $_smarty_tpl->getVariable('img_ps_dir')->value;?>
favicon.ico" />
		<script type="text/javascript">
			var baseDir = '<?php echo $_smarty_tpl->getVariable('content_dir')->value;?>
';
			var static_token = '<?php echo $_smarty_tpl->getVariable('static_token')->value;?>
';
			var token = '<?php echo $_smarty_tpl->getVariable('token')->value;?>
';
			var priceDisplayPrecision = <?php echo $_smarty_tpl->getVariable('priceDisplayPrecision')->value*$_smarty_tpl->getVariable('currency')->value->decimals;?>
;
			var priceDisplayMethod = '<?php echo $_smarty_tpl->getVariable('priceDisplay')->value;?>
';
			var roundMode = <?php echo $_smarty_tpl->getVariable('roundMode')->value;?>
;
		</script>
<?php if (isset($_smarty_tpl->getVariable('css_files',null,true,false)->value)){?>
	<?php  $_smarty_tpl->tpl_vars['media'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['css_uri'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('css_files')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['media']->key => $_smarty_tpl->tpl_vars['media']->value){
 $_smarty_tpl->tpl_vars['css_uri']->value = $_smarty_tpl->tpl_vars['media']->key;
?>
	<link href="<?php echo $_smarty_tpl->tpl_vars['css_uri']->value;?>
" rel="stylesheet" type="text/css" media="<?php echo $_smarty_tpl->tpl_vars['media']->value;?>
" />
	<?php }} ?>
<?php }?>
<?php if (isset($_smarty_tpl->getVariable('js_files',null,true,false)->value)){?>
	<?php  $_smarty_tpl->tpl_vars['js_uri'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('js_files')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['js_uri']->key => $_smarty_tpl->tpl_vars['js_uri']->value){
?>
	<script type="text/javascript" src="<?php echo $_smarty_tpl->tpl_vars['js_uri']->value;?>
"></script>
	<?php }} ?>
<?php }?>
		<?php echo $_smarty_tpl->getVariable('HOOK_HEADER')->value;?>

	</head>
	
	<body <?php if ($_smarty_tpl->getVariable('page_name')->value){?>id="<?php echo smarty_modifier_escape($_smarty_tpl->getVariable('page_name')->value,'htmlall','UTF-8');?>
"<?php }?>>
	<?php if (!$_smarty_tpl->getVariable('content_only')->value){?>
		<noscript><ul><li><?php echo smartyTranslate(array('s'=>'This shop requires JavaScript to run correctly. Please activate JavaScript in your browser.'),$_smarty_tpl);?>
</li></ul></noscript>
		<?php if (isset($_smarty_tpl->getVariable('restricted_country_mode',null,true,false)->value)&&$_smarty_tpl->getVariable('restricted_country_mode')->value){?>
		<div id="restricted-country">
			<p><?php echo smartyTranslate(array('s'=>'You can\'t make a new order from your country:'),$_smarty_tpl);?>
 <span class="bold"><?php echo $_smarty_tpl->getVariable('geolocalization_country')->value;?>
</span></p>
		</div>
		<?php }?>
		<div id="page">

			<!-- Header -->
			<div id="header">
				<h1 id="logo"><a href="<?php echo $_smarty_tpl->getVariable('base_dir')->value;?>
" title="<?php echo smarty_modifier_escape($_smarty_tpl->getVariable('shop_name')->value,'htmlall','UTF-8');?>
"><img src="<?php echo $_smarty_tpl->getVariable('img_ps_dir')->value;?>
logo.jpg" alt="<?php echo smarty_modifier_escape($_smarty_tpl->getVariable('shop_name')->value,'htmlall','UTF-8');?>
" <?php if ($_smarty_tpl->getVariable('logo_image_width')->value){?>width="<?php echo $_smarty_tpl->getVariable('logo_image_width')->value;?>
"<?php }?> <?php if ($_smarty_tpl->getVariable('logo_image_height')->value){?>height="<?php echo $_smarty_tpl->getVariable('logo_image_height')->value;?>
" <?php }?> /></a></h1>
				<div id="header_right">
					<?php echo $_smarty_tpl->getVariable('HOOK_TOP')->value;?>

				</div>
			</div>

			<div id="columns">
				<!-- Left -->
				<div id="left_column" class="column">
					<?php echo $_smarty_tpl->getVariable('HOOK_LEFT_COLUMN')->value;?>

				</div>

				<!-- Center -->
				<div id="center_column">
	<?php }?>
