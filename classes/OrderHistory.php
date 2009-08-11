<?php

/**
  * Orders histories class, OrderHistory.php
  * Orders histories management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		OrderHistory extends ObjectModel
{
	/** @var integer Order id */
	public 		$id_order;
	
	/** @var integer Order state id */
	public 		$id_order_state;
	
	/** @var integer Employee id for this history entry */
	public 		$id_employee;
	
	/** @var string Object creation date */
	public 		$date_add;

	/** @var string Object last modification date */
	public 		$date_upd;

	protected $tables = array ('order_history');
	
	protected	$fieldsRequired = array('id_order', 'id_order_state');
	protected	$fieldsValidate = array('id_order' => 'isUnsignedId', 'id_order_state' => 'isUnsignedId', 'id_employee' => 'isUnsignedId');

	protected 	$table = 'order_history';
	protected 	$identifier = 'id_order_history';

	public function getFields()
	{
		parent::validateFields();
		
		$fields['id_order'] = intval($this->id_order);
		$fields['id_order_state'] = intval($this->id_order_state);
		$fields['id_employee'] = intval($this->id_employee);
		$fields['date_add'] = pSQL($this->date_add);
				
		return $fields;
	}

	public function changeIdOrderState($new_order_state = NULL, $id_order)
	{
		if ($new_order_state != NULL)
		{
			Hook::updateOrderStatus(intval($new_order_state), intval($id_order));
			
			/* Best sellers */
			$newOS = new OrderState(intval($new_order_state));
			$oldOrderStatus = OrderHistory::getLastOrderState(intval($id_order));
			$cart = Cart::getCartByOrderId($id_order);
			if (Validate::isLoadedObject($cart))
				foreach ($cart->getProducts() as $product)
					/* If becoming logable => adding sale */
					if ($newOS->logable AND (!$oldOrderStatus OR !$oldOrderStatus->logable))
						ProductSale::addProductSale($product['id_product'], $product['quantity']);
					/* If becoming unlogable => removing sale */
					elseif (!$newOS->logable AND ($oldOrderStatus AND $oldOrderStatus->logable))
						ProductSale::removeProductSale($product['id_product'], $product['quantity']);
			
			$this->id_order_state = intval($new_order_state);
			
			/* Change invoice number of order ? */
			$newOS = new OrderState(intval($new_order_state));
			$order = new Order(intval($id_order));
			if (!Validate::isLoadedObject($newOS) OR !Validate::isLoadedObject($order))
				die(Tools::displayError('Invalid new order state'));
			
			/* The order is valid only if the invoice is available and the order is not cancelled */
			$order->valid = $newOS->logable;
			$order->update();

			if ($newOS->invoice AND !$order->invoice_number)
				$order->setInvoice();
			if ($newOS->delivery AND !$order->delivery_number)
				$order->setDelivery();
			Hook::postUpdateOrderStatus(intval($new_order_state), intval($id_order));
		}
	}

	static public function getLastOrderState($id_order)
	{
		$result = Db::getInstance()->getRow('
		SELECT `id_order_state`
		FROM `'._DB_PREFIX_.'order_history`
		WHERE `id_order` = '.intval($id_order).'
		ORDER BY `date_add` DESC, `id_order_history` DESC');
		if (!$result OR empty($result) OR !key_exists('id_order_state', $result))
			return false;
		return new OrderState(intval($result['id_order_state']));
	}

	public function addWithemail($autodate = true, $templateVars = false)
	{
		if (!parent::add($autodate))
			return false;
			
		$lastOrderState = $this->getLastOrderState($this->id_order);

		$result = Db::getInstance()->getRow('
			SELECT osl.`template`, c.`lastname`, c.`firstname`, osl.`name` AS osname, c.`email`
			FROM `'._DB_PREFIX_.'order_history` oh
				LEFT JOIN `'._DB_PREFIX_.'orders` o ON oh.`id_order` = o.`id_order`
				LEFT JOIN `'._DB_PREFIX_.'customer` c ON o.`id_customer` = c.`id_customer`
				LEFT JOIN `'._DB_PREFIX_.'order_state` os ON oh.`id_order_state` = os.`id_order_state`
				LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = o.`id_lang`)
			WHERE oh.`id_order_history` = '.intval($this->id).'
				AND os.`send_email` = 1');

		if (isset($result['template']) AND Validate::isEmail($result['email']))
		{
			$topic = $result['osname'];
			$data = array('{lastname}' => $result['lastname'], '{firstname}' => $result['firstname'], '{id_order}' => intval($this->id_order));
			if ($templateVars) $data = array_merge($data, $templateVars);
			$order = new Order(intval($this->id_order));
			$data['{total_paid}'] = Tools::displayPrice(floatval($order->total_paid), new Currency(intval($order->id_currency)), false, false);
			$data['{order_name}'] = sprintf("#%06d", intval($order->id));
			// additionnal links for download virtual product
			if ($virtualProducts = $order->getVirtualProducts() AND $this->id_order_state==_PS_OS_PAYMENT_)
			{
				global $smarty;
				$display = '';
				$assign = array();
				foreach ($virtualProducts AS $key => $virtualProduct)
				{
					$id_product_download = ProductDownload::getIdFromIdProduct($virtualProduct['product_id']);
					$product_download = new ProductDownload($id_product_download);
					$assign[$key]['name'] = $product_download->display_filename;
					$assign[$key]['link'] = $product_download->getTextLink(false, $virtualProduct['download_hash']);
					if ($virtualProduct['download_deadline'] != '0000-00-00 00:00:00')
						$assign[$key]['deadline'] = Tools::displayDate($virtualProduct['download_deadline'], $order->id_lang);
					if ($product_download->nb_downloadable != 0)
						$assign[$key]['downloadable'] = $product_download->nb_downloadable;
				}
				$smarty->assign('virtualProducts', $assign);
				$iso = Language::getIsoById(intval($order->id_lang));
				$links = $smarty->fetch(_PS_MAIL_DIR_.$iso.'/download-product.tpl');
				$tmpArray = array('{nbProducts}' => count($virtualProducts), '{virtualProducts}' => $links);
				$data = array_merge ($data, $tmpArray);
				global $_LANGMAIL;
				$subject = 'Virtual product to download';
				Mail::Send(intval($order->id_lang), 'download_product', ((is_array($_LANGMAIL) AND key_exists($subject, $_LANGMAIL)) ? $_LANGMAIL[$subject] : $subject), $data, $result['email'], $result['firstname'].' '.$result['lastname']);
			}

			if (Validate::isLoadedObject($order))
				Mail::Send(intval($order->id_lang), $result['template'], $topic, $data, $result['email'], $result['firstname'].' '.$result['lastname']);
		}
		
		if ($lastOrderState->id !== $this->id_order_state)
			Hook::postUpdateOrderStatus($this->id_order_state, intval($this->id_order));
		return true;
	}

}

?>
