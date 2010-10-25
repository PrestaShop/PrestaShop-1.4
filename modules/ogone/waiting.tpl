<p><img src="{$base_dir}img/loader.gif" /> {l s='Please wait while your order is being processed...' mod='ogone'}</p>
<script type="text/javascript">
function checkwaitingorder()
{ldelim}
	$.ajax({ldelim}
		type:"POST",
		async:true,
		url:'{$base_dir}modules/ogone/checkwaitingorder.php',
		data:'id_cart={$id_cart|intval}&id_module={$id_module|intval}&key={$key|escape}',
		success:function (r) {ldelim}
			if (r == 'ok')
				window.location.href = '{$link->getPageLink('my-account.php')}?id_cart={$id_cart|intval}&id_module={$id_module|intval}&key={$key|escape}';
		{rdelim}
	{rdelim});
	setTimeout('checkwaitingorder()', 5000);
{rdelim}	
setTimeout('checkwaitingorder()', 5000);
</script>
