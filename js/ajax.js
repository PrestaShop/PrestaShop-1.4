var query;
var lang = Array();

function setLang(array_lang) { lang = array_lang; }

function getQuery() {
 	var result;
 	
 	result = query;
 	if (result == null) {
 		if (window.XMLHttpRequest)
 			result = new XMLHttpRequest();
 		else if (window.ActiveXObject)
		 	result = new ActiveXObject('Microsoft.XMLHTTP');
 	}
 	return result;
}

function onQueryChange() {
 	if (query.readyState == 4 && query.status == 200)
 		document.getElementById('ajax_confirmation').innerHTML = '<span class="green bold">'+lang[0]+'</span>';
}

function request_failed() { alert(lang[1]); }

function showActivity() {
 	document.getElementById('ajax_confirmation').innerHTML = '<span class="bold">'+lang[2]+'</span>';
}

function check_for_all_accesses(tabsize, tabnumber)
{
	var i = 0;
	var res = 0;
	var right = 0;
	var rights = new Array('view', 'add', 'edit', 'delete', 'all'); 

	while (i != tabsize + 1)
	{
		if ($('#view'+i).attr('checked') == false || $('#edit'+i).attr('checked') == false || $('#add'+i).attr('checked') == false || $('#delete'+i).attr('checked') == false)
			$('#all'+i).attr('checked', false);
		else
			$('#all'+i).attr('checked', "checked");
		i++;
	}
	right = 0;
	while (right != 5)
	{
		res = 0;
		i = 0;
		while (i != tabsize)
		{
			if ($('#'+rights[right]+i).attr('checked') == true)
				res++;
			i++;
		}
		if (res == tabnumber - 1)
			$('#'+rights[right]+'all').attr('checked', "checked");
		else
			$('#'+rights[right]+'all').attr('checked', false);
		right++;
	}
}

function perfect_access_js_gestion(src, action, id_tab, tabsize, tabnumber)
{
 	if (id_tab == '-1' && action == 'all')
 	{
 		$('.add').attr('checked', src.checked);
 		$('.edit').attr('checked', src.checked);
 		$('.delete').attr('checked', src.checked);
		$('.view').attr('checked', src.checked);
		$('.all').attr('checked', src.checked);
		$('.31').attr('checked', "checked");
 	}
	else if (action == 'all')
		$('.'+id_tab).attr('checked', src.checked);
 	else if (id_tab == '-1')
 	{
 		$('.'+action).attr('checked', src.checked);
 		$('#'+action+'31').attr('checked', "checked");
 	}
	check_for_all_accesses(tabsize, tabnumber);
}

function ajax_power(src, action, id_tab, id_profile, token, tabsize, tabnumber)
{
	query = getQuery();
	perfect_access_js_gestion(src, action, id_tab, tabsize, tabnumber);
	if (query != null) {
	 	try {
		 	query.open('POST', 'index.php?tab=AdminAccess', true);
		 	query.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		 	query.onreadystatechange = onQueryChange;
		 	query.send('submitAddaccess=1&action='+action+'&perm='+parseInt(src.checked ? '1' : status = '0')+'&id_tab='+parseInt(id_tab)+'&id_profile='+parseInt(id_profile)+'&token='+token);
		 	showActivity();
		}
		catch(exc) {
			request_failed();
		}
	}
	else
		alert(lang[3]);
}

function redirect(new_page) { window.location = new_page; }
