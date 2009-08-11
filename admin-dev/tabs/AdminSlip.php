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

class AdminSlip extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'order_slip';
	 	$this->className = 'OrderSlip';
		$this->edit = true;
	 	$this->delete = true;
		$this->noAdd = true;
		
 		$this->fieldsDisplay = array(
		'id_order_slip' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'id_order' => array('title' => $this->l('ID Order'), 'width' => 75, 'align' => 'center'),
		'date_add' => array('title' => $this->l('Date issued'), 'width' => 60, 'type' => 'date'));
		
		$this->optionTitle = $this->l('Slip');
		
		parent::__construct();
	}

	public function display()
	{
		global $cookie;

		$this->getList(intval($cookie->id_lang), !Tools::getValue($this->table.'Orderby') ? 'date_add' : NULL, !Tools::getValue($this->table.'Orderway') ? 'DESC' : NULL);
		$this->displayList();
	}
	
	public function displayListContent($token = NULL)
	{
		global $currentIndex, $cookie;
		$irow = 0;
		if ($this->_list)
			foreach ($this->_list AS $tr)
			{
				$tr['id_order'] = $this->l('#').sprintf('%06d', $tr['id_order']);
				echo '<tr'.($irow++ % 2 ? ' class="alt_row"' : '').'>';
				echo '<td class="center"><input type="checkbox" name="'.$this->table.'Box[]" value="'.$tr['id_order_slip'].'" class="noborder" /></td>';
				foreach ($this->fieldsDisplay AS $key => $params)
					echo '<td class="pointer" onclick="document.location = \'pdf.php?id_order_slip='.$tr['id_order_slip'].'\'">'.$tr[$key].'</td>';
				echo '<td class="center">';
				echo '
				<a href="pdf.php?id_order_slip='.$tr['id_order_slip'].'">
				<img src="../img/admin/details.gif" border="0" alt="'.$this->l('View').'" title="'.$this->l('View').'" /></a>';
				echo '
				<a href="'.$currentIndex.'&id_'.$this->table.'='.$tr['id_order_slip'].'&delete'.$this->table.'&token='.($token!=NULL ? $token : $this->token).'" onclick="return confirm(\''.$this->l('Delete item #', __CLASS__, true, false).$tr['id_order_slip'].$this->l('?', __CLASS__, true, false).'\');">
				<img src="../img/admin/delete.gif" border="0" alt="'.$this->l('Delete').'" title="'.$this->l('Delete').'" /></a>';
				echo '</td>';
				echo '</tr>';
			}
	}
}

?>