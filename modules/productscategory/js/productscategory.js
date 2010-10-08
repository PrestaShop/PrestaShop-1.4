var pc_serialScrollNbImagesDisplayed;
var pc_serialScrollNbImages;
var pc_serialScrollActualImagesIndex;

function pc_serialScrollFixLock(event, targeted, scrolled, items, position)
{
	serialScrollNbImages = $('#productscategory_list li:visible').length;
	serialScrollNbImagesDisplayed = 5;
	
	var leftArrow = position == 0 ? true : false;
	var rightArrow = position + serialScrollNbImagesDisplayed >= serialScrollNbImages ? true : false;
	
	$('a#productscategory_scroll_left').css('cursor', leftArrow ? 'default' : 'pointer').fadeTo(0, leftArrow ? 0 : 1);		
	$('a#productscategory_scroll_right').css('cursor', rightArrow ? 'default' : 'pointer').fadeTo(0, rightArrow ? 0 : 1).css('display', rightArrow ? 'none' : 'block');
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
		lazy:true,
		lock: false,
		force:false,
		cycle:false
	});
	$('#productscategory_list').trigger( 'goto', [middle-3] );
});