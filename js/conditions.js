function acceptCGV(msg)
{
	//if (!getE('cgv').checked)
	if (!$('input#cgv:checked').length)
	{
		alert(msg);
		return false;
	}
	else
		return true;
}
