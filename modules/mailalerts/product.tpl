<!-- MODULE MailAlerts -->
{if $email}
	<input type="text" id="oos_customer_email" name="customer_email" size="20" value="your@email.com" class="mailalerts_oos_email" onclick="clearText();" /><br />
{/if}
<a href="#" onclick="return addNotification();" id="mailalert_link">{l s='Notify me when available' mod='mailalerts'}</a>
<script type="text/javascript">
oosHookJsCodeFunctions.push('oosHookJsCodeMailAlert');

function clearText()
{ldelim}
	if ($('#oos_customer_email').val() == 'your@email.com')
		$('#oos_customer_email').val('');
{rdelim}

function oosHookJsCodeMailAlert()
{ldelim}
	$.ajax({ldelim}
		type: 'POST',
		url: '{$base_dir}modules/mailalerts/mailalerts-ajax_check.php',
		data: 'id_product={$id_product}&id_product_attribute='+$('#idCombination').val(),
		success: function (msg)
		{ldelim}
			if (msg == '0')
			{ldelim}
				$('#mailalert_link').show().attr('href', 'modules/mailalerts/mailalerts-add.php?id_product={$id_product}&id_product_attribute='+$('#idCombination').val());
				$('#oos_customer_email').show();
			{rdelim}
			else
			{ldelim}
				$('#mailalert_link').hide();
				$('#oos_customer_email').hide();
			{rdelim}
		{rdelim}
	{rdelim});
{rdelim}

function  addNotification()
{ldelim}
	$.ajax({ldelim}
		type: 'POST',
		url: '{$base_dir}modules/mailalerts/mailalerts-ajax_add.php',
		data: 'id_product={$id_product}&id_product_attribute='+$('#idCombination').val()+'&customer_email='+$('#oos_customer_email').val()+'',
		success: function (msg)
		{ldelim}
			if (msg == '1')
			{ldelim}
				$('#mailalert_link').hide();
				$('#oos_customer_email').hide();
			{rdelim}
		{rdelim}
	{rdelim});
	return false;
{rdelim}
</script>
<!-- END : MODULE MailAlerts -->