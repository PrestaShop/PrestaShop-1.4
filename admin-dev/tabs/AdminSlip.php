<?php
/*
* Copyright (C) 2007-2010 PrestaShop 
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
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  Copyright (c) 2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

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
		'date_add' => array('title' => $this->l('Date issued'), 'width' => 60, 'type' => 'date', 'align' => 'right'));
		
		$this->optionTitle = $this->l('Slip');
		
		parent::__construct();
	}

	public function display()
	{
		global $cookie;

		$this->getList((int)($cookie->id_lang), !Tools::getValue($this->table.'Orderby') ? 'date_add' : NULL, !Tools::getValue($this->table.'Orderway') ? 'DESC' : NULL);
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

