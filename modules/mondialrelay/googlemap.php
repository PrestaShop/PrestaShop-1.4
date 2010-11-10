
<script type="text/javascript" src="../../js/jquery/jquery-1.2.6.pack.js"></script>
<script type="text/javascript" src="../../js/jquery/jquery.easing.1.3.js"></script>
<script type="text/javascript" src="../../js/jquery/jquery.hotkeys-0.7.8-packed.js"></script>
<script type="text/javascript" src="../../js/jquery/jquery.autocomplete.js"></script>
<?php echo '<script type="text/javascript" src="http://www.google.com/jsapi?key='.$_GET['googlekey'].'"></script>
<script type="text/javascript">

function add_address_google_map(address, address_google)
{
		function createMarker(latlng)
		{
			var baseIcon = new GIcon(G_DEFAULT_ICON);
		    var letter = String.fromCharCode("M".charCodeAt(0));
			var letteredIcon = new GIcon(baseIcon);
   		 	letteredIcon.image = "http://www.google.com/mapfiles/markerM.png";
    		markerOptions = { icon:letteredIcon };

	    	var marker = new GMarker(latlng, markerOptions);
    	
	    	GEvent.addListener(marker, "click", function() {
	       		google_map_general.openInfoWindowHtml(latlng, \'<img src="'.$_GET['relativ_base_dir'].'modules/mondialrelay/kit_mondialrelay/MR_small.gif">\'+ \'  \' + address);
	      	});
    	  	return marker;
		}
		
		geocoder.getLatLng(address_google,
			function(point)
			{
				if (point)
					google_map_general.addOverlay(createMarker(point));
			}
		);
}

function recherche_MR(args)
{
	var ok = 1;
	if (ok == 1)
	{
		$.ajax({
			type: "POST",
			url: \'kit_mondialrelay/RecherchePointRelais_ajax.php\',
			data: args ,
			dataType: \'json\',
			async : false,
			success: function(obj)
				{
					json_addresses = obj;
				}
			});
	}
	else
	{
		alert(\'Formulaire incomplet\');
		return false;
	}
}

function google_map_init()
{
	google_map_general = new google.maps.Map2(document.getElementById("map"));
	geocoder = new GClientGeocoder();
	geocoder.getLatLng(\''.$_GET['address'].'\',
			function(point)
			{
				if (point)
				{
					google_map_general.setCenter(point, 11);
					var marker = new GMarker(point);
					google_map_general.addOverlay(marker);
					marker.openInfoWindowHtml(\''.$_GET['address'].'\');
				}
			}
	);
	google_map_general.addControl(new GSmallMapControl());
}
</script>

<div id="map" style="height:300px; width:500px; border:1px;" ></div>

<script type="text/javascript">
var json_addresses;
recherche_MR(\'relativ_base_dir='.$_GET['relativ_base_dir'].'&Pays='.$_GET['Pays'].'&Ville='.$_GET['Ville'].'&CP='.$_GET['CP'].'&Taille=&Poids='.$_GET['Poids'].'&Action='.$_GET['Action'].'&num='.$_GET['num'].'\');

	google.load("maps", "2.x");
	window.onload = function()
		{
			var cpt = 0;
			google_map_init();
			while (json_addresses.addresses[cpt])
			{
				address_google = json_addresses.addresses[cpt].address3+\' \'+json_addresses.addresses[cpt].postcode+\' \'+json_addresses.addresses[cpt].city+\' \'+json_addresses.addresses[cpt].iso_country;
				address = json_addresses.addresses[cpt].address1+\'<br />\'+json_addresses.addresses[cpt].address2+\' \'+json_addresses.addresses[cpt].address3+\'<br />\'+json_addresses.addresses[cpt].postcode+\' \'+json_addresses.addresses[cpt].city+\' \'+json_addresses.addresses[cpt].iso_country;
				add_address_google_map(address, address_google);
				cpt++;
			}
		}
</script>'

?>
