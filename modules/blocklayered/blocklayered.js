$(document).ready(function()
{
	$('#layered_block_left input[type=checkbox]').click(function(){
	
		$.ajax({
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