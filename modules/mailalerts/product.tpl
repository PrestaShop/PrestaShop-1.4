<!-- MODULE MailAlerts -->
{if $email}
	<input type="text" id="oos_customer_email" name="customer_email" size="20" value="your@email.com" class="mailalerts_oos_email" onclick="clearText();" /><br />
{/if}
<a href="#" onclick="return addNotification();" id="mailalert_link">{l s='Notify me when available' mod='mailalerts'}</a>
<p id="oos_customer_email_result" style="display:none;"></p>
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
		url: '{/literal}{$base_dir}{literal}modules/mailalerts/mailalerts-ajax_check.php',
		data: 'id_product={/literal}{$id_product}{literal}&id_product_attribute='+$('#idCombination').val(),
		success: function (msg) {
			if (msg == '0') {
				$('#mailalert_link').show();
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
		url: '{/literal}{$base_dir}{literal}modules/mailalerts/mailalerts-ajax_add.php',
		data: 'id_product={/literal}{$id_product}{literal}&id_product_attribute='+$('#idCombination').val()+'&customer_email='+$('#oos_customer_email').val()+'',
		success: function (msg) {
			if (msg == '1') {
				$('#mailalert_link').hide();
				$('#oos_customer_email').hide();
				$('#oos_customer_email_result').html("{/literal}{l s='Request notification registered' mod='mailalerts'}{literal}");
				$('#oos_customer_email_result').css('color', 'green').show();
			}
			else {
				$('#oos_customer_email_result').html("{/literal}{l s='Your e-mail address is invalid' mod='mailalerts'}{literal}");
				$('#oos_customer_email_result').css('color', 'red').show();
			}
		}
	});
	return false;
}{/literal}
//]]>
</script>
<!-- END : MODULE MailAlerts -->
