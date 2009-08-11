<?php

/**
  * Order statues tab for admin panel, AdminOrdersStates.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminReturn extends AdminTab
{
	public function __construct()
	{
		global $cookie;

	 	$this->table = 'order_return';
	 	$this->className = 'OrderReturn';
		$this->colorOnBackground = true;
		$this->_select = 'orsl.`name`';
		$this->_join = 'LEFT JOIN '._DB_PREFIX_.'order_return_state_lang orsl ON (orsl.`id_order_return_state` = a.`state` AND orsl.`id_lang` = '.intval($cookie->id_lang).')';

 		$this->fieldsDisplay = array(
		'id_order_return' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'id_order' => array('title' => $this->l('Order ID'), 'width' => 75, 'align' => 'center'),
		'name' => array('title' => $this->l('Status'), 'width' => 150, 'align' => 'center'),
		'date_add' => array('title' => $this->l('Date issued'), 'width' => 60, 'type' => 'date'));
		
		$this->optionTitle = $this->l('Merchandise return (RMA) options');
		$this->_fieldsOptions = array(
		'PS_ORDER_RETURN' => array('title' => $this->l('Enable returns:'), 'desc' => $this->l('Select whether or not to activate merchandise return for your shop'), 'cast' => 'intval', 'type' => 'bool'),
		'PS_ORDER_RETURN_NB_DAYS' => array('title' => $this->l('Time limit of validity:'), 'desc' => $this->l('Number of days the customer can make a return after the purchase date'), 'cast' => 'intval', 'type' => 'text', 'size' => '2'),
		);
		
		parent::__construct();
	}
	
	public function postProcess()
	{
		global $currentIndex, $cookie;
		
		if (Tools::isSubmit('deleteorder_return_detail'))
		{
			if ($this->tabAccess['delete'] === '1')
			{
				if (($id_order_detail = intval(Tools::getValue('id_order_detail'))) AND Validate::isUnsignedId($id_order_detail))
				{
					if (($id_order_return = intval(Tools::getValue('id_order_return'))) AND Validate::isUnsignedId($id_order_return))
					{
						$orderReturn = new OrderReturn($id_order_return);
						if (!Validate::isLoadedObject($orderReturn))
							die(Tools::displayError());
						if (intval($orderReturn->countProduct()) > 1)
						{
							if (OrderReturn::deleteOrderReturnDetail($id_order_return, $id_order_detail, intval(Tools::getValue('id_customization', 0))))
								Tools::redirectAdmin($currentIndex.'&conf=4token='.$this->token);
							else
								$this->_errors[] = Tools::displayError('an error occured while deleting an order return detail');
						}
						else
							$this->_errors[] = Tools::displayError('you need at least one product');
					}
					else
						$this->_errors[] = Tools::displayError('the order return is invalid');
				}
				else
					$this->_errors[] = Tools::displayError('the order return detail is invalid');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		elseif (Tools::isSubmit('submitAddorder_return'))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (($id_order_return = intval(Tools::getValue('id_order_return'))) AND Validate::isUnsignedId($id_order_return))
				{
					$orderReturn = new OrderReturn($id_order_return);
					$customer = new Customer($orderReturn->id_customer);
					$orderReturn->state = intval(Tools::getValue('state'));
					if ($orderReturn->save())
					{
						$orderReturnState = new OrderReturnState($orderReturn->state);
						$vars = array(
						'{lastname}' => $customer->lastname,
						'{firstname}' => $customer->firstname,
						'{id_order_return}' => $id_order_return,
						'{state_order_return}' => $orderReturnState->name[intval(Configuration::get('PS_LANG_DEFAULT'))]);
						Mail::Send(intval($cookie->id_lang), 'order_return_state', html_entity_decode($this->l('Your order return state has changed'), ENT_NOQUOTES, 'UTF-8'), $vars, $customer->email, $customer->firstname.' '.$customer->lastname);
						Tools::redirectAdmin($currentIndex.'&conf=4&token='.$this->token);
					}
				}
				else
					$this->_errors[] = Tools::displayError('no order return ID.');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
		}
		parent::postProcess();
	}
	
	public function display()
	{
		global $currentIndex, $cookie;

		// Include current tab
		if (isset($_GET['update'.$this->table]))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				$this->displayForm();
				echo '<br /><br /><a href="'.$currentIndex.'&token='.$this->token.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to list').'</a><br />';
			}
			else
				echo $this->l('You do not have permission to edit here');
		}
		else
		{
			$this->getList(intval($cookie->id_lang), !Tools::getValue($this->table.'Orderby') ? 'date_add' : NULL, !Tools::getValue($this->table.'Orderway') ? 'DESC' : NULL);
			$this->displayList();
			$this->displayOptionsList();
			$this->includeSubTab('display');
		}
	}

	
	public function displayListContent($token = NULL)
	{
		global $currentIndex, $cookie;
		$irow = 0;
		if ($this->_list)
			foreach ($this->_list AS $tr)
			{
				$tr['id_order'] = $this->l('#').sprintf('%06d', $tr['id_order']);
				$id = $tr['id_'.$this->table];
				echo '<tr'.($irow++ % 2 ? ' class="alt_row"' : '').' '.((isset($state->color) AND $this->colorOnBackground) ? 'style="background-color: '.$state->color.'"' : '').'>';
				foreach ($this->fieldsDisplay AS $key => $params)
					echo '<td class="pointer" onclick="document.location = \''.$currentIndex.'&id_'.$this->table.'='.$id.'&update'.$this->table.'&token='.($token!=NULL ? $token : $this->token).'\'"'.'>'.$tr[$key].'</td>';
				echo '</tr>';
			}
	}
	
	public function displayForm()
	{
		global $currentIndex, $cookie;
		
		$obj = $this->loadObject(true);
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();

		echo '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
		</script>
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<input type="hidden" name="id_order" value="'.$obj->id_order.'" />
			<input type="hidden" name="id_customer" value="'.$obj->id_customer.'" />
			<fieldset class="width3"><legend><img src="../img/admin/return.gif" />'.$this->l('Return Merchandise Authorization (RMA)').'</legend>
				<label>'.$this->l('Customer:').' </label>';
				$customer = new Customer(intval($obj->id_customer));
		echo '
				<div class="margin-form">'.$customer->firstname.' '.$customer->lastname.'
				<p style="clear: both"><a href="index.php?tab=AdminCustomers&id_customer='.$customer->id.'&viewcustomer&token='.Tools::getAdminToken('AdminCustomers'.intval(Tab::getIdFromClassName('AdminCustomers')).intval($cookie->id_employee)).'">'.$this->l('View details on customer page').'</a></p>
				</div>
				<label>'.$this->l('Order:').' </label>';
				$order = new Order(intval($obj->id_order));
		echo '		<div class="margin-form">'.$this->l('Order #').sprintf('%06d', $order->id).' '.$this->l('from').' '.Tools::displayDate($order->date_upd, $order->id_lang).'
				<p style="clear: both"><a href="index.php?tab=AdminOrders&id_order='.$order->id.'&vieworder&token='.Tools::getAdminToken('AdminOrders'.intval(Tab::getIdFromClassName('AdminOrders')).intval($cookie->id_employee)).'">'.$this->l('View details on order page').'</a></p>
				</div>
				<label>'.$this->l('Customer explanation:').' </label>
				<div class="margin-form">'.$obj->question.'</div>
				<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" style="float:right; margin-right:120px;"/>
				<label>'.$this->l('Status:').' </label>
				<div class="margin-form">
				<select name=\'state\'>';
				$states = OrderReturnState::getOrderReturnStates($cookie->id_lang);
				foreach ($states as $state)
					echo '<option value="'.$state['id_order_return_state'].'"'.($obj->state == $state['id_order_return_state'] ? ' selected="selected"' : '').'>'.$state['name'].'</option>';
		echo '	</select>
				<p style="clear: both">'.$this->l('Merchandise return (RMA) status').'</p>
				</div>';
		if ($obj->state >= 3)
			echo '	<label>'.$this->l('Slip:').' </label>
				<div class="margin-form">'.$this->l('Generate a new slip from the customer order').'
				<p style="clear: both"><a href="index.php?tab=AdminOrders&id_order='.$order->id.'&vieworder&token='.Tools::getAdminToken('AdminOrders'.intval(Tab::getIdFromClassName('AdminOrders')).intval($cookie->id_employee)).'#products">'.$this->l('More information on order page').'</a></p>
				</div>';
		echo '	<label>'.$this->l('Products:').' </label>
				<div class="margin-form">';
			echo '<table cellpadding="0" cellspacing="0">
					<tr>
						<td class="col-left">&nbsp;</td>
						<td>
							<table cellspacing="0" cellpadding="0" class="table">
							<tr>
								<th style="width: 100px;">'.$this->l('Reference').'</th>
								<th>'.$this->l('Product name').'</th>
								<th>'.$this->l('Quantity').'</th>
								<th>'.$this->l('Action').'</th>
							</tr>';

			$order = new Order(intval($obj->id_order));
			$quantityDisplayed = array();
			/* Customized products */
			if ($returnedCustomizations = OrderReturn::getReturnedCustomizedProducts(intval($obj->id_order)))
			{
				$allCustomizedDatas = Product::getAllCustomizedDatas(intval($order->id_cart));
				foreach ($returnedCustomizations AS $returnedCustomization)
				{
					echo '
					<tr>
						<td>'.$returnedCustomization['reference'].'</td>
						<td class="center">'.$returnedCustomization['name'].'</td>
						<td class="center">'.intval($returnedCustomization['product_quantity']).'</td>
						<td class="center"><a href="'.$currentIndex.'&deleteorder_return_detail&id_order_detail='.$returnedCustomization['id_order_detail'].'&id_customization='.$returnedCustomization['id_customization'].'&id_order_return='.$obj->id.'&token='.$this->token.'"><img src="../img/admin/delete.gif"></a></td>
					</tr>';
					$customizationDatas = &$allCustomizedDatas[intval($returnedCustomization['product_id'])][intval($returnedCustomization['product_attribute_id'])][intval($returnedCustomization['id_customization'])]['datas'];
					foreach ($customizationDatas AS $type => $datas)
					{
						echo '<tr>
						<td colspan="4">';
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
						</tr>';
					}
					$quantityDisplayed[intval($returnedCustomization['id_order_detail'])] = isset($quantityDisplayed[intval($returnedCustomization['id_order_detail'])]) ? $quantityDisplayed[intval($returnedCustomization['id_order_detail'])] + intval($returnedCustomization['product_quantity']) : intval($returnedCustomization['product_quantity']);
				}
			}

			/* Classic products */
			$products = OrderReturn::getOrdersReturnProducts($obj->id, $order);
			foreach ($products AS $k => $product)
				if (!isset($quantityDisplayed[intval($product['id_order_detail'])]) OR intval($product['product_quantity']) > intval($quantityDisplayed[intval($product['id_order_detail'])]))
					echo '
					<tr>
						<td>'.$product['product_reference'].'</td>
						<td class="center">'.$product['product_name'].'</td>
						<td class="center">'.$product['product_quantity'].'</td>
						<td class="center"><a href="'.$currentIndex.'&deleteorder_return_detail&id_order_detail='.$product['id_order_detail'].'&id_order_return='.$obj->id.'&token='.$this->token.'"><img src="../img/admin/delete.gif"></a></td>
					</tr>';

			echo '
							</table>
						</td>
					</tr>
				</table>
				<p>'.$this->l('List of products in return package').'</p>
				</div>
				<div class="margin-form">
					
				</div>
			</fieldset>
		</form>';
	}
}

?>