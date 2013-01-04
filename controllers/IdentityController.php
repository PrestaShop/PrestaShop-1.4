<?php
/*
* 2007-2013 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class IdentityControllerCore extends FrontController
{
	public $auth = true;
	public $php_self = 'identity.php';
	public $authRedirection = 'identity.php';
	public $ssl = true;

	public function preProcess()
	{
		parent::preProcess();

		$customer = new Customer((int)self::$cookie->id_customer);

		if (isset($_POST['years']) && isset($_POST['months']) && isset($_POST['days']))
			$customer->birthday = (int)$_POST['years'].'-'.(int)$_POST['months'].'-'.(int)$_POST['days'];

		if (Tools::isSubmit('submitIdentity'))
		{
			if (Module::getInstanceByName('blocknewsletter')->active)
			{
				if (!isset($_POST['optin']))
					$customer->optin = 0;
				if (!isset($_POST['newsletter']))
					$customer->newsletter = 0;
			}
			if (!isset($_POST['id_gender']))
				$_POST['id_gender'] = 9;

			if (!@checkdate(Tools::getValue('months'), Tools::getValue('days'), Tools::getValue('years')) &&
			!(Tools::getValue('months') == '' && Tools::getValue('days') == '' && Tools::getValue('years') == ''))
				$this->errors[] = Tools::displayError('Invalid date of birth');
			else
			{
				$customer->birthday = (empty($_POST['years']) ? '' : (int)($_POST['years']).'-'.(int)($_POST['months']).'-'.(int)($_POST['days']));

				$id_customer_exists = (int)Customer::customerExists(Tools::getValue('email'), true, false);
				if ($id_customer_exists && $id_customer_exists != (int)self::$cookie->id_customer)
					$this->errors[] = Tools::displayError('An account is already registered with this e-mail.');

				$_POST['old_passwd'] = trim($_POST['old_passwd']);
				if (empty($_POST['old_passwd']) || (Tools::encrypt($_POST['old_passwd']) != self::$cookie->passwd))
					$this->errors[] = Tools::displayError('Your password is incorrect.');
				elseif ($_POST['passwd'] != $_POST['confirmation'])
					$this->errors[] = Tools::displayError('Password and confirmation do not match');
				else
				{
					$prev_id_default_group = $customer->id_default_group;
					$this->errors = array_unique(array_merge($this->errors, $customer->validateController(true, true)));
				}
				if (!count($this->errors))
				{
					$customer->id_default_group = (int)$prev_id_default_group;
					$customer->firstname = Tools::ucfirst(Tools::strtolower($customer->firstname));
					if (Tools::getValue('passwd'))
						self::$cookie->passwd = $customer->passwd;
					if ($customer->update())
					{
						self::$cookie->customer_lastname = $customer->lastname;
						self::$cookie->customer_firstname = $customer->firstname;
						self::$smarty->assign('confirmation', 1);
					}
					else
						$this->errors[] = Tools::displayError('Cannot update information');
				}
			}
		}
		else
			$_POST = array_map('stripslashes', $customer->getFields());

		$birthday = $customer->birthday ? explode('-', $customer->birthday) : array('-', '-', '-');

		/* Generate years, months and days */
		self::$smarty->assign(array('years' => Tools::dateYears(), 'sl_year' => $birthday[0], 'months' => Tools::dateMonths(),
		'sl_month' => $birthday[1], 'days' => Tools::dateDays(), 'sl_day' => $birthday[2], 'errors' => $this->errors));

		self::$smarty->assign('newsletter', (int)Module::getInstanceByName('blocknewsletter')->active);
	}

	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_.'identity.css');
	}

	public function displayContent()
	{
		parent::displayContent();
		self::$smarty->display(_PS_THEME_DIR_.'identity.tpl');
	}
}