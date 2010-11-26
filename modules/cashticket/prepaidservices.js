function toggleImediatPayment()
{
	if ($('#ct_business_type').val() == 'I')
		$('#imediat_payment').hide();
	else
		$('#imediat_payment').show();
}

$(document).ready(function() {
	toggleImediatPayment();
});

