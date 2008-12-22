<!-- MODULE MailAlerts -->
<a href="#" onclick="return addNotification();" id="mailalert_link">{l s='Notify me when available' mod='mailalerts'}</a>
<script type="text/javascript">
oosHookJsCodeFunctions.push('oosHookJsCodeMailAlert');
function oosHookJsCodeMailAlert()
{ldelim}
	$.ajax({ldelim}
		type: 'POST',
		url: '{$base_dir}modules/mailalerts/mailalerts-ajax_check.php',
		data: 'id_product={$id_product}&id_product_attribute='+$('#idCombination').val(),
		success: function (msg)
		{ldelim}
			if (msg == '0')
				$('#mailalert_link').show().attr('href', 'modules/mailalerts/mailalerts-add.php?id_product={$id_product}&id_product_attribute='+$('#idCombination').val());
			else
				$('#mailalert_link').hide();
		{rdelim}
	{rdelim});
{rdelim}

function  addNotification()
{ldelim}
	$.ajax({ldelim}
		type: 'POST',
		url: '{$base_dir}modules/mailalerts/mailalerts-ajax_add.php',
		data: 'id_product={$id_product}&id_product_attribute='+$('#idCombination').val(),
		success: function (msg)
		{ldelim}
			if (msg == '1')
				$('#mailalert_link').hide();
		{rdelim}
	{rdelim});
	return false;
{rdelim}
</script>
<!-- END : MODULE MailAlerts -->