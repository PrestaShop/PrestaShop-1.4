$(document).ready(function()
{
	$('#layered_form input[type=checkbox]').live('click', function()
	{
		$('#product_list').empty().html($('#layered_ajax_loader').html());
		
		$.ajax(
		{
			type: 'GET',
			url: baseDir + 'modules/blocklayered/blocklayered-ajax.php',
			data: $('#layered_form').serialize(),
			dataType: 'json',
			success: function(data)
			{
				$('#layered_block_left').after(data.layered_block_left).remove();
				$('#product_list').html(data.product_list);
			}
		});
	});
});