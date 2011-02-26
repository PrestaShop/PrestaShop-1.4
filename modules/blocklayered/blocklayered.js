$(document).ready(function()
{
	$('#layered_form span.layered_close a').live('click', function()
	{
		if ($(this).html() == '&lt;')
		{
			$('#'+$(this).attr('rel')).show();
			$(this).html('v');
		}
		else
		{
			$('#'+$(this).attr('rel')).hide();
			$(this).html('&lt;');
		}
	});
	
	$('#layered_form input[type=checkbox]').live('click', function()
	{
		$('#product_list').empty().html($('#layered_ajax_loader').html());
		
		var layeredCheckbox = $(this);
		
		$.ajax(
		{
			type: 'GET',
			url: baseDir + 'modules/blocklayered/blocklayered-ajax.php',
			data: $('#layered_form').serialize(),
			dataType: 'json',
			success: function(data)
			{
				$('#layered_block_left').after(data.layered_block_left).remove();				
				$('#enabled_filters ul').append('<a href="#" onclick="$(this).parent().remove();">x</a> '+$(layeredCheckbox).parent().parent().parent().find('span.layered_subtitle').html()+': <b>'+$(layeredCheckbox).parent().html().replace(/<span>.*<\/span>/gi, '').replace(/<\/?[^>]+>/gi, '')+'</b>');
				$('#enabled_filters').show();
				$('#enabled_filters').css('padding-bottom', '10px');
				$('#enabled_filters').css('margin-bottom', '5px');
				$('#enabled_filters').css('border-bottom', '1px dotted #CCC');
				$('#product_list').html(data.product_list);
			}
		});
	});
});