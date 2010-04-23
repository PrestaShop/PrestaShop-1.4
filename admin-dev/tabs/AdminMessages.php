<?php

/**
  * Orders tab for admin panel, AdminOrders.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminMessages extends AdminTab
{
	public function __construct()
	{
	 	global $cookie;
	 	$this->table = 'order';
	 	$this->className = 'Order';
	 	$this->view = 'noActionColumn';
		$this->colorOnBackground = true;

		$start = 0;
		$this->_defaultOrderBy = 'date_add';

		/* Manage default params values */
		if (empty($limit))
			$limit = ((!isset($cookie->{$this->table.'_pagination'})) ? $this->_pagination[0] : $limit = $cookie->{$this->table.'_pagination'});

		if (!Validate::isTableOrIdentifier($this->table))
			die (Tools::displayError('Table name is invalid:').' "'.$this->table.'"');

		if (empty($orderBy))
			$orderBy = Tools::getValue($this->table.'Orderby', $this->_defaultOrderBy);
		elseif ($orderBy == 'id_order')
			$orderBy = 'm.id_order';
		
		if (empty($orderWay))
			$orderWay = Tools::getValue($this->table.'Orderway', 'ASC');		

		$limit = intval(Tools::getValue('pagination', $limit));
		$cookie->{$this->table.'_pagination'} = $limit;

		/* Check params validity */
		if (!Validate::isOrderBy($orderBy) OR !Validate::isOrderWay($orderWay)
			OR !is_numeric($start) OR !is_numeric($limit))
			die(Tools::displayError('get list params is not valid'));

		if ($orderBy == 'id_order')
			$orderBy = 'm.id_order';
		
		/* Determine offset from current page */
		if ((isset($_POST['submitFilter'.$this->table]) OR
		isset($_POST['submitFilter'.$this->table.'_x']) OR
		isset($_POST['submitFilter'.$this->table.'_y'])) AND
		!empty($_POST['submitFilter'.$this->table]) AND
		is_numeric($_POST['submitFilter'.$this->table]))
			$start = intval($_POST['submitFilter'.$this->table] - 1) * $limit;

		$this->_list = Db::getInstance()->ExecuteS('
		SELECT m.id_message, m.id_cart, m.id_employee, IF(m.id_order > 0, m.id_order, \'--\') id_order, m.message, m.private, m.date_add, CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS customer,
		c.id_customer, count(m.id_message) nb_messages, (SELECT message FROM '._DB_PREFIX_.'message WHERE id_order = m.id_order ORDER BY date_add DESC LIMIT 1) last_message,
		(SELECT COUNT(m2.id_message) FROM '._DB_PREFIX_.'message m2 WHERE 1 AND m2.id_customer != 0 AND m2.id_order = m.id_order AND m2.id_message NOT IN 
		(SELECT mr2.id_message FROM '._DB_PREFIX_.'message_readed mr2 WHERE mr2.id_employee = '.intval($cookie->id_employee).') GROUP BY m2.id_order) nb_messages_not_read_by_me
		FROM '._DB_PREFIX_.'message m
		LEFT JOIN '._DB_PREFIX_.'orders o ON (o.id_order = m.id_order)
		LEFT JOIN '._DB_PREFIX_.'customer c ON (c.id_customer = m.id_customer)
		GROUP BY m.id_order
		ORDER BY '.(isset($orderBy) ? pSQL($orderBy) : 'date_add') .' '.(isset($orderWay) ? pSQL($orderWay) : 'DESC').'
		LIMIT '.intval($start).','.intval($limit));

 		$this->fieldsDisplay = array(
		'id_order' => array('title' => $this->l('Order ID'), 'align' => 'center', 'width' => 30),
		'id_customer' => array('title' => $this->l('Customer ID'), 'align' => 'center', 'width' => 30),
		'customer' => array('title' => $this->l('Customer'), 'width' => 100, 'filter_key' => 'customer', 'tmpTableFilter' => true),
		'last_message' => array('title' => $this->l('Last message'), 'width' => 400, 'orderby' => false),
		'nb_messages_not_read_by_me' => array('title' => $this->l('Unread message(s)'), 'width' =>30, 'align' => 'center'),
		'nb_messages' => array('title' => $this->l('Number of messages'), 'width' => 30, 'align' => 'center'));

		parent::__construct();
	}

	public function display()
	{
		global $cookie;

		if (isset($_GET['view'.$this->table]) AND !empty($_GET['id_order']) AND $_GET['id_order'] != '--')
			Tools::redirectAdmin('index.php?tab=AdminOrders&id_order='.intval($_GET['id_order']).'&vieworder'.'&token='.Tools::getAdminToken('AdminOrders'.intval(Tab::getIdFromClassName('AdminOrders')).intval($cookie->id_employee)));
		else
		{
			if (isset($_GET['id_order']) AND (empty($_GET['id_order']) OR $_GET['id_order'] == '--'))
			{
				echo '<p class="warning bold"><img src="../img/admin/warning.gif" alt="" class="middle" /> &nbsp;'.
				Tools::displayError('Cannot display this message because the customer has not finalized its order').'</p>';
			}
			foreach ($this->_list AS $k => $item)
				if (Tools::strlen($item['last_message']) > 150 + Tools::strlen('...'))
					$this->_list[$k]['last_message'] = Tools::substr(html_entity_decode($item['last_message'], ENT_QUOTES, 'UTF-8'), 0, 150, 'UTF-8').'...';
			$this->displayList();
			$this->displayOptionsList();
		}
	}
}

?>
