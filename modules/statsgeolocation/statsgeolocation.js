
/**
 * Display a waiting message while doing requests
 */
function wait()
{
	var wait = document.getElementById('wait').value;
	if (typeof(wait) == 'number') //should never appear...
		wait="wait...";
	document.getElementById("belowmap").innerHTML=wait;
}

/**
 * Launch the url page in background and write its content in the selected div
 */
function doAjax(dataform)
{
	$.ajax({
		url: '../modules/statsgeolocation/config.php',
		type: 'POST',
		data: dataform,
		dataType: 'html',
		error: function()
		{
			$("#belowmap").html("Cannot load countries' list");
		},
		success: function(data)
		{	
			$("#belowmap").html(data);
		}	
	});
}

/**
 * Show the cross in the map
 */
function displayMarker(x, y)
{
	var m = $("#marker").get(0);
	var size = $('#marker_size').val();

	m.style.display='';
	m.style.width=size+'px';
	m.style.height=size+'px';
	m.style.left=(x - (size/2))+'px';
	m.style.top=(y - (size/2))+'px';
	$("#form_x").val(x);
	$("#form_y").val(y);
}

/**
 * For jQuery to register events on the different buttons
 */
_registerClickButtons=function()
{
	var x=$('#hiddenx').val();
	var y=$('#hiddeny').val();
	if (x != -1 && y != -1)
		displayMarker(x, y);
	$('#selectinfo').html($('#lang_info').val());
	$('#cancel_id').val($('#lang_cancel').val());
	$('#validate_id').val($('#lang_validate').val());
	$("#cancel_id").click(function()
	{
		var dataform="opt=1&id_lang="+$('#id_lang').val();
		wait();
		$('#opt').val(1);
		document.getElementById("marker").style.display='none';
		doAjax(dataform);
	}); 
	$("#validate_id").click(function()
	{
		if (document.getElementById("marker").style.display == 'none')
			alert(document.getElementById("lang_error").value);
		else
		{
			var dataform="opt=3&id_lang="+$('#id_lang').val()+"&id_country="+document.getElementById("country_selected").value;
			dataform +="&x="+document.getElementById("form_x").value+"&y="+document.getElementById("form_y").value;
			wait();
			$('#opt').val(1);
			document.getElementById("marker").style.display='none';
			doAjax(dataform);
		}
	}); 
}

/**
 * For jQuery to register events while clicking on a country
 */
_registerClickOnCountry=function()
{
	var dataform="opt=2&id_lang="+$('#id_lang').val();
	$('#country_selected').val(0);
	
	$(".country").click(function()
	{
		wait();
		$('#country_selected').val($(this).attr("id")); 
		dataform += "&id_country="+$(this).attr("id");
		doAjax(dataform);
	}); 
}

/**
 * The first thing jQuery should do once the fist page is loaded
*/
_firstOfAll=function()
{
	var dataform="opt=1&id_lang=" + $('#id_lang').val();
	doAjax(dataform);
};

/**
 * This is what happens when someone is doing a mouse click on the map
 */
function clickOnImage(event)
{
	if ($("#country_selected").val() != '0')
	{
		var e = event || window.event;
		var pos = getRelativeCoordinates(event, $("#reference").get(0));
		var m = $("#marker").get(0);
		displayMarker(pos.x, pos.y);
	}
}

/**
 * Retrieve the absolute coordinates of an element.
 *
 * @param element
 *   A DOM element.
 * @return
 *   A hash containing keys 'x' and 'y'.
 */
function getAbsolutePosition(element)
{
	var r = { x: element.offsetLeft, y: element.offsetTop };
	if (element.offsetParent)
	{
		var tmp = getAbsolutePosition(element.offsetParent);
		r.x += tmp.x;
		r.y += tmp.y;
	}
	return r;
};

/**
 * Retrieve the coordinates of the given event relative to the center
 * of the widget.
 *
 * @param event
 *   A mouse-related DOM event.
 * @param reference
 *   A DOM element whose position we want to transform the mouse coordinates to.
 * @return
 *    A hash containing keys 'x' and 'y'.
 */
function getRelativeCoordinates(event, reference)
{
	var x, y;
	event = event || window.event;
	var el = event.target || event.srcElement;

	if (!window.opera && typeof event.offsetX != 'undefined')
	{
		// Use offset coordinates and find common offsetParent
		var pos = { x: event.offsetX, y: event.offsetY };

		// Send the coordinates upwards through the offsetParent chain.
		var e = el;
		while (e)
		{
			e.mouseX = pos.x;
			e.mouseY = pos.y;
			pos.x += e.offsetLeft;
			pos.y += e.offsetTop;
			e = e.offsetParent;
		}

		// Look for the coordinates starting from the reference element.
		var e = reference;
		var offset = { x: 0, y: 0 }
		while (e)
		{
			if (typeof e.mouseX != 'undefined')
			{
				x = e.mouseX - offset.x;
				y = e.mouseY - offset.y;
				break;
			}
			offset.x += e.offsetLeft;
			offset.y += e.offsetTop;
			e = e.offsetParent;
		}

		// Reset stored coordinates
		e = el;
		while (e)
		{
			e.mouseX = undefined;
			e.mouseY = undefined;
			e = e.offsetParent;
		}
	}
	else
	{
		// Use absolute coordinates
		var pos = getAbsolutePosition(reference);
		x = event.pageX  - pos.x;
		y = event.pageY - pos.y;
	}
	// Subtract distance to middle
	return { x: x, y: y };
}
