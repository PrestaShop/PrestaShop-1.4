<?php

/**
  * Delivery slip tab for admin panel, AdminDeliverySlip.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class AdminDeliverySlip extends AdminTab
{
	public function __construct()
	{
		global $cookie;

		$this->table = 'delivery';
		
		$this->optionTitle = $this->l('Delivery slips options');
		$this->_fieldsOptions = array(
			'PS_DELIVERY_PREFIX' => array('title' => $this->l('Delivery prefix:'), 'desc' => $this->l('Prefix used for delivery slips'), 'size' => 2, 'type' => 'textLang'),
			'PS_DELIVERY_NUMBER' => array('title' => $this->l('Delivery number:'), 'desc' => $this->l('The next delivery slip will begin with this number, and then increase with each additional slip'), 'size' => 2, 'type' => 'text'),
		);

		parent::__construct();
	}

	public function displayForm()
	{
		global $currentIndex;
		
		$output = '
		<h2>'.$this->l('Print PDF delivery slips').'</h2>
		<fieldset class="width2">
			<form action="'.$currentIndex.'&submitPrint=1&token='.$this->token.'" method="post">
				<label>'.$this->l('From:').' </label>
				<div class="margin-form">
					<input type="text" size="4" maxlength="10" name="date_from" value="'.(date('Y-m-d')).'" style="width: 120px;" /> <sup>*</sup>
					<p style="clear: both;">'.$this->l('Format: 2007-12-31 (inclusive)').'</p>
				</div>
				<label>'.$this->l('To:').' </label>
				<div class="margin-form">
					<input type="text" size="4" maxlength="10" name="date_to" value="'.(date('Y-m-d')).'" style="width: 120px;" /> <sup>*</sup>
					<p style="clear: both;">'.$this->l('Format: 2008-12-31 (inclusive)').'</p>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('Generate PDF file').'" name="submitPrint" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required fields').'</div>
			</form>
		</fieldset>';
		
		echo $output;
	}
	
	public function display()
	{
		$this->displayForm();
		$this->displayOptionsList();
	}
	
	public function postProcess()
	{
		global $currentIndex;
		
		if(Tools::getValue('submitPrint'))
		{
			if (!Validate::isDate($_POST['date_from']))
				$this->_errors[] = $this->l('Invalid from date');
			if (!Validate::isDate($_POST['date_to']))
				$this->_errors[] = $this->l('Invalid end date');
			if (!sizeof($this->_errors))
			{
				$orders = Order::getOrdersIdByDate($_POST['date_from'], $_POST['date_to'], NULL, 'delivery');
				if (sizeof($orders))
					Tools::redirectAdmin('pdf.php?deliveryslips='.urlencode(serialize($orders)).'&token='.$this->token);
				else
					$this->_errors[] = $this->l('No delivery slip found for this period');
			}			
		}
		else
			parent::postProcess();
	}
}

?>