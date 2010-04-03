function acceptCGV(msg)
{
	if ($('#cgv').length && !$('input#cgv:checked').length)
	{
		alert(msg);
		return false;
	}
	else
		return true;
}
