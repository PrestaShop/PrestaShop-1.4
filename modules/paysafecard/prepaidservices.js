function toggleImediatPayment()
{
	if ($('#ct_business_type').val() == 'I')
		$('#imediat_payment').hide();
	else
		$('#imediat_payment').show();
}

function toggleSystemLogo(img_path)
{
	if ($('ct_system').val() == 'P')
		$('system_logo').attr('src', img_path + '');
	else 
		$('system_logo').attr('src', img_path + '');
}

$(document).ready(function() {
	toggleImediatPayment();
});