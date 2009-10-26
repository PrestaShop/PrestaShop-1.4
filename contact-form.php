<?php

$useSSL = true;

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/header.php');

$errors = array();

$smarty->assign('contacts', Contact::getContacts(intval($cookie->id_lang)));

if (Tools::isSubmit('submitMessage'))
{
	if (!($from = Tools::getValue('from')) OR !Validate::isEmail($from))
        $errors[] = Tools::displayError('invalid e-mail address');
    elseif (!($message = nl2br2(Tools::getValue('message'))))
        $errors[] = Tools::displayError('message cannot be blank');
    elseif (!Validate::isMessage($message))
        $errors[] = Tools::displayError('invalid message');
    elseif (!($id_contact = intval(Tools::getValue('id_contact'))) OR !(Validate::isLoadedObject($contact = new Contact(intval($id_contact), intval($cookie->id_lang)))))
    	$errors[] = Tools::displayError('please select a contact in the list');
    else
    {
		if (intval($cookie->id_customer))
			$customer = new Customer(intval($cookie->id_customer));
		if (Mail::Send(intval($cookie->id_lang), 'contact', 'Message from contact form', array('{email}' => $_POST['from'], '{message}' => stripslashes($message)), $contact->email, $contact->name, $from, (intval($cookie->id_customer) ? $customer->firstname.' '.$customer->lastname : $from)))
			$smarty->assign('confirmation', 1);
		else
			$errors[] = Tools::displayError('an error occurred while sending message');
    }
}

$email = Tools::safeOutput(Tools::getValue('from', ((isset($cookie) AND isset($cookie->email) AND Validate::isEmail($cookie->email)) ? $cookie->email : '')));
$smarty->assign(array(
	'errors' => $errors,
	'email' => $email
));

$smarty->display(_PS_THEME_DIR_.'contact-form.tpl');
include(dirname(__FILE__).'/footer.php');

?>
