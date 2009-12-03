function updateAddress(phone)
{
	$.ajax({
		type: 'GET',
		url:  baseDir+'modules/reverso/reverso_check.php?phone=' + $('#reverso_form').val(),
		async: true,
		cache: false,
		type: 'text',
		success: function(data)
		{
			if (data == 0)
			{
				alert(unknown_number);
				return false;
			}
			else if (data == 11)
			{
				return false;
			}
			var fields = data.split(',');
			$(fields).each(function(){
				var field  = this.split(':');
				$('form#account-creation_form  input[name=\''+ field[0] +'\']').val(field[1]);
			});
		}
	});
	return false;
}

$(document).ready(function(){
	$('#reverso_form').typeWatch({
		callback:function(){ updateAddress();}, 
		wait:800, 
		highlight:false, 
		enterkey:false
	});
});
