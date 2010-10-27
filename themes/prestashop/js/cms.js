function submitPublishCMS(url, redirect)
{
	var id_cms = $('#admin-action-cms-id').val();

	$.ajaxSetup({async: false});
	$.post(url+'/ajax.php', { submitPublishCMS: '1', id_cms: id_cms, status: 1, redirect: redirect },
		function(data)
		{
			if (data.indexOf('error') === -1)
				document.location.href = data;
		}
	);

	return true;
}