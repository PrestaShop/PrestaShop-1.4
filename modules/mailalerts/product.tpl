<!-- MODULE MailAlerts -->
{if $email}
	<input type="text" id="oos_customer_email" name="customer_email" size="20" value="{l s='your e-mail' mod='mailalerts'}" class="mailalerts_oos_email" onclick="clearText();" /><br />
{/if}
<a href="#" onclick="return addNotification();" id="mailalert_link">{l s='Notify me when available' mod='mailalerts'}</a>
<script type="text/javascript">{literal}
// <![CDATA[
oosHookJsCodeFunctions.push('oosHookJsCodeMailAlert');

function clearText() {
	if ($('#oos_customer_email').val() == 'your@email.com')
		$('#oos_customer_email').val('');
}

function oosHookJsCodeMailAlert() {
	$.ajax({
		type: 'POST',
		url: '{$base_dir}modules/mailalerts/mailalerts-ajax_check.php',
		data: 'id_product={$id_product}&id_product_attribute='+$('#idCombination').val(),
		success: function (msg) {
			if (msg == '0') {
				$('#mailalert_link').show().attr('href', 'modules/mailalerts/mailalerts-add.php?id_product={$id_product}&id_product_attribute='+$('#idCombination').val());
				$('#oos_customer_email').show();
			}
			else {
				$('#mailalert_link').hide();
				$('#oos_customer_email').hide();
			}
		}
	});
}

function  addNotification() {
	$.ajax({
		type: 'POST',
		url: '{$base_dir}modules/mailalerts/mailalerts-ajax_add.php',
		data: 'id_product={$id_product}&id_product_attribute='+$('#idCombination').val()+'&customer_email='+$('#oos_customer_email').val()+'',
		success: function (msg) {
			if (msg == '1') {
				$('#mailalert_link').hide();
				$('#oos_customer_email').hide();
				$('#oosHook').html("{l s='Request notification registered' mod='mailalerts'}");
			}
		}
	});
	return false;
}{/literal}
//]]>
</script>
<!-- END : MODULE MailAlerts -->
