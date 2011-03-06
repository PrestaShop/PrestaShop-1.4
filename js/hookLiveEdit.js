var modules_list = new Array();
var hooks_list = new Array();
var hookable_list = new Array();	
	
$(document).ready(function(){
	
	getHookableList();
	
	$('.unregisterHook').unbind('click').click(function(){
		id = $(this).attr('id'); 
		$(this).parent().parent().parent().fadeOut('slow', function () {
			$(this).remove();
		});
		return false;
	});
		

	$('#cancelMove').unbind('click').click(function(){
		$('#'+cancelMove+'').sortable('cancel');
		return false;
	});
	$('#saveLiveEdit').unbind('click').click(function(){
		saveModulePosition();
		return false;
	});
	
	$('#fancy').fancybox({
			modal : true
			});
	
	$('.dndHook').each(function () {
		var id_hook = $(this).attr('id');
		var new_target = '';
		var old_target = '';
		var cancel = false;
		
		$('#'+id_hook+'').sortable({
			opacity : 0.5,
			cursor : 'move',

			connectWith: '.dndHook',
			receive: function(event, ui) {
				if (new_target == '')
					new_target = event.target.id;
			},
			start: function (event, ui) {
				new_target = ui.item[0].parentNode.id;
			},
			stop: function(event, ui) {
				
				if (cancel)
					$(this).sortable('cancel');
				else
				{
					old_target = event.target.id;
					cancelMove = old_target;
					if (new_target == '')
						new_target = old_target;
				}
			},
			change: function(event, ui) {
				new_target = $(ui.placeholder).parent().attr('id');
				ids = ui.item[0].id.split('_');
				if(in_array(hookable_list[new_target], ids[5]))
				{
     				cancel = false;
     				ui.placeholder.css({visibility : 'visible', border : '1px solid #72CB67', background : '#DFFAD3'});
     			}
     			else
 				{
 					ui.placeholder.css({visibility : 'visible', border : '1px solid #EC9B9B', background : '#FAE2E3'});
 					cancel = true;
 				}
    		}
		});
		$('#'+id_hook+'').disableSelection();
	});
});
function getHookableList()
{
	$("#fancy").attr('href', '#live_edit_load');
	$("#fancy").trigger("click");
	$.ajax({
       type: 'GET',
       url: baseDir + ad +'/ajax.php',
       async: true,
       dataType: 'json',
       data: 'ajax=true&getHookableList&hooks_list='+hooks_list+'&modules_list='+modules_list ,
       success: function(jsonData)
       {
			hookable_list = jsonData;
			//$("#fancy").close();
		},
  		error: function(XMLHttpRequest, textStatus, errorThrown) {
  			alert("TECHNICAL ERROR: unable to unregister hook \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);}
	});
}

function saveModulePosition()
{
	var str = '';
	for(var i = 0; i < hooks_list.length; i++)
	{
		str += '&'+hooks_list[i]+'=';
		$('#'+hooks_list[i]+' > .dndModule').each( function () {
			ids = $(this).attr('id').split('_');
			str += ids[1]+'_'+ids[3]+',';
		});
		str = str.substr(0 , str.length - 1);
	}
	$.ajax({
       type: 'GET',
       url: baseDir + ad +'/ajax.php',
       async: true,
       dataType: 'json',
       data: 'ajax=true&saveHook&hooks_list='+hooks_list+str ,
       success: function(jsonData)
       {
			$('#live_edit_feed_back').html('<div class="live_edit_feed_back_ok">'+saveOK+'</div>');
			$('#live_edit_feed_back').fadeIn('slow');
			setTimeout("hideFeedback()",5000);
		},
  		error: function(XMLHttpRequest, textStatus, errorThrown) {
  			alert("TECHNICAL ERROR: unable to unregister hook \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);}
	});

}

function hideFeedback()
{
	$('#live_edit_feed_back').fadeOut('slow', function (){
		$(this).html('')
		});
};

function in_array (tab, val) {
	for(var i = 0, l = tab.length; i < l; i++) {
		if(tab[i] == val) {
			return true;
		}
	}
	return false;
}
