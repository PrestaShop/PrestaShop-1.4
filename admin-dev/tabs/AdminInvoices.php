<?php

/**
  * Invoice tab for admin panel, AdminInvoices.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

class AdminInvoices extends AdminTab
{
	public function __construct()
	{
		global $cookie;

		$this->table = 'invoice';
		
		$this->optionTitle = $this->l('Invoices options');
		$this->_fieldsOptions = array(
			'PS_INVOICE' => array('title' => $this->l('Enable invoices:'), 'desc' => $this->l('Select whether or not to activate invoice for your shop'), 'cast' => 'intval', 'type' => 'bool'),
			'PS_INVOICE_PREFIX' => array('title' => $this->l('Invoice prefix:'), 'desc' => $this->l('Prefix used for invoices'), 'size' => 6, 'type' => 'textLang'),
			'PS_INVOICE_NUMBER' => array('title' => $this->l('Invoice number:'), 'desc' => $this->l('The next invoice will begin with this number, and then increase with each additional invoice'), 'size' => 6, 'type' => 'text', 'cast' => 'intval')
		);

		parent::__construct();
	}

	public function displayForm($isMainTab = true)
	{
		global $currentIndex;
		parent::displayForm();
		
		$output = '
		<h2>'.$this->l('Print PDF invoices').'</h2>
		<fieldset class="width2">
			<form action="'.$currentIndex.'&submitPrint=1&token='.$this->token.'" method="post">
				<label>'.$this->l('From:').' </label>
				<div class="margin-form">
					<input type="text" size="4" maxlength="10" name="date_from" value="'.(date('Y-m-d')).'" style="width: 120px;" /> <sup>*</sup>
					<p class="clear">'.$this->l('Format: 2007-12-31 (inclusive)').'</p>
				</div>
				<label>'.$this->l('To:').' </label>
				<div class="margin-form">
					<input type="text" size="4" maxlength="10" name="date_to" value="'.(date('Y-m-d')).'" style="width: 120px;" /> <sup>*</sup>
					<p class="clear">'.$this->l('Format: 2008-12-31 (inclusive)').'</p>
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
		
		if(Tools::isSubmit('submitPrint'))
		{
			if (!Validate::isDate(Tools::getValue('date_from')))
				$this->_errors[] = $this->l('Invalid from date');
			if (!Validate::isDate(Tools::getValue('date_to')))
				$this->_errors[] = $this->l('Invalid end date');
			if (!sizeof($this->_errors))
			{
				$orders = Order::getOrdersIdInvoiceByDate(Tools::getValue('date_from'), Tools::getValue('date_to'), NULL, 'invoice');
				if (sizeof($orders))
					Tools::redirectAdmin('pdf.php?invoices&date_from='.urlencode(Tools::getValue('date_from')).'&date_to='.urlencode(Tools::getValue('date_to')).'&token='.$this->token);
				$this->_errors[] = $this->l('No invoice found for this period');
			}
		}
		elseif (Tools::isSubmit('submitOptionsinvoice'))
		{
			if (intval(Tools::getValue('PS_INVOICE_NUMBER')) == 0)
				$this->_errors[] = $this->l('Invalid invoice number');
			else
				parent::postProcess();
		}
		else
			parent::postProcess();
	}
}

?>
