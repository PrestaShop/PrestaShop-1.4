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

function ajax_power(src, action, id_tab, id_profile, token)
{
	query = getQuery();
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