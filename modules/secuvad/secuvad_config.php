<?php


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
	mail($secuvad->get_secuvad_contact(), $this->l('Hack Attempt'), 'secuvad_id='.$secuvad->get_secuvad_id()."\n".$this->l('No authorized access').' (ip='.$secuvad->getRemoteIPaddress().')'."\n".$this->l('The datas sent are:')."\n\n".'GET :'."\n".print_r($_GET,true)."\n\n".'POST :'."\n".print_r($_POST,true));


