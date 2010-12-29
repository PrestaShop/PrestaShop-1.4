<?php
/*
* 2007-2010 PrestaShop 
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
*  @copyright  2007-2010 Prestashop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/


include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/secuvad.php');

$secuvad = new Secuvad();
if (in_array($secuvad->getRemoteIPaddress(), $secuvad->get_secuvad_ip_config()) AND sha1(Tools::getValue('p')) == $secuvad->get_secuvad_random())
{
	$value = stripslashes(Tools::getValue('value'));
	$key = stripslashes(Tools::getValue('key'));
    Configuration::updateValue($key, $value);
	echo 'OK';
}
else
	mail($secuvad->get_secuvad_contact(), $this->l('Hack Attempt'), 'secuvad_id='.$secuvad->get_secuvad_id()."\n".$this->l('No authorized access').' (ip='.$secuvad->getRemoteIPaddress().')'."\n".$this->l('The data sent is:')."\n\n".'GET :'."\n".print_r($_GET,true)."\n\n".'POST :'."\n".print_r($_POST,true));


