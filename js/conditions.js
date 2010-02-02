function acceptCGV(msg)
{
	if (!getE('cgv').checked)
	{
		alert(msg);
		return false;
	}
	else
		return true;
}
