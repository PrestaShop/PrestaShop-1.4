var pc_serialScrollNbImagesDisplayed;
var pc_serialScrollNbImages;
var pc_serialScrollActualImagesIndex;

function pc_serialScrollFixLock(event, targeted, scrolled, items, position){
	$('#productscategory_scroll_left').css('cursor', position == 0 ? 'default' : 'pointer').fadeTo(500, position == 0 ? 0.2 : 1);
	$('#productscategory_scroll_right').css('cursor', position + serialScrollNbImagesDisplayed == serialScrollNbImages ? 'default' : 'pointer').fadeTo(500, position + serialScrollNbImagesDisplayed == serialScrollNbImages ? 0.2 : 1);
	return true;
}

$(document).ready(function(){
//init the serialScroll for thumbs
	pc_serialScrollNbImages = $('#productscategory_list li').length;
	pc_serialScrollNbImagesDisplayed = 5;
	pc_serialScrollActualImagesIndex = 0;
	$('#productscategory_list').serialScroll({
		items:'li',
		prev:'a#productscategory_scroll_left',
		next:'a#productscategory_scroll_right',
		axis:'x',
		offset:0,
		stop:true,
		onBefore:pc_serialScrollFixLock,
		duration:300,
		step: 1,
		lock: false,
		force:false,
		cycle:false
	});
	$('#productscategory_list').trigger( 'goto', [middle-3] );
});
