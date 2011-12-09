{*
* 2007-2011 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 6735 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<style>.tooltip{ position: absolute; top: 0; left: 0; z-index: 3; display: none; padding: 10px 13px; width: 300px; background-color: #ffffff; border: 1px solid #ff6600; }</style>
<script type="text/javascript" src="{$base_dir}/modules/socolissimoflex/jquery.simpletip-1.3.1.min.js"></script>
<script type="text/javascript">
var ajax_url = '{$base_dir}/modules/socolissimoflex/ajax.php';
var delivery_point_selected = '{$delivery_point_selected}';

var id_carrier_homedelivery = {$id_carrier_homedelivery};
var id_carrier_appdelivery = {$id_carrier_appdelivery};
var id_carrier_deliverypoint = {$id_carrier_deliverypoint};

var message_homedelivery = "<span style=\"color:#ff6600;font-weight:bold\">Mon domicile</span><br /><br />Livraison à l'adresse de votre choix en main propre (si remise avec signature) ou en boîtes aux lettres.<br /><br />Vous serez alerté par courriel 24h avant l'arrivée de votre colis. En cas d'abscence et/ou si le colis ne peut être inséré dans votre boîte aux lettres, il sera mis à votre disposition dans le bureau de poste auquel vous êtes rattaché.";
var message_appdelivery = "<span style=\"color:#ff6600;font-weight:bold\">Mon RDV</span><br /><br />Livraison à l'adresse de votre choix sur rendez-vous avec remise en main propre du colis du lundi au vendredi (uniquement proposé sur Paris).<br /><br />Dès que votre colis sera disponible vous recevrez un courriel et un sms vous invitant à choisir parmi les créneaux horaires suivants: de 17h à 18h30, de 18h30 à 20h, de 20h à 21h30.<br /><br />Vous pouvez également choisir de vous faire livrer un autre jour dans un délai de 10 jours ouvrables.";
var message_deliverypoint = "<span style=\"color:#ff6600;font-weight:bold\">Mon Bureau de Poste</span><br /><br />Livraison dans l'un des 10 000 principaux Bureaux de Poste ou Agence de Livraison La Poste que vous avez choisi (sans passage préalable de votre facteur à domicile).<br /><br />Dès que votre colis est disponible vous recevrez un sms et un courriel avec un bon de retrait vous permettant de venir le retirer dans les 10 jours ouvrables. Passé ce délai, le colis sera retourné à l'espéditeur.";

{literal}
function addDeliveryPointDiv()
{
	var deliveryPoint = '';
	deliveryPoint += '<tr><td colspan="4" id="deliveryPointSocoFlex" style="display:none;">';
	deliveryPoint += '<div id="deliveryPointListSocoFlex"></div>';
	deliveryPoint += '</td></tr>';
	$("#id_carrier"+id_carrier_deliverypoint).parent().parent().after(deliveryPoint);
}

function hideDeliveryPoint()
{
	$("#deliveryPointSocoFlex").hide();
}

function displayDeliveryPoint()
{
	$("#deliveryPointSocoFlex").show();
	$.ajax({
	  url: ajax_url,
	  success: function(msg) {
	    $('#deliveryPointListSocoFlex').html(msg);
	  }
	});
}

$(document).ready(function () {
	addDeliveryPointDiv();
	if (delivery_point_selected == 'true')
		displayDeliveryPoint();
	$("#id_carrier"+id_carrier_homedelivery).parent().parent().simpletip({content: message_homedelivery, fixed: false, offset: [150, 5] });
	$("#id_carrier"+id_carrier_appdelivery).parent().parent().simpletip({content: message_appdelivery, fixed: false, offset: [150, 5] });
	$("#id_carrier"+id_carrier_deliverypoint).parent().parent().simpletip({content: message_deliverypoint, fixed: false, offset: [150, 5] });
	$("#id_carrier"+id_carrier_homedelivery).parent().parent().click(function(){hideDeliveryPoint();});
	$("#id_carrier"+id_carrier_appdelivery).parent().parent().click(function(){hideDeliveryPoint();});
	$("#id_carrier"+id_carrier_deliverypoint).parent().parent().click(function(){displayDeliveryPoint();});
});
{/literal}
</script>
