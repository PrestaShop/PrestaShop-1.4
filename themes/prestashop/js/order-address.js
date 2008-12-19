
//update the display of the addresses
function updateAddressesDisplay()
{
	// update content of delivery address
	updateAddressDisplay('delivery');

	// update content of invoice address
	//if addresses have to be equals...
	if ($('input[type=checkbox]#addressesAreEquals:checked').length == 1)
	{
		$('#address_invoice_form:visible').hide('fast');
		var txtInvoiceTitle = $('ul#address_invoice li.address_title').html();
		$('ul#address_invoice').html( $('ul#address_delivery').html() );
		$('ul#address_invoice li.address_title').html(txtInvoiceTitle);
	}
	else
	{
		$('#address_invoice_form:hidden').show('fast');
		updateAddressDisplay('invoice');
	}
}

function updateAddressDisplay(addressType)
{
	var idAddress = $('select#id_address_' + addressType + '').val();
	$('ul#address_' + addressType + ' li.address_company').html(addresses[idAddress][0]);
	if(addresses[idAddress][0] == '')
		$('ul#address_' + addressType + ' li.address_company').hide();
	else
		$('ul#address_' + addressType + ' li.address_company').show();
	$('ul#address_' + addressType + ' li.address_name').html(addresses[idAddress][1] + ' ' + addresses[idAddress][2]);
	$('ul#address_' + addressType + ' li.address_address1').html(addresses[idAddress][3]);
	$('ul#address_' + addressType + ' li.address_address2').html(addresses[idAddress][4]);
	if(addresses[idAddress][4] == '')
		$('ul#address_' + addressType + ' li.address_address2').hide();
	else
		$('ul#address_' + addressType + ' li.address_address2').show();
	$('ul#address_' + addressType + ' li.address_city').html(addresses[idAddress][5] + ' ' + addresses[idAddress][6]);
	$('ul#address_' + addressType + ' li.address_country').html(addresses[idAddress][7] + (addresses[idAddress][8] != '' ? ' (' + addresses[idAddress][8] +')' : ''));
	// change update link
	var link = $('ul#address_' + addressType + ' li.address_update a').attr('href');
	var expression = /id_address=\d+/;
	link = link.replace(expression, 'id_address='+idAddress);
	$('ul#address_' + addressType + ' li.address_update a').attr('href', link);
}
