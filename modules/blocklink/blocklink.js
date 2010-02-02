function linkEdition(linkId)
{
 	getE('id').value = linkId;
 	getE('url').value = links[linkId][0];
 	getE('newWindow').checked = links[linkId][1];
	var beg = parseInt(getE('languageFirst').value);
 	for (var i = 0; i <= parseInt(getE('languageNb').value - 1); i++)
 		getE('textInput_'+ (beg + i)).value = links[linkId][i + 2];
 	getE('submitLinkUpdate').disabled = '';
 	getE('submitLinkUpdate').setAttribute('class', 'button');
 	/* ##### IE */
 	getE('submitLinkUpdate').setAttribute('className', 'button');
}

function linkDeletion(linkId)
{
 	document.location.replace(currentUrl+'&id='+linkId+'&token='+token);
}