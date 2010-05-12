var cs_serialScrollNbImagesDisplayed;
var cs_serialScrollNbImages;
var cs_serialScrollActualImagesIndex;

function cs_serialScrollFixLock(event, targeted, scrolled, items, position)
{
	serialScrollNbImages = $('#crossselling_list li:visible').length;
	serialScrollNbImagesDisplayed = 5;
	
	var leftArrow = position == 0 ? true : false;
	var rightArrow = position + serialScrollNbImagesDisplayed >= serialScrollNbImages ? true : false;
	
	$('a#crossselling_scroll_left').css('cursor', leftArrow ? 'default' : 'pointer').css('display', leftArrow ? 'none' : 'block').fadeTo(0, leftArrow ? 0 : 1);		
	$('a#crossselling_scroll_right').css('cursor', rightArrow ? 'default' : 'pointer').fadeTo(0, rightArrow ? 0 : 1).css('display', rightArrow ? 'none' : 'block');
	return true;
}

$(document).ready(function(){
//init the serialScroll for thumbs
	cs_serialScrollNbImages = $('#crossselling_list li').length;
	cs_serialScrollNbImagesDisplayed = 5;
	cs_serialScrollActualImagesIndex = 0;
	$('#crossselling_list').serialScroll({
		items:'li',
		prev:'a#crossselling_scroll_left',
		next:'a#crossselling_scroll_right',
		axis:'x',
		offset:0,
		stop:true,
		onBefore:cs_serialScrollFixLock,
		duration:300,
		step: 1,
		lazy:true,
		lock: false,
		force:false,
		cycle:false
	});
	$('#crossselling_list').trigger( 'goto', [middle-3] );
});
