<?php
/*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 14390 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once(dirname(__FILE__).'/../../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../../init.php');

include_once(_PS_MODULE_DIR_.'paypal/paypal.php');

define('TIMEOUT', 15);

define('INVALID', 'INVALID');
define('VERIFIED', 'VERIFIED');

/*
 * PayPal notification fields
 */
define('ID_INVOICE', 'invoice');
define('ID_PAYER', 'payer_id');
define('ID_TRANSACTION', 'txn_id');
define('CURRENCY', 'mc_currency');
define('PAYER_EMAIL', 'payer_email');
define('PAYMENT_DATE', 'payment_date');
define('TOTAL_PAID', 'mc_gross');
define('SHIPPING', 'shipping');
define('VERIFY_SIGN', 'verify_sign');

define('DEBUG_FILE', 'debug.log');

class PayPalNotifier extends PayPal {

	public function __construct()
	{
		parent::__construct();

		if (isset($_POST) && !empty($_POST))
		{
			$data = Tools::getValue('custom');
			$result = json_decode($data, true);

			$this->confirmOrder($result);
		}
	}

	private function confirmOrder($custom)
	{
		$cart = new Cart($custom['id_cart']);
		$cart_details = $cart->getSummaryDetails();
		$cart_hash = sha1(serialize($cart->getProducts()));

		$mc_gross = Tools::getValue('mc_gross');
		$total_price = $cart_details['total_price'];

		$result = $this->verify();

		if (strcmp($result, VERIFIED) == 0)
		{
			if (($mc_gross == $total_price) && ($custom['hash'] == $cart_hash))
			{
				$payment = Configuration::get('PS_OS_PAYMENT');
			}
			else
			{
				$payment = Configuration::get('PS_OS_ERROR');
			}

			$this->validateOrder($cart->id, $payment, $total_price, $this->displayName);
			$this->save($cart->id);
		}
	}

	public function save($id_cart)
	{
		$id_order = (int)Order::getOrderByCartId($id_cart);

		$transaction = array(
			'id_transaction' => pSQL(Tools::getValue(ID_TRANSACTION)),
			'id_invoice' => pSQL(Tools::getValue(ID_INVOICE)),
			'currency' => pSQL(Tools::getValue(CURRENCY)),
			'total_paid' => (float)Tools::getValue(TOTAL_PAID),
			'shipping' => (float)Tools::getValue(SHIPPING),
			'payment_date' => pSQL(Tools::getValue(PAYMENT_DATE))
		);

		$this->_saveTransaction($id_order, $transaction);
	}

	public function fetchResponse($url, $data)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, TIMEOUT);

		$result = curl_exec($ch);

		curl_close($ch);

		return $result;
	}

	public function verify()
	{
		$url = $this->getPaypalStandardUrl();
		$array = array_merge(array('cmd' => '_notify-validate'), $_POST);

		$data = http_build_query($array, '', '&');

		return $this->fetchResponse($url, $data);
	}

}

new PayPalNotifier();