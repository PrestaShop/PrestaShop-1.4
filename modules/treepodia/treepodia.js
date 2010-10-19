function setPreview(path, name){
    $("#preview-logo").attr("src", path);
	$('#trpd_play_logo').val(name);
}

function toggleSelector()
{
	$('#selector').toggle('fast');
	$('#change-logo').hide();
	return false;
}

$(document).ready(function() {
	$('#change-logo').click(toggleSelector);
	$('#selector').hide();
});
