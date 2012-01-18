<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
<script type="text/javascript" src="./modules/tntcarrier/js/relais.js"></script>
<script type="text/javascript">
{literal}	
	
    $("input[name='id_carrier']").click(function() {
    getAjaxRelais($("input[name='id_carrier']:checked").val());
    });

	function getAjaxRelais(id)
	{
        if (document.getElementById("tr_carrier_relais"))
            {
                var node = document.getElementById("tr_carrier_relais").parentNode;
                var father = node.parentNode;
                father.removeChild(node);
                //return;
            }
		$.get(
			"./modules/tntcarrier/relaisColis.php?id_carrier="+id+"&idcart="+$("#cartRelaisColis").val(),
			function(response, status, xhr) 
			{
				/*if (status == "error") 
					$("#tr_carrier_relais").html(xhr.status + " " + xhr.statusText);*/
				$("#loadingRelais"+id).hide();
				if (status == 'success' && response != 'none')
				{
					$("#id_carrier"+id).parent().parent().after("<tr><td colspan='4' style='display:none' id='tr_carrier_relais'></td></tr>");
                    $("#tr_carrier_relais").html(response);
					$("#tr_carrier_relais").slideDown('slow');
					tntRCInitMap();
					tntRCgetCommunes();
				}
			}
		);
	}
	
	function displayHelpCarrier(src)
	{
		$("#tntHelpCarrier").css('height', $(document).height()+'px');
		$("#helpCarrierFrame").attr('src', src);
		$("#helpCarrierBlock").css('top', $(window).scrollTop()+'px');
		if ($(window).height() > 500)
		{
			var h = ($(window).height() - 520) / 2+'px';
			
			$("#helpCarrierBlock").css('margin-top', h);
		}
		else
			$("#HelpCarrierBlock").css('margin-top', '20px');
		$(".opc-main-block").css('position', 'static');
		$("#tntHelpCarrier").show();
	}
	
	function hideHelpCarrier()
	{
		$("#tntHelpCarrier").hide();
		$(".opc-main-block").css('position', 'relative');
	}
	
	function selectCities()
	{
		$.get(
			"./modules/tntcarrier/changeCity.php?city="+$("#citiesGuide").val()+"&id="+$("#cartRelaisColis").val(),
			function(response, status, xhr) 
			{
				/*if (status == "error") 
					$("#tr_carrier_relais").html(xhr.status + " " + xhr.statusText);*/
				if (status == 'success' && response != 'none')
				{
					window.location.href = $("#reload_link").val();
				}
				else
					return false;
			}
		);
	}
{/literal}
</script>
<div id="tntHelpCarrier" style="display:none;position:absolute;width:100%;top:0px;left:0px;background:url('./img/macFFBgHack.png')">
	<div id="helpCarrierBlock" style="text-align:center;position:relative">
		<div style="width:720px;margin:auto;background-color:white">
		<span style="cursor:pointer;color:blue;text-decoration:underline;" onclick="hideHelpCarrier()">{l s='Close' mod='tntcarrier'}</span><br/>
		<iframe id="helpCarrierFrame" style="height:500px;width:700px;border:none;margin-top:5px">
		</iframe>
		</div>
	</div>
</div>
<input type="hidden" id="cartRelaisColis" value="{$id_cart}" name="cartRelaisColis" />

{if isset($error)}
	<h3>{$error}</h3>
	{l s='Postal Code' mod='tntcarrier'} : {$postalCode}
	<select id="citiesGuide" style="width:130px" onchange="selectCities()">
		<option selected="selected">{l s='Choose' mod='tntcarrier'}</option>
	 {foreach from=$cities item=v}
		<option value='{$v}'>{$v}</option>
	 {/foreach}
	</select>
	{if isset($link)}
	<input type="hidden" value="{$redirect}" id="reload_link" name="reload_link"/>
	{/if}
{/if}