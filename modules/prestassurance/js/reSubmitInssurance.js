var ajaxQueries = new Array();

$(document).ready(function()
{

	$('.reSubmitSouscription').click(function () {
		reSubmitSouscription($(this).attr('id'));
	});
	
});


function reSubmitSouscription(id_order)
{
	//abort all ajaxQuery running
	for(i = 0; i < ajaxQueries.length; i++)
		ajaxQueries[i].abort();
	ajaxQueries = new Array();

	ajaxQuery = $.ajax({
		type: 'GET',
		url: ajax_url+'ajax.php',
		async: false,
		cache: false,
		dataType : "html",
		data: 'token='+token+'&reSubmitSouscription&id_order='+id_order ,
		success: function(htmlData)
		{
			$('#reSubmitSouscriptionContent').fadeOut('fast', function () {
				$(this).replaceWith(htmlData, function () {
				 	$(this).fadeIn('fast');
				 	})
				 });
		},
		error: function(XMLHttpRequest, textStatus, errorThrown)
		{
			alert('TECHNICAL ERROR\nDetails:\nError thrown: ' + XMLHttpRequest + '\n' + 'Text status: ' + textStatus);
		}
	});
	ajaxQueries.push(ajaxQuery);
	return false
}