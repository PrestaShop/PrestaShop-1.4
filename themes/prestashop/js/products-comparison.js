function checkBeforeComparison()
{
	var id_list = '';
	$('.comparator:checked').each(
		function()
		{
			id_list += $(this).val() + '|';
		}
	);	
	
	$('#compare_product_list').val(id_list);
	
	if ($('.comparator:checked').length == 0)
	{
		alert(min_item);
		return false;
	}
	
	return true;
}

function checkForComparison(nb_max_item)
{
	if ($('.comparator:checked').length > nb_max_item)
		alert(max_item);
}
