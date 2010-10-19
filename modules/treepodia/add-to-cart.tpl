{literal}
<script type="text/javascript">
<!-- 
    document.write(unescape("%3Cscript src='" + ((document.location.protocol == 'https:') ? 'https://' : 'http://') + "api.treepodia.com/video/Treepodia.js' type='text/javascript'%3E%3C/script%3E"));

	
	function sendProductToTreepodia(rel)
	{
		var reg = new RegExp('^ajax_id_product_([\\d+]*)$', 'g');
		var res = reg.exec(rel);
		
		if (res.lenght > 2)
			Treepodia.getProduct('{/literal}{$account_id}{literal}', res[1]).logAddToCart();
	}
	
	$(document).ready(function()
	{
		$('.ajax_add_to_cart_button').mouseup(function(){
			sendProductToTreepodia($(this).attr('rel'));
			return false;
		});
		
		//for product page 'add' button...
		$('body#product p#add_to_cart input').mouseup(function(){
			sendProductToTreepodia($(this).attr('rel'));
			return false;
		});
	});
// -->
</script>
{/literal}