<?php

/**
  * Orders tab for admin panel, AdminOrders.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminOrders extends AdminTab
{
	public function __construct()
	{
		global $cookie, $currentIndex;

	 	$this->table = 'order';
	 	$this->className = 'Order';
	 	$this->view = 'noActionColumn';
		$this->colorOnBackground = true;
	 	$this->_select = '
			a.id_order AS id_pdf,
			CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
			osl.`name` AS `osname`,
			os.`color`,
			IF((SELECT COUNT(so.id_order) FROM `'._DB_PREFIX_.'orders` so WHERE so.id_customer = a.id_customer AND so.valid = 1) > 1, 0, 1) as new,
			(SELECT COUNT(od.`id_order`) FROM `'._DB_PREFIX_.'order_detail` od WHERE od.`id_order` = a.`id_order` GROUP BY `id_order`) AS product_number';
	 	$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = a.`id_customer`)
	 	LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON (oh.`id_order` = a.`id_order`)
		LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = oh.`id_order_state`)
		LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.intval($cookie->id_lang).')';
		$this->_where = 'AND oh.`id_order_history` = (SELECT MAX(`id_order_history`) FROM `'._DB_PREFIX_.'order_history` moh WHERE moh.`id_order` = a.`id_order` GROUP BY moh.`id_order`)';

		$statesArray = array();
		$states = OrderState::getOrderStates(intval($cookie->id_lang));

		foreach ($states AS $state)
			$statesArray[$state['id_order_state']] = $state['name'];
 		$this->fieldsDisplay = array(
		'id_order' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'new' => array('title' => $this->l('New'), 'width' => 25, 'align' => 'center', 'type' => 'bool', 'filter_key' => 'new', 'tmpTableFilter' => true, 'icon' => array(0 => 'blank.gif', 1 => 'news-new.gif'), 'orderby' => false),
		'customer' => array('title' => $this->l('Customer'), 'widthColumn' => 160, 'width' => 140, 'filter_key' => 'customer', 'tmpTableFilter' => true),
		'total_paid' => array('title' => $this->l('Total'), 'width' => 70, 'align' => 'right', 'prefix' => '<b>', 'suffix' => '</b>', 'price' => true, 'currency' => true),
		'payment' => array('title' => $this->l('Payment'), 'width' => 100),
		'osname' => array('title' => $this->l('Status'), 'widthColumn' => 250, 'type' => 'select', 'select' => $statesArray, 'filter_key' => 'os!id_order_state', 'filter_type' => 'int', 'width' => 200),
		'date_add' => array('title' => $this->l('Date'), 'width' => 90, 'align' => 'right', 'type' => 'datetime', 'filter_key' => 'a!date_add'),
		'id_pdf' => array('title' => $this->l('PDF'), 'callback' => 'printPDFIcons', 'orderby' => false, 'search' => false));
		parent::__construct();
	}

	/**
	  * @global object $cookie Employee cookie necessary to keep trace of his/her actions
	  */
	public function postProcess()
	{
		global $currentIndex, $cookie;

		/* Update shipping number */
		if (Tools::isSubmit('submitShippingNumber') AND ($id_order = intval(Tools::getValue('id_order'))) AND Validate::isLoadedObject($order = new Order($id_order)))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (!$order->hasBeenShipped())
					die(Tools::displayError('The shipping number can only be set once the order has been shipped!'));
				$_GET['view'.$this->table] = true;
				if (!$shipping_number = pSQL(Tools::getValue('shipping_number')))
					$this->_errors[] = Tools::displayError('Invalid new order status!');
				else
				{
					global $_LANGMAIL;
					$order->shipping_number = $shipping_number;
					$order->update();
					$customer = new Customer(intval($order->id_customer));
					$carrier = new Carrier(intval($order->id_carrier));
					if (!Validate::isLoadedObject($customer) OR !Validate::isLoadedObject($carrier))
						die(Tools::displayError());
					$templateVars = array(
						'{followup}' => str_replace('@', $order->shipping_number, $carrier->url),
						'{firstname}' => $customer->firstname,
						'{lastname}' => $customer->lastname,
						'{id_order}' => intval($order->id)
					);
					$subject = 'Package in transit';
					Mail::Send(intval($order->id_lang), 'in_transit', ((is_array($_LANGMAIL) AND key_exists($subject, $_LANGMAIL)) ? $_LANGMAIL[$subject] : $subject), $templateVars, $customer->email, $customer->firstname.' '.$customer->lastname);
				}
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
		
		/* Change order state, add a new entry in order history and send an e-mail to the customer if needed */
		elseif (Tools::isSubmit('submitState') AND ($id_order = intval(Tools::getValue('id_order'))) AND Validate::isLoadedObject($order = new Order($id_order)))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				$_GET['view'.$this->table] = true;
				if (!$newOrderStatusId = intval(Tools::getValue('id_order_state')))
					$this->_errors[] = Tools::displayError('Invalid new order status!');
				else
				{
					$history = new OrderHistory();
					$history->id_order = $id_order;
					$history->changeIdOrderState(intval($newOrderStatusId), intval($id_order));
					$history->id_employee = intval($cookie->id_employee);
					$carrier = new Carrier(intval($order->id_carrier), intval($order->id_lang));
					$templateVars = array('{followup}' => ($history->id_order_state == _PS_OS_SHIPPING_ AND $order->shipping_number) ? str_replace('@', $order->shipping_number, $carrier->url) : '');
					if ($history->addWithemail(true, $templateVars))
						Tools::redirectAdmin($currentIndex.'&id_order='.$id_order.'&vieworder'.'&token='.$this->token);
					$this->_errors[] = Tools::displayError('an error occurred while changing status or was unable to send e-mail to the customer');
				}
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}

		/* Add a new message for the current order and send an e-mail to the customer if needed */
		elseif (isset($_POST['submitMessage']))
		{
			$_GET['view'.$this->table] = true;
		 	if ($this->tabAccess['edit'] === '1')
			{
				if (!($id_order = intval(Tools::getValue('id_order'))) OR !($id_customer = intval(Tools::getValue('id_customer'))))
					$this->_errors[] = Tools::displayError('an error occurred before sending message');
				elseif (!Tools::getValue('message'))
					$this->_errors[] = Tools::displayError('message cannot be blank');
				else
				{
					/* Get message rules and and check fields validity */
					$rules = call_user_func(array('Message', 'getValidationRules'), 'Message');
					foreach ($rules['required'] AS $field)
						if (($value = Tools::getValue($field)) == false AND (string)$value != '0')
							if (!Tools::getValue('id_'.$this->table) OR $field != 'passwd')
								$this->_errors[] = Tools::displayError('field').' <b>'.$field.'</b> '.Tools::displayError('is required');
					foreach ($rules['size'] AS $field => $maxLength)
						if (Tools::getValue($field) AND Tools::strlen(Tools::getValue($field)) > $maxLength)
							$this->_errors[] = Tools::displayError('field').' <b>'.$field.'</b> '.Tools::displayError('is too long').' ('.$maxLength.' '.Tools::displayError('chars max').')';
					foreach ($rules['validate'] AS $field => $function)
						if (Tools::getValue($field))
							if (!Validate::$function(htmlentities(Tools::getValue($field), ENT_COMPAT, 'UTF-8')))
								$this->_errors[] = Tools::displayError('field').' <b>'.$field.'</b> '.Tools::displayError('is invalid');
					if (!sizeof($this->_errors))
					{
						$message = new Message();
						$message->id_employee = intval($cookie->id_employee);
						$message->message = htmlentities(Tools::getValue('message'), ENT_COMPAT, 'UTF-8');
						$message->id_order = $id_order;
						$message->private = Tools::getValue('visibility');
						if (!$message->add())
							$this->_errors[] = Tools::displayError('an error occurred while sending message');
						elseif ($message->private)
							Tools::redirectAdmin($currentIndex.'&id_order='.$id_order.'&vieworder&conf=11'.'&token='.$this->token);
						elseif (Validate::isLoadedObject($customer = new Customer($id_customer)))
						{
							$order = new Order(intval($message->id_order));
							if (Validate::isLoadedObject($order))
							{
								$title = html_entity_decode($this->l('New message regarding your order').' '.$message->id_order, ENT_NOQUOTES, 'UTF-8');
								$varsTpl = array('{lastname}' => $customer->lastname, '{firstname}' => $customer->firstname, '{id_order}' => $message->id_order, '{message}' => (Configuration::get('PS_MAIL_TYPE') == 2 ? $message->message : nl2br2($message->message)));
								if (Mail::Send(intval($order->id_lang), 'order_merchant_comment', $title, $varsTpl, $customer->email, $customer->firstname.' '.$customer->lastname))
									Tools::redirectAdmin($currentIndex.'&id_order='.$id_order.'&vieworder&conf=11'.'&token='.$this->token);
							}
						}
						$this->_errors[] = Tools::displayError('an error occurred while sending e-mail to the customer');
					}
				}
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}

		/* Cancel product from order */
		elseif (Tools::isSubmit('cancelProduct') AND Validate::isLoadedObject($order = new Order(intval(Tools::getValue('id_order')))))
		{
		 	if ($this->tabAccess['delete'] === '1')
			{
				$productList = Tools::getValue('id_order_detail');
				$customizationList = Tools::getValue('id_customization');
				$qtyList = Tools::getValue('cancelQuantity');
				$customizationQtyList = Tools::getValue('cancelCustomizationQuantity');

				if ($productList OR $customizationList)
				{
					if ($productList)
						foreach ($productList AS $key => $id_order_detail)
						{
							$qtyCancelProduct = abs($qtyList[$key]);
							if (!$qtyCancelProduct)
								$this->_errors[] = Tools::displayError('No quantity selected for product.');
						}
					if ($customizationList)
						foreach ($customizationList AS $id_customization => $id_order_detail)
						{
							$qtyCancelProduct = abs($customizationQtyList[$id_customization]);
							if (!$qtyCancelProduct)
								$this->_errors[] = Tools::displayError('No quantity selected for product.');
						}

					if (!sizeof($this->_errors) AND $productList)
						foreach ($productList AS $key => $id_order_detail)
						{
							$qtyCancelProduct = abs($qtyList[$key]);
							$orderDetail = new OrderDetail(intval($id_order_detail));

							// Reinject product
							if (!$order->hasBeenDelivered() OR ($order->hasBeenDelivered() AND Tools::isSubmit('reinjectQuantities')))
							{
								$reinjectableQuantity = intval($orderDetail->product_quantity_in_stock) - intval($orderDetail->product_quantity_reinjected);
								$quantityToReinject = $qtyCancelProduct > $reinjectableQuantity ? $reinjectableQuantity : $qtyCancelProduct;
								if (!Product::reinjectQuantities($orderDetail, $quantityToReinject))
									$this->_errors[] = Tools::displayError('Cannot re-stock product').' <span class="bold">'.$orderDetail->product_name.'</span>';
							}

							// Delete product
							if (!$order->deleteProduct($order, $orderDetail, $qtyCancelProduct))
								$this->_errors[] = Tools::displayError('an error occurred during deletion for the product').' <span class="bold">'.$orderDetail->product_name.'</span>';
							Module::hookExec('cancelProduct', array('order' => $order, 'id_order_detail' => $id_order_detail));
						}
					if (!sizeof($this->_errors) AND $customizationList)
						foreach ($customizationList AS $id_customization => $id_order_detail)
						{
							$orderDetail = new OrderDetail(intval($id_order_detail));
							$qtyCancelProduct = abs($customizationQtyList[$id_customization]);
							if (!$order->deleteCustomization($id_customization, $qtyCancelProduct, $orderDetail))
								$this->_errors[] = Tools::displayError('an error occurred during deletion for the product customization').' '.$id_customization;
						}
					// E-mail params
					if ((isset($_POST['generateCreditSlip']) OR isset($_POST['generateDiscount'])) AND !sizeof($this->_errors))
					{
						$customer = new Customer(intval($order->id_customer));
						$params['{lastname}'] = $customer->lastname;
						$params['{firstname}'] = $customer->firstname;
						$params['{id_order}'] = $order->id;
					}
					
					// Generate credit slip
					if (isset($_POST['generateCreditSlip']) AND !sizeof($this->_errors))
					{
						if (!OrderSlip::createOrderSlip($order, $productList, $qtyList, isset($_POST['shippingBack'])))
							$this->_errors[] = Tools::displayError('Cannot generate credit slip');
						else
						{
							Module::hookExec('orderSlip', array('order' => $order, 'productList' => $productList, 'qtyList' => $qtyList));
							@Mail::Send(intval($order->id_lang), 'credit_slip', html_entity_decode($this->l('New credit slip regarding your order #').$order->id, ENT_NOQUOTES, 'UTF-8'), $params, $customer->email, $customer->firstname.' '.$customer->lastname);
						}
					}
					
					// Generate voucher
					if (isset($_POST['generateDiscount']) AND !sizeof($this->_errors))
					{
						if (!$voucher = Discount::createOrderDiscount($order, $productList, $qtyList, $this->l('Credit Slip concerning the order #'), isset($_POST['shippingBack'])))
							$this->_errors[] = Tools::displayError('Cannot generate voucher');
						else
						{
							$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
							$params['{voucher_amount}'] = Tools::displayPrice($voucher->value, $currency, false, false);
							$params['{voucher_num}'] = $voucher->name;
							@Mail::Send(intval($order->id_lang), 'voucher', html_entity_decode($this->l('New voucher regarding your order #').$order->id, ENT_NOQUOTES, 'UTF-8'), $params, $customer->email, $customer->firstname.' '.$customer->lastname);
						}
					}
				}
				else
					$this->_errors[] = Tools::displayError('No product or quantity selected.');

				// Redirect if no errors
				if (!sizeof($this->_errors))
					Tools::redirectLink($currentIndex.'&id_order='.$order->id.'&vieworder&conf=1&token='.$this->token);
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		elseif (isset($_GET['messageReaded']))
		{
			Message::markAsReaded(intval($_GET['messageReaded']), intval($cookie->id_employee));
		}
		parent::postProcess();
	}

	private function displayCustomizedDatas(&$customizedDatas, &$product, &$currency, &$image, $tokenCatalog, $id_order_detail)
	{
		$order = $this->loadObject();

		if (is_array($customizedDatas) AND isset($customizedDatas[intval($product['product_id'])][intval($product['product_attribute_id'])]))
		{
			echo '
			<tr>
				<td align="center">'.(isset($image['id_image']) ? cacheImage(_PS_IMG_DIR_.'p/'.intval($product['product_id']).'-'.intval($image['id_image']).'.jpg',
				'product_mini_'.intval($product['product_id']).(isset($product['product_attribute_id']) ? '_'.intval($product['product_attribute_id']) : '').'.jpg', 45, 'jpg') : '--').'</td>
				<td><a href="index.php?tab=AdminCatalog&id_product='.$product['product_id'].'&updateproduct&token='.$tokenCatalog.'">
					<span class="productName">'.$product['product_name'].' - '.$this->l('customized').'</span><br />
					'.($product['product_reference'] ? $this->l('Ref:').' '.$product['product_reference'] : '')
					.(($product['product_reference'] AND $product['product_supplier_reference']) ? ' / '.$product['product_supplier_reference'] : '')
					.'</a></td>
				<td align="center">'.Tools::displayPrice($product['product_price_wt'], $currency, false, false).'</td>
				<td align="center" class="productQuantity">'.$product['customizationQuantityTotal'].'</td>
				'.($order->hasBeenPaid() ? '<td align="center" class="productQuantity">'.$product['customizationQuantityRefunded'].'</td>' : '').'
				'.($order->hasBeenDelivered() ? '<td align="center" class="productQuantity">'.$product['customizationQuantityReturned'].'</td>' : '').'
				<td align="center" class="productQuantity"> - </td>
				<td align="center">'.Tools::displayPrice($product['total_customization_wt'], $currency, false, false).'</td>
				<td align="center" class="cancelCheck">--</td>
			</tr>';
			foreach ($customizedDatas[intval($product['product_id'])][intval($product['product_attribute_id'])] AS $customizationId => $customization)
			{
				echo '
				<tr>
					<td colspan="2">';
				foreach ($customization['datas'] AS $type => $datas)
					if ($type == _CUSTOMIZE_FILE_)
					{
						$i = 0;
						echo '<ul style="margin: 4px 0px 4px 0px; padding: 0px; list-style-type: none;">';
						foreach ($datas AS $data)
							echo '<li style="display: inline; margin: 2px;">
									<a href="displayImage.php?img='.$data['value'].'&name='.intval($order->id).'-file'.++$i.'" target="_blank"><img src="'._THEME_PROD_PIC_DIR_.$data['value'].'_small" alt="" /></a>
								</li>';
						echo '</ul>';
					}
					elseif ($type == _CUSTOMIZE_TEXTFIELD_)
					{
						$i = 0;
						echo '<ul style="margin: 0px 0px 4px 0px; padding: 0px 0px 0px 6px; list-style-type: none;">';
						foreach ($datas AS $data)
							echo '<li>'.$this->l('Text #').++$i.$this->l(':').' '.$data['value'].'</li>';
						echo '</ul>';
					}
				echo '</td>
					<td align="center">-</td>
					<td align="center" class="productQuantity">'.$customization['quantity'].'</td>
					'.($order->hasBeenPaid() ? '<td align="center">'.$customization['quantity_refunded'].'</td>' : '').'
					'.($order->hasBeenDelivered() ? '<td align="center">'.$customization['quantity_returned'].'</td>' : '').'
					<td align="center">-</td>
					<td align="center">'.Tools::displayPrice($product['product_price'] * (1 + ($product['tax_rate'] * 0.01)) * ($customization['quantity']), $currency, false, false).'</td>
					<td align="center" class="cancelCheck">
						<input type="hidden" name="totalQtyReturn" id="totalQtyReturn" value="'.intval($customization['quantity_returned']).'" />
						<input type="hidden" name="totalQty" id="totalQty" value="'.intval($customization['quantity']).'" />
						<input type="hidden" name="productName" id="productName" value="'.$product['product_name'].'" />';
				if ((!$order->hasBeenDelivered() OR Configuration::get('PS_ORDER_RETURN')) AND intval(($customization['quantity_returned']) < intval($customization['quantity'])))
					echo '
						<input type="checkbox" name="id_customization['.$customizationId.']" id="id_customization['.$customizationId.']" value="'.$id_order_detail.'" onchange="setCancelQuantity(this, \'_'.$customizationId.'\', 1)" '.((intval($customization['quantity_returned'] + $customization['quantity_refunded']) >= intval($customization['quantity'])) ? 'disabled="disabled" ' : '').'/>';
				else
					echo '--';
				echo '
					</td>
					<td class="cancelQuantity">';
				if (intval($customization['quantity_returned'] + $customization['quantity_refunded']) >= intval($customization['quantity']))
					echo '<input type="hidden" name="cancelCustomizationQuantity['.$customizationId.']" value="0" />';
				elseif (!$order->hasBeenDelivered() OR Configuration::get('PS_ORDER_RETURN'))
					echo '
						<input type="text" id="cancelQuantity_'.$customizationId.'" name="cancelCustomizationQuantity['.$customizationId.']" size="2" onclick="selectCheckbox(this);" value="" /> ';
				echo ($order->hasBeenDelivered() ? intval($customization['quantity_returned']).'/'.(intval($customization['quantity']) - intval($customization['quantity_refunded'])) : ($order->hasBeenPaid() ? intval($customization['quantity_refunded']).'/'.intval($customization['quantity']) : '')).'
					</td>';
				echo '
				</tr>';
			}
		}
	}

	private function getCancelledProductNumber(&$order, &$product)
	{
		$productQuantity = array_key_exists('customizationQuantityTotal', $product) ? $product['product_quantity'] - $product['customizationQuantityTotal'] : $product['product_quantity'];
		$productRefunded = $product['product_quantity_refunded'];
		$productReturned = $product['product_quantity_return'];
		$content = '0/'.$productQuantity;
		if ($order->hasBeenDelivered())
			$content = $productReturned.'/'.($productQuantity - $productRefunded);
		elseif ($order->hasBeenPaid())
			$content = $productRefunded.'/'.$productQuantity;
		return $content;
	}

	public function viewDetails()
	{
		global $currentIndex, $cookie;
		$irow = 0;
		$order = $this->loadObject();

		$customer = new Customer($order->id_customer);
		$customerStats = $customer->getStats();
		$addressInvoice = new Address($order->id_address_invoice, intval($cookie->id_lang));
		if (Validate::isLoadedObject($addressInvoice) AND $addressInvoice->id_state)
			$invoiceState = new State(intval($addressInvoice->id_state));
		$addressDelivery = new Address($order->id_address_delivery, intval($cookie->id_lang));
		if (Validate::isLoadedObject($addressDelivery) AND $addressDelivery->id_state)
			$deliveryState = new State(intval($addressDelivery->id_state));
		$carrier = new Carrier($order->id_carrier);
		$history = $order->getHistory($cookie->id_lang);
		$products = $order->getProducts();
		$customizedDatas = Product::getAllCustomizedDatas(intval($order->id_cart));
		Product::addCustomizationPrice($products, $customizedDatas);
		$discounts = $order->getDiscounts();
		$messages = Message::getMessagesByOrderId($order->id, true);
		$states = OrderState::getOrderStates(intval($cookie->id_lang));
		$currency = new Currency($order->id_currency);
		$currentLanguage = new Language(intval($cookie->id_lang));
		$currentState = OrderHistory::getLastOrderState($order->id);
		$sources = ConnectionsSource::getOrderSources($order->id);
		$cart = Cart::getCartByOrderId($order->id);
		$link = new Link();

		$row = array_shift($history);

		if ($order->total_paid != $order->total_paid_real)
			echo '<center><span class="warning" style="font-size: 16px">'.$this->l('Warning:').' '.Tools::displayPrice($order->total_paid_real, $currency, false, false).' '.$this->l('paid instead of').' '.Tools::displayPrice($order->total_paid, $currency, false, false).' !</span></center><div class="clear"><br /><br /></div>';

		// display bar code if module enabled
		$hook = Module::hookExec('invoice', array('id_order' => $order->id));
		if ($hook !== false)
		{
			echo '<div style="float: right; margin: -40px 40px 10px 0;">';
			echo $hook;
			echo '</div><br style="clear: both;" />';
		}

		// display order header
		echo '
		<div style="float: left;">';
		echo '
			<h2 style="width: 430px;">'.$customer->firstname.' '.$customer->lastname.' '.$this->l('#').sprintf('%06d', $order->id).
			((($currentState->invoice OR $order->invoice_number) AND count($products)) ? ' - <a href="pdf.php?id_order='.$order->id.'&pdf"><img src="../img/admin/tab-invoice.gif" alt="'.$this->l('View invoice').'" title="'.$this->l('View invoice').'" /></a>' : '').
			(($currentState->delivery OR $order->delivery_number) ? ' - <a href="pdf.php?id_delivery='.$order->delivery_number.'"><img src="../img/admin/delivery.gif" alt="'.$this->l('View delivery slip').'" title="'.$this->l('View delivery slip').'" /></a>' : '').
			' - <a href="javascript:window.print()"><img src="../img/admin/printer.gif" alt="'.$this->l('Print order').'" title="'.$this->l('Print order').'" /></a>';
		echo '</h2>';
		
		/* Display current state */
		echo '
			<table cellspacing="0" cellpadding="0" class="table" style="width: 429px">
				<tr>
					<th>'.Tools::displayDate($row['date_add'], intval($cookie->id_lang), true).'</th>
					<th><img src="../img/os/'.$row['id_order_state'].'.gif" /></th>
					<th>'.stripslashes($row['ostate_name']).'</th>
					<th>'.((!empty($row['employee_lastname'])) ? '('.stripslashes(Tools::substr($row['employee_firstname'], 0, 1)).'. '.stripslashes($row['employee_lastname']).')' : '').'</th>
				</tr>';
			/* Display previous states */
			foreach ($history AS $row)
			{
				echo '
				<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">
					<td>'.Tools::displayDate($row['date_add'], intval($cookie->id_lang), true).'</td>
					<td><img src="../img/os/'.$row['id_order_state'].'.gif" /></td>
					<td>'.stripslashes($row['ostate_name']).'</td>
					<td>'.((!empty($row['employee_lastname'])) ? '('.stripslashes(Tools::substr($row['employee_firstname'], 0, 1)).'. '.stripslashes($row['employee_lastname']).')' : '').'</td>
				</tr>';
			}
		echo '
			</table>
			<br />';

		/* Display state form */
		echo '
			<form action="'.$currentIndex.'&view'.$this->table.'&token='.$this->token.'" method="post" style="text-align:center;">
				<select name="id_order_state">';
		$currentStateTab = $order->getCurrentStateFull($cookie->id_lang);
		foreach ($states AS $state)
			echo '<option value="'.$state['id_order_state'].'"'.(($state['id_order_state'] == $currentStateTab['id_order_state']) ? ' selected="selected"' : '').'>'.stripslashes($state['name']).'</option>';
		echo '
				</select>
				<input type="hidden" name="id_order" value="'.$order->id.'" />
				<input type="submit" name="submitState" value="'.$this->l('Change').'" class="button" />
			</form>';
		
		/* Display customer information */
		echo '
		<br />
		<fieldset style="width: 400px">
			<legend><img src="../img/admin/tab-customers.gif" /> '.$this->l('Customer information').'</legend>
			<span style="font-weight: bold; font-size: 14px;"><a href="?tab=AdminCustomers&id_customer='.$customer->id.'&viewcustomer&token='.Tools::getAdminToken('AdminCustomers'.intval(Tab::getIdFromClassName('AdminCustomers')).intval($cookie->id_employee)).'"> '.$customer->firstname.' '.$customer->lastname.'</a></span> ('.$this->l('#').$customer->id.')<br />
			(<a href="mailto:'.$customer->email.'">'.$customer->email.'</a>)<br /><br />
			'.$this->l('Account registered:').' '.Tools::displayDate($customer->date_add, intval($cookie->id_lang), true).'<br />
			'.$this->l('Valid orders placed:').' <b>'.$customerStats['nb_orders'].'</b><br />
			'.$this->l('Total paid since registration:').' <b>'.Tools::displayPrice($customerStats['total_orders'], $currency, false, false).'</b><br />
		</fieldset>';
		
		/* Display sources */
		if (sizeof($sources))
		{
			echo '<br />
			<fieldset style="width: 400px;"><legend><img src="../img/admin/tab-stats.gif" /> '.$this->l('Sources').'</legend><ul '.(sizeof($sources) > 3 ? 'style="overflow-y: scroll; height: 200px"' : '').'>';
			foreach ($sources as $source)
				echo '<li>
						'.Tools::displayDate($source['date_add'], intval($cookie->id_lang), true).'<br />
						<b>'.$this->l('From:').'</b> <a href="'.$source['http_referer'].'">'.preg_replace('/^www./', '', parse_url($source['http_referer'], PHP_URL_HOST)).'</a><br />
						<b>'.$this->l('To:').'</b> '.$source['request_uri'].'<br />
						'.($source['keywords'] ? '<b>'.$this->l('Keywords:').'</b> '.$source['keywords'].'<br />' : '').'<br />
					</li>';
			echo '</ul></fieldset>';
		}
		// display hook specified to this page : AdminOrder
		if (($hook = Module::hookExec('adminOrder', array('id_order' => $order->id))) !== false)
			echo $hook;
		
		echo '
		</div>
		<div style="float: left; margin-left: 40px">';

		/* Display invoice information */
		if (($currentState->invoice OR $order->invoice_number) AND count($products))
		echo '
			<fieldset style="width: 400px">
				<legend><a href="pdf.php?id_order='.$order->id.'&pdf"><img src="../img/admin/tab-invoice.gif" /> '.$this->l('Invoice').'</a></legend>
				<a href="pdf.php?id_order='.$order->id.'&pdf">'.$this->l('Invoice #').'<b>'.Configuration::get('PS_INVOICE_PREFIX', intval($cookie->id_lang)).sprintf('%06d', $order->invoice_number).'</b></a>
				<br />'.$this->l('Created on:').' '.$order->invoice_date.'
			</fieldset><br />';
		
		/* Display shipping infos */
		echo '
		<fieldset style="width: 400px">
			<legend><img src="../img/admin/delivery.gif" /> '.$this->l('Shipping information').'</legend>
			'.$this->l('Total weight:').' <b>'.number_format($order->getTotalWeight(), 3).' '.Configuration::get('PS_WEIGHT_UNIT').'</b><br />
			'.$this->l('Carrier:').' <b>'.($carrier->name == '0' ? Configuration::get('PS_SHOP_NAME') : $carrier->name).'</b><br />
			'.(($currentState->delivery OR $order->delivery_number) ? '<br /><a href="pdf.php?id_delivery='.$order->delivery_number.'">'.$this->l('Delivery slip #').'<b>'.Configuration::get('PS_DELIVERY_PREFIX', intval($cookie->id_lang)).sprintf('%06d', $order->delivery_number).'</b></a><br />' : '');
			if ($order->shipping_number)
				echo $this->l('Tracking number:').' <b>'.$order->shipping_number.'</b> (<a href="'.str_replace('@', $order->shipping_number, $carrier->url).'">'.$this->l('Track the shipment').'</a>)';
			
			/* Carrier module */
			if ($carrier->is_module == 1)
			{
				$module = Module::getInstanceByName($carrier->name);
				echo call_user_func(array($module, 'displayInfoByCart'), $order->id_cart);
			}
			
			/* Display shipping number field */
			if ($carrier->url && $order->hasBeenShipped())
			 echo '
				<form action="'.$currentIndex.'&view'.$this->table.'&token='.$this->token.'" method="post" style="margin-top:10px;">
					<input type="text" name="shipping_number" value="'. $order->shipping_number.'" />
					<input type="hidden" name="id_order" value="'.$order->id.'" />
					<input type="submit" name="submitShippingNumber" value="'.$this->l('Set shipping number').'" class="button" />
				</form>';
			echo '
		</fieldset>';
		
		/* Display summary order */
		echo '
		<br />
		<fieldset style="width: 400px">
			<legend><img src="../img/admin/details.gif" /> '.$this->l('Order details').'</legend>
			<label>'.$this->l('Original cart:').' </label>
			<div style="margin: 2px 0 1em 190px;"><a href="?tab=AdminCarts&id_cart='.$cart->id.'&viewcart&token='.Tools::getAdminToken('AdminCarts'.intval(Tab::getIdFromClassName('AdminCarts')).intval($cookie->id_employee)).'">'.$this->l('Cart #').sprintf('%06d', $cart->id).'</a></div>
			<label>'.$this->l('Payment mode:').' </label>
			<div style="margin: 2px 0 1em 190px;">'.$order->payment.' '.($order->module ? '('.$order->module.')' : '').'</div>
			<div style="margin: 2px 0 1em 50px;">
				<table class="table" width="300px;" cellspacing="0" cellpadding="0">
					<tr><td width="150px;">'.$this->l('Products').'</td><td align="right">'.Tools::displayPrice($order->getTotalProductsWithTaxes(), $currency, false, false).'</td></tr>
					'.($order->total_discounts > 0 ? '<tr><td>'.$this->l('Discounts').'</td><td align="right">'.Tools::displayPrice($order->total_discounts, $currency, false, false).'</td></tr>' : '').'
					'.($order->total_wrapping > 0 ? '<tr><td>'.$this->l('Wrapping').'</td><td align="right">'.Tools::displayPrice($order->total_wrapping, $currency, false, false).'</td></tr>' : '').'
					<tr><td>'.$this->l('Shipping').'</td><td align="right">'.Tools::displayPrice($order->total_shipping, $currency, false, false).'</td></tr>
					<tr style="font-size: 20px"><td>'.$this->l('Total').'</td><td align="right">'.Tools::displayPrice($order->total_paid, $currency, false, false).($order->total_paid != $order->total_paid_real ? '<br /><font color="red">('.$this->l('Paid:').' '.Tools::displayPrice($order->total_paid_real, $currency, false, false).')</font>' : '').'</td></tr>
				</table>
			</div>
			<div style="float: left; margin-right: 10px; margin-left: 42px;">
				<span class="bold">'.$this->l('Recycled package:').'</span>
				'.($order->recyclable ? '<img src="../img/admin/enabled.gif" />' : '<img src="../img/admin/disabled.gif" />').'
			</div>
			<div style="float: left; margin-right: 10px;">
				<span class="bold">'.$this->l('Gift wrapping:').'</span>
				 '.($order->gift ? '<img src="../img/admin/enabled.gif" />
			'.(!empty($order->gift_message) ? '<div style="border: 1px dashed #999; padding: 5px; margin-top: 8px;"><b>'.$this->l('Message:').'</b><br />'.$order->gift_message.'</div>' : '') : '<img src="../img/admin/disabled.gif" />').'</div>
		</fieldset>';

		echo '
		</div>';

		echo '
		<div class="clear">&nbsp;</div>';

		/* Display adresses : delivery & invoice */
		echo '<div class="clear">&nbsp;</div>
		<div style="float: left">
			<fieldset style="width: 400px;">
				<legend><img src="../img/admin/delivery.gif" alt="'.$this->l('Shipping address').'" />'.$this->l('Shipping address').'</legend>
				<div style="float: right">
					<a href="'.$link->getUrlWith('tab', 'AdminAddresses').'&id_address='.$addressDelivery->id.'&addaddress&realedit=1&id_order='.$order->id.($addressDelivery->id == $addressInvoice->id ? '&address_type=1' : '').'&token='.Tools::getAdminToken('AdminAddresses'.intval(Tab::getIdFromClassName('AdminAddresses')).intval($cookie->id_employee)).'&back='.urlencode($_SERVER['REQUEST_URI']).'"><img src="../img/admin/edit.gif" /></a>
					<a href="http://maps.google.com/maps?f=q&hl='.$currentLanguage->iso_code.'&geocode=&q='.$addressDelivery->address1.' '.$addressDelivery->postcode.' '.$addressDelivery->city.($addressDelivery->id_state ? ' '.$deliveryState->name: '').'"><img src="../img/admin/google.gif" alt="" class="middle" /></a>
				</div>
				'. (!empty($addressDelivery->company) ? $addressDelivery->company.'<br />' : '') .$addressDelivery->firstname.' '.$addressDelivery->lastname.'<br />
				'.$addressDelivery->address1.'<br />'. (!empty($addressDelivery->address2) ? $addressDelivery->address2.'<br />' : '') .'
				'.$addressDelivery->postcode.' '.$addressDelivery->city.'<br />
				'.$addressDelivery->country.($addressDelivery->id_state ? ' - '.$deliveryState->name : '').'
				'.(!empty($addressDelivery->other) ? '<hr />'.$addressDelivery->other.'<br />' : '').'
			</fieldset>
		</div>
		<div style="float: left; margin-left: 40px">
			<fieldset style="width: 400px;">
				<legend><img src="../img/admin/invoice.gif" alt="'.$this->l('Invoice address').'" />'.$this->l('Invoice address').'</legend>
				<div style="float: right"><a href="'.$link->getUrlWith('tab', 'AdminAddresses').'&id_address='.$addressInvoice->id.'&addaddress&realedit=1&id_order='.$order->id.($addressDelivery->id == $addressInvoice->id ? '&address_type=2' : '').'&back='.urlencode($_SERVER['REQUEST_URI']).'&token='.Tools::getAdminToken('AdminAddresses'.intval(Tab::getIdFromClassName('AdminAddresses')).intval($cookie->id_employee)).'"><img src="../img/admin/edit.gif" /></a></div>
				'. (!empty($addressInvoice->company) ? $addressInvoice->company.'<br />' : '') .$addressInvoice->firstname.' '.$addressInvoice->lastname.'<br />
				'.$addressInvoice->address1.'<br />'. (!empty($addressInvoice->address2) ? $addressInvoice->address2.'<br />' : '') .'
				'.$addressInvoice->postcode.' '.$addressInvoice->city.'<br />
				'.$addressInvoice->country.($addressInvoice->id_state ? ' - '.$invoiceState->name : '').'
				'.(!empty($addressInvoice->other) ? '<hr />'.$addressInvoice->other.'<br />' : '').'
			</fieldset>
		</div>
		<div class="clear">&nbsp;</div>';

		// List of products
		echo '
		<a name="products"><br /></a>
		<form action="'.$currentIndex.'&submitCreditSlip&vieworder&token='.$this->token.'" method="post" onsubmit="return orderDeleteProduct(\''.$this->l('Cannot return this product').'\', \''.$this->l('Quantity to cancel is superior than quantity available').'\');">
			<input type="hidden" name="id_order" value="'.$order->id.'" />
			<fieldset style="width: 868px; ">
				<legend><img src="../img/admin/cart.gif" alt="'.$this->l('Products').'" />'.$this->l('Products').'</legend>
				<div style="float:left;">
					<table style="width: 868px;" cellspacing="0" cellpadding="0" class="table" id="orderProducts">
						<tr>
							<th align="center" style="width: 60px">&nbsp;</th>
							<th>'.$this->l('Product').'</th>
							<th style="width: 80px; text-align: center">'.$this->l('UP').' <sup>*</sup></th>
							<th style="width: 20px; text-align: center">'.$this->l('Qty').'</th>
							'.($order->hasBeenPaid() ? '<th style="width: 20px; text-align: center">'.$this->l('Refunded').'</th>' : '').'
							'.($order->hasBeenDelivered() ? '<th style="width: 20px; text-align: center">'.$this->l('Returned').'</th>' : '').'
							<th style="width: 30px; text-align: center">'.$this->l('Stock').'</th>
							<th style="width: 90px; text-align: center">'.$this->l('Total').'</th>
							<th colspan="2" style="width: 120px;"><img src="../img/admin/delete.gif" alt="'.$this->l('Products').'" /> '.($order->hasBeenDelivered() ? $this->l('Return') : ($order->hasBeenPaid() ? $this->l('Refund') : $this->l('Cancel'))).'</th>';
		echo '
						</tr>';
						$tokenCatalog = Tools::getAdminToken('AdminCatalog'.intval(Tab::getIdFromClassName('AdminCatalog')).intval($cookie->id_employee));
						foreach ($products as $k => $product)
						{
							$image = array();
							if (isset($product['product_attribute_id']) AND intval($product['product_attribute_id']))
								$image = Db::getInstance()->getRow('
								SELECT id_image
								FROM '._DB_PREFIX_.'product_attribute_image
								WHERE id_product_attribute = '.intval($product['product_attribute_id']));
						 	if (!isset($image['id_image']) OR !$image['id_image'])
								$image = Db::getInstance()->getRow('
								SELECT id_image
								FROM '._DB_PREFIX_.'image
								WHERE id_product = '.intval($product['product_id']).' AND cover = 1');
						 	$stock = Db::getInstance()->getRow('
							SELECT '.($product['product_attribute_id'] ? 'pa' : 'p').'.quantity
							FROM '._DB_PREFIX_.'product p
							'.($product['product_attribute_id'] ? 'LEFT JOIN '._DB_PREFIX_.'product_attribute pa ON p.id_product = pa.id_product' : '').'
							WHERE p.id_product = '.intval($product['product_id']).'
							'.($product['product_attribute_id'] ? 'AND pa.id_product_attribute = '.intval($product['product_attribute_id']) : ''));
							if (isset($image['id_image']))
							{
								$target = '../img/tmp/product_mini_'.intval($product['product_id']).(isset($product['product_attribute_id']) ? '_'.intval($product['product_attribute_id']) : '').'.jpg';
								if (file_exists($target))
									$products[$k]['image_size'] = getimagesize($target);
							}
							// Customization display
							$this->displayCustomizedDatas($customizedDatas, $product, $currency, $image, $tokenCatalog, $k);
							
							// Normal display
							if ($product['product_quantity'] > $product['customizationQuantityTotal'])
							{
								echo '
								<tr'.((isset($image['id_image']) AND isset($products[$k]['image_size'])) ? ' height="'.($products[$k]['image_size'][1] + 7).'"' : '').'>
									<td align="center">'.(isset($image['id_image']) ? cacheImage(_PS_IMG_DIR_.'p/'.intval($product['product_id']).'-'.intval($image['id_image']).'.jpg',
									'product_mini_'.intval($product['product_id']).(isset($product['product_attribute_id']) ? '_'.intval($product['product_attribute_id']) : '').'.jpg', 45, 'jpg') : '--').'</td>
									<td><a href="index.php?tab=AdminCatalog&id_product='.$product['product_id'].'&updateproduct&token='.$tokenCatalog.'">
										<span class="productName">'.$product['product_name'].'</span><br />
										'.($product['product_reference'] ? $this->l('Ref:').' '.$product['product_reference'] : '')
										.(($product['product_reference'] AND $product['product_supplier_reference']) ? ' / '.$product['product_supplier_reference'] : '')
										.'</a></td>
									<td align="center">'.Tools::displayPrice($product['product_price_wt'], $currency, false, false).'</td>
									<td align="center" class="productQuantity">'.(intval($product['product_quantity']) - $product['customizationQuantityTotal']).'</td>
									'.($order->hasBeenPaid() ? '<td align="center" class="productQuantity">'.intval($product['product_quantity_refunded']).'</td>' : '').'
									'.($order->hasBeenDelivered() ? '<td align="center" class="productQuantity">'.intval($product['product_quantity_return']).'</td>' : '').'
									<td align="center" class="productQuantity">'.intval($stock['quantity']).'</td>
									<td align="center">'.Tools::displayPrice($product['product_price'] * (1 + ($product['tax_rate'] * 0.01)) * (intval($product['product_quantity']) - $product['customizationQuantityTotal']), $currency, false, false).'</td>
									<td align="center" class="cancelCheck">
										<input type="hidden" name="totalQtyReturn" id="totalQtyReturn" value="'.intval($product['product_quantity_return']).'" />
										<input type="hidden" name="totalQty" id="totalQty" value="'.intval($product['product_quantity']).'" />
										<input type="hidden" name="productName" id="productName" value="'.$product['product_name'].'" />';
								if ((!$order->hasBeenDelivered() OR Configuration::get('PS_ORDER_RETURN')) AND intval($product['product_quantity_return']) < intval($product['product_quantity']))
									echo '
										<input type="checkbox" name="id_order_detail['.$k.']" id="id_order_detail['.$k.']" value="'.$product['id_order_detail'].'" onchange="setCancelQuantity(this, '.intval($product['id_order_detail']).', 1)" '.((intval($product['product_quantity_return'] + $product['product_quantity_refunded']) >= intval($product['product_quantity'])) ? 'disabled="disabled" ' : '').'/>';
								else
									echo '--';
								echo '
									</td>
									<td class="cancelQuantity">';
								if (intval($product['product_quantity_return'] + $product['product_quantity_refunded']) >= intval($product['product_quantity']))
									echo '<input type="hidden" name="cancelQuantity['.$k.']" value="0" />';
								elseif (!$order->hasBeenDelivered() OR Configuration::get('PS_ORDER_RETURN'))
									echo '
										<input type="text" id="cancelQuantity_'.intval($product['id_order_detail']).'" name="cancelQuantity['.$k.']" size="2" onclick="selectCheckbox(this);" value="" /> ';
								echo $this->getCancelledProductNumber($order, $product).'
									</td>
								</tr>';
							}
						}
					echo '
					</table>
					<div style="float:left; width:280px; margin-top:15px;"><sup>*</sup> '.$this->l('Prices are printed without taxes').(!Configuration::get('PS_ORDER_RETURN') ? '<br /><br />'.$this->l('Merchandise returns are disabled') : '').'</div>';
					if (sizeof($discounts))
					{
						echo '
					<div style="float:right; width:280px; margin-top:15px;">
					<table cellspacing="0" cellpadding="0" class="table" style="width:100%;">
						<tr>
							<th><img src="../img/admin/coupon.gif" alt="'.$this->l('Discounts').'" />'.$this->l('Discount name').'</th>
							<th align="center" style="width: 100px">'.$this->l('Value').'</th>
						</tr>';
						foreach ($discounts as $discount)
							echo '
						<tr>
							<td>'.$discount['name'].'</td>
							<td align="center">- '.Tools::displayPrice($discount['value'], $currency, false).'</td>
						</tr>';
						echo '
					</table></div>';
					}
				echo '
				</div>';
				
				// Cancel product
				echo '
				<div style="clear:both; height:15px;">&nbsp;</div>
				<div style="float: right; width: 160px;">';
				if ($order->hasBeenDelivered())
					echo '
					<input type="checkbox" id="reinjectQuantities" name="reinjectQuantities" class="button" />&nbsp;<label for="reinjectQuantities" style="float:none; font-weight:normal;">'.$this->l('Re-stock products').'</label><br />';
				if ((!$order->hasBeenDelivered() AND $order->hasBeenPaid()) OR ($order->hasBeenDelivered() AND Configuration::get('PS_ORDER_RETURN')))
					echo '
					<input type="checkbox" id="generateCreditSlip" name="generateCreditSlip" class="button" onclick="toogleShippingCost(this)" />&nbsp;<label for="generateCreditSlip" style="float:none; font-weight:normal;">'.$this->l('Generate a credit slip').'</label><br />
					<input type="checkbox" id="generateDiscount" name="generateDiscount" class="button" onclick="toogleShippingCost(this)" />&nbsp;<label for="generateDiscount" style="float:none; font-weight:normal;">'.$this->l('Generate a voucher').'</label><br />
					<span id="spanShippingBack" style="display:none;"><input type="checkbox" id="shippingBack" name="shippingBack" class="button" />&nbsp;<label for="shippingBack" style="float:none; font-weight:normal;">'.$this->l('Repay shipping costs').'</label><br /></span>';
				if (!$order->hasBeenDelivered() OR ($order->hasBeenDelivered() AND Configuration::get('PS_ORDER_RETURN')))
					echo '
					<div style="text-align:center; margin-top:5px;"><input type="submit" name="cancelProduct" value="'.($order->hasBeenDelivered() ? $this->l('Return products') : ($order->hasBeenPaid() ? $this->l('Refund products') : $this->l('Cancel products'))).'" class="button" style="margin-top:8px;" /></div>';
				echo '
				</div>';
			echo '
			</fieldset>
		</form>
		<div class="clear" style="height:20px;">&nbsp;</div>';
		
		/* Display send a message to customer & returns/credit slip*/
		$returns = OrderReturn::getOrdersReturn($order->id_customer, $order->id);
		$slips = OrderSlip::getOrdersSlip($order->id_customer, $order->id);
		echo '
		<div style="float: left">
			<form action="'.$_SERVER['REQUEST_URI'].'&token='.$this->token.'" method="post" onsubmit="if (getE(\'visibility\').checked == true) return confirm(\''.$this->l('Do you want to send this message to the customer?', __CLASS__, true, false).'\');">
			<fieldset style="width: 400px;">
				<legend style="cursor: pointer;" onclick="openCloseLayer(\'message\');openCloseLayer(\'message_m\');"><img src="../img/admin/email_edit.gif" /> '.$this->l('New message').'</legend>
				<div id="message_m" style="display: '.(Tools::getValue('message') ? 'none' : 'block').'">
					<a href="javascript:openCloseLayer(\'message\');openCloseLayer(\'message_m\');"><b>'.$this->l('Click here').'</b> '.$this->l('to add a comment or send a message to the customer').'</a>
				</div>
				<div id="message" style="display: '.(Tools::getValue('message') ? 'block' : 'none').'">
					<select name="order_message" id="order_message" onchange="orderOverwriteMessage(this, \''.$this->l('Do you want to overwrite your existing message?').'\')">
						<option value="0" selected="selected">-- '.$this->l('Choose a standard message').' --</option>';
		$orderMessages = OrderMessage::getOrderMessages(intval($order->id_lang));
        foreach ($orderMessages AS $orderMessage)
            echo '		<option value="'.htmlentities($orderMessage['message'], ENT_COMPAT, 'UTF-8').'">'.$orderMessage['name'].'</option>';
		echo '		</select><br /><br />
					<b>'.$this->l('Display to consumer?').'</b>
					<input type="radio" name="visibility" id="visibility" value="0" /> '.$this->l('Yes').'
					<input type="radio" name="visibility" value="1" checked="checked" /> '.$this->l('No').'
					<p id="nbchars" style="display:inline;font-size:10px;color:#666;"></p><br /><br />
					<textarea id="txt_msg" name="message" cols="50" rows="8" onKeyUp="var length = document.getElementById(\'txt_msg\').value.length; if (length > 600) length = \'600+\'; document.getElementById(\'nbchars\').innerHTML = \''.$this->l('600 chars max').' (\' + length + \')\';">'.htmlentities(Tools::getValue('message'), ENT_COMPAT, 'UTF-8').'</textarea><br /><br />
					<input type="hidden" name="id_order" value="'.intval($order->id).'" />
					<input type="hidden" name="id_customer" value="'.intval($order->id_customer).'" />
					<input type="submit" class="button" name="submitMessage" value="'.$this->l('Send').'" />
				</div>
			</fieldset>
			</form>';
		/* Display list of messages */
		if (sizeof($messages))
		{
			echo '
			<br />
			<fieldset style="width: 400px;">
			<legend><img src="../img/admin/email.gif" /> '.$this->l('Messages').'</legend>';
			foreach ($messages as $message)
			{
				echo '<div style="overflow:auto; width:400px;" '.($message['is_new_for_me'] ?'class="new_message"':'').'>';
				if ($message['is_new_for_me'])
					echo '<a class="new_message" title="'.$this->l('Mark this message as \'viewed\'').'" href="'.$_SERVER['REQUEST_URI'].'&token='.$this->token.'&messageReaded='.intval($message['id_message']).'"><img src="../img/admin/enabled.gif" alt="" /></a>';
				echo $this->l('At').' <i>'.Tools::displayDate($message['date_add'], intval($cookie->id_lang), true);
				echo '</i> '.$this->l('from').' <b>'.(($message['elastname']) ? ($message['efirstname'].' '.$message['elastname']) : ($message['cfirstname'].' '.$message['clastname'])).'</b>';
				echo (intval($message['private']) == 1 ? '<span style="color:red; font-weight:bold;">'.$this->l('Private:').'</span>' : '');
				echo '<p>'.nl2br2($message['message']).'</p>';
				echo '</div>';
				echo '<br />';
			}
			echo '<p class="info">'.$this->l('When you read a message, please click on the green check.').'</p>';
			echo '</fieldset>';
		}
		echo '</div>';
		
		/* Display return product */
		echo '<div style="float: left; margin-left: 40px">
			<fieldset style="width: 400px;">
				<legend><img src="../img/admin/return.gif" alt="'.$this->l('Merchandise returns').'" />'.$this->l('Merchandise returns').'</legend>';
		if (!sizeof($returns))
			echo $this->l('No merchandise return for this order.');
		else
			foreach ($returns as $return)
			{
				$state = new OrderReturnState($return['state']);
				echo '('.Tools::displayDate($return['date_upd'], $cookie->id_lang).') :
				<b><a href="index.php?tab=AdminReturn&id_order_return='.$return['id_order_return'].'&updateorder_return&token='.Tools::getAdminToken('AdminReturn'.intval(Tab::getIdFromClassName('AdminReturn')).intval($cookie->id_employee)).'">'.$this->l('#').sprintf('%06d', $return['id_order_return']).'</a></b> -
				'.$state->name[$cookie->id_lang].'<br />';
			}
		echo '</fieldset>';
		
		/* Display credit slip */
		echo '
				<br />
				<fieldset style="width: 400px;">
					<legend><img src="../img/admin/slip.gif" alt="'.$this->l('Credit slip').'" />'.$this->l('Credit slip').'</legend>';
		if (!sizeof($slips))
			echo $this->l('No slip for this order.');
		else
			foreach ($slips as $slip)
				echo '('.Tools::displayDate($slip['date_upd'], $cookie->id_lang).') : <b><a href="pdf.php?id_order_slip='.$slip['id_order_slip'].'">'.$this->l('#').sprintf('%06d', $slip['id_order_slip']).'</a></b><br />';
		echo '</fieldset>
		</div>';
		echo '<div class="clear">&nbsp;</div>';
		echo '<br /><br /><a href="'.$currentIndex.'&token='.$this->token.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to list').'</a><br />';
	}
	
	public function display()
	{
		global $cookie;

		if (isset($_GET['view'.$this->table]))
			$this->viewDetails();
		else
		{
			$this->getList(intval($cookie->id_lang), !Tools::getValue($this->table.'Orderby') ? 'date_add' : NULL, !Tools::getValue($this->table.'Orderway') ? 'DESC' : NULL);
			$currency = new Currency(intval(Configuration::get('PS_CURRENCY_DEFAULT')));
			$this->displayList();
			echo '<h2 class="space" style="text-align:right; margin-right:44px;">'.$this->l('Total:').' '.Tools::displayPrice($this->getTotal(), $currency).'</h2>';
		}
	}
	
	private function getTotal()
	{
		$total = 0;
		foreach ($this->_list as $item)
			$total += $item['total_paid'];
		return $total;
	}
}

?>
