/*
* Copyright (C) 2007-2010 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  Copyright (c) 2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

function changeAddress(flag)
{
	if (flag == 1)
	{
		var id = getE('id_address_delivery').value;
		var span = getE('displayDelivery');
	}
	else if (flag == 2)
	{
		var id = getE('id_address_invoice').value;
		var span = getE('displayInvoice');
	}
	span.innerHTML = '';
	if (addresses[id][0])
		span.innerHTML += addresses[id][0] + '<br />';
	span.innerHTML += addresses[id][1] + ' ' + addresses[id][2] + '<br />' + addresses[id][3] + '<br />';
	if (addresses[id][4])
		span.innerHTML += addresses[id][4] + '<br />';
	span.innerHTML += addresses[id][5] + ' ' + addresses[id][6] + '<br />';
	span.innerHTML += addresses[id][7] + '<br />';
	if (getE('same').value = 1)
		getE('displayInvoice').innerHTML = span.innerHTML;
}