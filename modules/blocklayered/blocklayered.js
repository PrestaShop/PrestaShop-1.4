$(document).ready(function()
{
	$('#layered_block_left input[type=checkbox]').click(function()
	{
		$('#product_list').empty().html($('#layered_ajax_loader').html());

		$.ajax(
		{
			type: 'GET',
			url: baseDir + 'modules/blocklayered/blocklayered-ajax.php',
			data: $('#layered_form').serialize(),
			success: function(data)
			{
				$('#product_list').html(data);
			}
		});
	});
});