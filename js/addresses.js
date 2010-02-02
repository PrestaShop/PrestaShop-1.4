function changeAddress(flag)
{
	if (flag == 1)
	{
		var id = getE('id_address_delivery').value;
		var span = getE('displayDelivery');
	}
	else if (flag == 2)
	{
		var id = getE('id_address_invoice').value;
		var span = getE('displayInvoice');
	}
	span.innerHTML = '';
	if (addresses[id][0])
		span.innerHTML += addresses[id][0] + '<br />';
	span.innerHTML += addresses[id][1] + ' ' + addresses[id][2] + '<br />' + addresses[id][3] + '<br />';
	if (addresses[id][4])
		span.innerHTML += addresses[id][4] + '<br />';
	span.innerHTML += addresses[id][5] + ' ' + addresses[id][6] + '<br />';
	span.innerHTML += addresses[id][7] + '<br />';
	if (getE('same').value = 1)
		getE('displayInvoice').innerHTML = span.innerHTML;
}