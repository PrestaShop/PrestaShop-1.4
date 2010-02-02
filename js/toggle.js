function getE(name)
{
	if (document.getElementById)
		var elem = document.getElementById(name);
	else if (document.all)
		var elem = document.all[name];
	else if (document.layers)
		var elem = document.layers[name];
	return elem;
}

function toggleLayer(whichLayer, flag)
{
	var style = getE(whichLayer).style;
	style.display = (flag == '') ? 'none' : 'block';
}

function openCloseLayer(whichLayer, action)
{
 	var style = getE(whichLayer).style;
	if (!action)
		style.display = style.display == 'none' ? 'block' : 'none';
	else if (action == 'open')
		style.display = 'block';
	else if (action == 'close')
		style.display = 'none';
}