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

{if isset($deliveryPointList)}
	<table width="100%">
	{foreach from=$deliveryPointList item=deliveryPoint}
		<tr>
			<td style="border-top: 0px; border-bottom: 1px solid #BDC2C9;"><input type="radio" name="deliveryPointSelected" value="{$deliveryPoint.identifiant}" {if $delivery_point eq $deliveryPoint.identifiant}checked="checked"{/if}></td>
			<td style="border-top: 0px; border-bottom: 1px solid #BDC2C9;"><img src="{$base_dir}/modules/socolissimoflex/carrier-delivery-point.jpg" /></td>
			<td width="80%" style="border-top: 0px; border-bottom: 1px solid #BDC2C9;">
				<b>{$deliveryPoint.nom}</b><br />
				{$deliveryPoint.adresse1}<br />
				{$deliveryPoint.codePostal} {$deliveryPoint.localite}<br />
			</td>
			<td style="border-top: 0px; border-bottom: 1px solid #BDC2C9;">Distance:<br />{$deliveryPoint.distanceEnMetre/1000}km</td>
		</tr>
	{/foreach}
	</table>
{else}
	<p>Aucun point relais disponible</p>
{/if}
