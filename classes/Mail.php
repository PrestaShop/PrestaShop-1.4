<?php

/**
  * Mail class, Mail.php
  * Mails management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */
  
include_once(_PS_SWIFT_DIR_.'Swift.php');
include_once(_PS_SWIFT_DIR_.'Swift/Connection/SMTP.php');
include_once(_PS_SWIFT_DIR_.'Swift/Connection/NativeMail.php');
include_once(_PS_SWIFT_DIR_.'Swift/Plugin/Decorator.php');

class Mail
{
	static public function Send($id_lang, $template, $subject, $templateVars, $to, $toName = NULL, $from = NULL, $fromName = NULL, $fileAttachment = NULL, $modeSMTP = NULL, $templatePath = _PS_MAIL_DIR_)
	{
		$configuration = Configuration::getMultiple(array('PS_SHOP_EMAIL', 'PS_MAIL_METHOD', 'PS_MAIL_SERVER', 'PS_MAIL_USER', 'PS_MAIL_PASSWD', 'PS_SHOP_NAME', 'PS_MAIL_SMTP_ENCRYPTION', 'PS_MAIL_SMTP_PORT', 'PS_MAIL_METHOD', 'PS_MAIL_TYPE'));
		if(!isset($configuration['PS_MAIL_SMTP_ENCRYPTION'])) $configuration['PS_MAIL_SMTP_ENCRYPTION'] = "off";
		if(!isset($configuration['PS_MAIL_SMTP_PORT'])) $configuration['PS_MAIL_SMTP_PORT'] = "default";
		
		if (!isset($from)) $from = $configuration['PS_SHOP_EMAIL'];
		if (!isset($fromName)) $fromName = $configuration['PS_SHOP_NAME'];

		if ((!empty($from) AND !Validate::isEmail($from)) OR (!empty($fromName) AND !Validate::isMailName($fromName)) OR 
		 (!is_array($to) AND !Validate::isEmail($to)) OR (!empty($toName) AND !Validate::isMailName($toName)) OR !is_array($templateVars) OR 
		 !Validate::isTplName($template) OR !Validate::isMailSubject($subject))
	 		die(Tools::displayError('Error: mail parameters are corrupted'));
		
		/* Construct multiple recipients list if needed */
		if (is_array($to))
		{
			$to_list = new Swift_RecipientList();
			foreach ($to as $key => $addr)
			{
				$to_name = NULL;
				$addr = trim($addr);
				if (!Validate::isEmail($addr))
					die(Tools::displayError('Error: mail parameters are corrupted'));
				if ($toName AND is_array($toName) AND Validate::isGenericName($toName[$key]))
					$to_name = $toName[$key];
				$to_list->addTo($addr, $to_name);
			}
			$to_plugin = $to[0];
			$to = $to_list;
		} else {
			/* Simple recipient, one address */
			$to_plugin = $to;
			$to = new Swift_Address($to, $toName);
		}
		try {
			/* Connect with the appropriate configuration */
			if (intval($configuration['PS_MAIL_METHOD']) == 2)
			{
				$connection = new Swift_Connection_SMTP($configuration['PS_MAIL_SERVER'], $configuration['PS_MAIL_SMTP_PORT'], ($configuration['PS_MAIL_SMTP_ENCRYPTION'] == "ssl") ? Swift_Connection_SMTP::ENC_SSL : (($configuration['PS_MAIL_SMTP_ENCRYPTION'] == "tls") ? Swift_Connection_SMTP::ENC_TLS : Swift_Connection_SMTP::ENC_OFF));
				$connection->setTimeout(4);
				if (!$connection)
					return false;
				if (!empty($configuration['PS_MAIL_USER']) AND !empty($configuration['PS_MAIL_PASSWD']))
				{
					$connection->setUsername($configuration['PS_MAIL_USER']);
					$connection->setPassword($configuration['PS_MAIL_PASSWD']);
				}
			}
			else
				$connection = new Swift_Connection_NativeMail();

			if (!$connection)
				return false;
			$swift = new Swift($connection);
			/* Get templates content */
			$iso = Language::getIsoById(intval($id_lang));
			if (!$iso)
				die (Tools::displayError('Error - No iso code for email !'));
			$template = $iso.'/'.$template;

				
			if (!file_exists($templatePath.$template.'.txt') OR !file_exists($templatePath.$template.'.html'))
				die(Tools::displayError('Error - The following email template is missing:').' '.$templatePath.$template.'.txt');
				
			$templateHtml = file_get_contents($templatePath.$template.'.html');
			$templateTxt = strip_tags(html_entity_decode(file_get_contents($templatePath.$template.'.txt'), NULL, 'utf-8'));
			include_once(dirname(__FILE__).'/../mails/'.$iso.'/lang.php');

			global $_LANGMAIL;
			/* Create mail and attach differents parts */
			$message = new Swift_Message('['.Configuration::get('PS_SHOP_NAME').'] '.((is_array($_LANGMAIL) AND key_exists($subject, $_LANGMAIL)) ? $_LANGMAIL[$subject] : $subject));
			$templateVars['{shop_logo}'] = (file_exists(_PS_IMG_DIR_.'logo.jpg')) ? $message->attach(new Swift_Message_Image(new Swift_File(_PS_IMG_DIR_.'logo.jpg'))) : '';
			$templateVars['{shop_name}'] = Configuration::get('PS_SHOP_NAME');
			$templateVars['{shop_url}'] = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__;
			$swift->attachPlugin(new Swift_Plugin_Decorator(array($to_plugin => $templateVars)), 'decorator');
			if ($configuration['PS_MAIL_TYPE'] == 3 OR $configuration['PS_MAIL_TYPE'] == 2)
				$message->attach(new Swift_Message_Part($templateTxt, 'text/plain', '8bit', 'utf-8'));
			if ($configuration['PS_MAIL_TYPE'] == 3 OR $configuration['PS_MAIL_TYPE'] == 1)
				$message->attach(new Swift_Message_Part($templateHtml, 'text/html', '8bit', 'utf-8'));
			if ($fileAttachment AND isset($fileAttachment['content']) AND isset($fileAttachment['name']) AND isset($fileAttachment['mime']))
				$message->attach(new Swift_Message_Attachment($fileAttachment['content'], $fileAttachment['name'], $fileAttachment['mime']));
			/* Send mail */
			$send = $swift->send($message, $to, new Swift_Address($from, $fromName));
			$swift->disconnect();
			return $send;
		}
	
		catch (Swift_ConnectionException $e) { return false; }
	}
}
