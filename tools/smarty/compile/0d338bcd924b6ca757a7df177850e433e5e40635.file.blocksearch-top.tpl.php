<?php /* Smarty version Smarty-3.0.5, created on 2010-11-30 10:10:20
         compiled from "C:\Apache\htdocs\jquery/modules/blocksearch/blocksearch-top.tpl" */ ?>
<?php /*%%SmartyHeaderCode:34534cf4bf7c46a188-58925403%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '0d338bcd924b6ca757a7df177850e433e5e40635' => 
    array (
      0 => 'C:\\Apache\\htdocs\\jquery/modules/blocksearch/blocksearch-top.tpl',
      1 => 1290763358,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '34534cf4bf7c46a188-58925403',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<!-- Block search module TOP -->
<div id="search_block_top">
	<form method="get" action="<?php echo $_smarty_tpl->getVariable('link')->value->getPageLink('search.php');?>
" id="searchbox">
	<p>
		<label for="search_query"><!-- image on background --></label>
		<input type="hidden" name="orderby" value="position" />
		<input type="hidden" name="orderway" value="desc" />
		<input type="text" id="search_query" name="search_query" value="<?php if (isset($_GET['search_query'])){?><?php echo stripslashes(htmlentities($_GET['search_query'],$_smarty_tpl->getVariable('ENT_QUOTES')->value,'utf-8'));?>
<?php }?>" />
		<input type="submit" name="submit_search" value="<?php echo smartyTranslate(array('s'=>'Search','mod'=>'blocksearch'),$_smarty_tpl);?>
" class="button" />
	</p>
	</form>
</div>
<?php if ($_smarty_tpl->getVariable('instantsearch')->value){?>
	<script type="text/javascript">
/* <![CDATA[ */
		
		function tryToCloseInstantSearch() {
			if ($('#old_center_column').length > 0)
			{
				$('#center_column').remove();
				$('#old_center_column').attr('id', 'center_column');
				return false;
			}
		}
		
		instantSearchQueries = new Array();
		function stopInstantSearchQueries(){
			for(i=0;i<instantSearchQueries.length;i++) {
				instantSearchQueries[i].abort();
			}
			instantSearchQueries = new Array();
		}
		
		$("#search_query").keyup(function(){
			if($(this).val().length > 0){
				stopInstantSearchQueries();
				instantSearchQuery = $.ajax({
				url: '<?php if ($_smarty_tpl->getVariable('search_ssl')->value==1){?><?php echo $_smarty_tpl->getVariable('link')->value->getPageLink('search.php',true);?>
<?php }else{ ?><?php echo $_smarty_tpl->getVariable('link')->value->getPageLink('search.php');?>
<?php }?>',
				data: 'instantSearch=1&id_lang=<?php echo $_smarty_tpl->getVariable('cookie')->value->id_lang;?>
&q='+$(this).val(),
				dataType: 'html',
				success: function(data){
					if($("#search_query").val().length > 0)
					{
						tryToCloseInstantSearch();
						$('#center_column').attr('id', 'old_center_column');
						$('#old_center_column').after('<div id="center_column">'+data+'</div>');
						$("#instant_search_results a.close").click(function() {
							$("#search_query").val('');
							return tryToCloseInstantSearch();
						});
						return false;
					}
					else
						tryToCloseInstantSearch();
					}
				});
				instantSearchQueries.push(instantSearchQuery);
			}
			else
				tryToCloseInstantSearch();
		});
		
	</script>
<?php }elseif($_smarty_tpl->getVariable('ajaxsearch')->value){?>
	<script type="text/javascript">
		$('document').ready( function() {
			$("#search_query")
				.autocomplete(
					'<?php if ($_smarty_tpl->getVariable('search_ssl')->value==1){?><?php echo $_smarty_tpl->getVariable('link')->value->getPageLink('search.php',true);?>
<?php }else{ ?><?php echo $_smarty_tpl->getVariable('link')->value->getPageLink('search.php');?>
<?php }?>', {
						minChars: 3,
						max: 10,
						width: 500,
						selectFirst: false,
						scroll: false,
						dataType: "json",
						formatItem: function(data, i, max, value, term) {
							return value;
						},
						parse: function(data) {
							var mytab = new Array();
							for (var i = 0; i < data.length; i++) {
								mytab[mytab.length] = { 
									data: data[i], 
									value: data[i].cname + ' > ' + data[i].pname 
								};
							}
							return mytab;
						},
						extraParams: {
							ajaxSearch: 1,
							id_lang: <?php echo $_smarty_tpl->getVariable('cookie')->value->id_lang;?>

						}
					}
				)
				.result(function(event, data, formatted) {
					$('#search_query').val(data.pname);
					document.location.href = data.product_link;
				})
		});
		
		/* ]]> */
	</script>
<?php }?>
<!-- /Block search module TOP -->
