<?php

/**
  * PDF class, PDF.php
  * PDF invoices and document management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(_PS_FPDF_PATH_.'fpdf.php');

class PDF_PageGroup extends FPDF
{
	var $NewPageGroup;   // variable indicating whether a new group was requested
	var $PageGroups;	 // variable containing the number of pages of the groups
	var $CurrPageGroup;  // variable containing the alias of the current page group

	// create a new page group; call this before calling AddPage()
	function StartPageGroup()
	{
		$this->NewPageGroup=true;
	}

	// current page in the group
	function GroupPageNo()
	{
		return $this->PageGroups[$this->CurrPageGroup];
	}

	// alias of the current page group -- will be replaced by the total number of pages in this group
	function PageGroupAlias()
	{
		return $this->CurrPageGroup;
	}

	function _beginpage($orientation, $arg2)
	{
		parent::_beginpage($orientation, $arg2);
		if($this->NewPageGroup)
		{
			// start a new group
			$n = sizeof($this->PageGroups)+1;
			$alias = "{nb$n}";
			$this->PageGroups[$alias] = 1;
			$this->CurrPageGroup = $alias;
			$this->NewPageGroup=false;
		}
		elseif($this->CurrPageGroup)
			$this->PageGroups[$this->CurrPageGroup]++;
	}

	function _putpages()
	{
		$nb = $this->page;
		if (!empty($this->PageGroups))
		{
			// do page number replacement
			foreach ($this->PageGroups as $k => $v)
				for ($n = 1; $n <= $nb; $n++)
					$this->pages[$n]=str_replace($k, $v, $this->pages[$n]);
		}
		parent::_putpages();
	}
}

class PDF extends PDF_PageGroup
{
	private static $order = NULL;
	private static $orderReturn = NULL;
	private static $orderSlip = NULL;
	private static $delivery = NULL;

	/** @var object Order currency object */
	private static $currency = NULL;

	private static $_iso;

	/** @var array Special PDF params such encoding and font */

	private static $_pdfparams = array();
	private static $_fpdf_core_fonts = array('courier', 'helvetica', 'helveticab', 'helveticabi', 'helveticai', 'symbol', 'times', 'timesb', 'timesbi', 'timesi', 'zapfdingbats');

	/**
	* Constructor
	*/
	public function PDF($orientation='P', $unit='mm', $format='A4')
	{
		global $cookie;

		if (!isset($cookie) OR !is_object($cookie))
			$cookie->id_lang = intval(Configuration::get('PS_LANG_DEFAULT'));
		self::$_iso = strtoupper(Language::getIsoById($cookie->id_lang));
		FPDF::FPDF($orientation, $unit, $format);
		$this->_initPDFFonts();
	}

	private function _initPDFFonts()
	{
		if (!$languages = Language::getLanguages())
			die(Tools::displayError());
		foreach ($languages AS $language)
		{
			$isoCode = strtoupper($language['iso_code']);
			$conf = Configuration::getMultiple(array('PS_PDF_ENCODING_'.$isoCode, 'PS_PDF_FONT_'.$isoCode));
			self::$_pdfparams[$isoCode] = array(
				'encoding' => (isset($conf['PS_PDF_ENCODING_'.$isoCode]) AND $conf['PS_PDF_ENCODING_'.$isoCode] == true) ? $conf['PS_PDF_ENCODING_'.$isoCode] : 'iso-8859-1',
				'font' => (isset($conf['PS_PDF_FONT_'.$isoCode]) AND $conf['PS_PDF_FONT_'.$isoCode] == true) ? $conf['PS_PDF_FONT_'.$isoCode] : 'helvetica'
			);
		}

		if ($font = self::embedfont())
		{
			$this->AddFont($font);
			$this->AddFont($font, 'B');
		}
	}

	/**
	* Invoice header
	*/
	public function Header()
	{
		global $cookie;

		$conf = Configuration::getMultiple(array('PS_SHOP_NAME', 'PS_SHOP_ADDR1', 'PS_SHOP_CODE', 'PS_SHOP_CITY', 'PS_SHOP_COUNTRY', 'PS_SHOP_STATE'));
		$conf['PS_SHOP_NAME'] = isset($conf['PS_SHOP_NAME']) ? Tools::iconv('utf-8', self::encoding(), $conf['PS_SHOP_NAME']) : 'Your company';
		$conf['PS_SHOP_ADDR1'] = isset($conf['PS_SHOP_ADDR1']) ? Tools::iconv('utf-8', self::encoding(), $conf['PS_SHOP_ADDR1']) : 'Your company';
		$conf['PS_SHOP_CODE'] = isset($conf['PS_SHOP_CODE']) ? Tools::iconv('utf-8', self::encoding(), $conf['PS_SHOP_CODE']) : 'Postcode';
		$conf['PS_SHOP_CITY'] = isset($conf['PS_SHOP_CITY']) ? Tools::iconv('utf-8', self::encoding(), $conf['PS_SHOP_CITY']) : 'City';
		$conf['PS_SHOP_COUNTRY'] = isset($conf['PS_SHOP_COUNTRY']) ? Tools::iconv('utf-8', self::encoding(), $conf['PS_SHOP_COUNTRY']) : 'Country';
		$conf['PS_SHOP_STATE'] = isset($conf['PS_SHOP_STATE']) ? Tools::iconv('utf-8', self::encoding(), $conf['PS_SHOP_STATE']) : '';

		if (file_exists(_PS_IMG_DIR_.'/logo.jpg'))
			$this->Image(_PS_IMG_DIR_.'/logo.jpg', 10, 8, 0, 15);
		$this->SetFont(self::fontname(), 'B', 15);
		$this->Cell(115);
		
		if (self::$orderReturn)
			$this->Cell(77, 10, self::l('RETURN #').' '.sprintf('%06d', self::$orderReturn->id), 0, 1, 'R');
		elseif (self::$orderSlip)
			$this->Cell(77, 10, self::l('SLIP #').' '.sprintf('%06d', self::$orderSlip->id), 0, 1, 'R');
		elseif (self::$delivery)
			$this->Cell(77, 10, self::l('DELIVERY SLIP #').' '.Configuration::get('PS_DELIVERY_PREFIX', intval($cookie->id_lang)).sprintf('%06d', self::$delivery), 0, 1, 'R');
		elseif (self::$order->invoice_number)
			$this->Cell(77, 10, self::l('INVOICE #').' '.Configuration::get('PS_INVOICE_PREFIX', intval($cookie->id_lang)).sprintf('%06d', self::$order->invoice_number), 0, 1, 'R');
		else
			$this->Cell(77, 10, self::l('ORDER #').' '.sprintf('%06d', self::$order->id), 0, 1, 'R');
   }

	/**
	* Invoice footer
	*/
	public function Footer()
	{
		$this->SetY(-33);
		$this->SetFont(self::fontname(), '', 7);
		$this->Cell(190, 5, ' '."\n".'P. '.$this->GroupPageNo().' / '.$this->PageGroupAlias(), 'T', 1, 'R');

		/*
		 * Display a message for customer
		 */
		if (!self::$delivery)
		{
			$this->SetFont(self::fontname(), '', 8);
			if (self::$orderSlip)
				$textFooter = self::l('An electronic version of this credit slip is available in your account. To access it, log in to the');
			else
				$textFooter = self::l('An electronic version of this invoice is available in your account. To access it, log in to the');
			$this->Cell(0, 10, $textFooter, 0, 0, 'C', 0, (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].__PS_BASE_URI__.'history.php');			
			$this->Ln(4);
			$this->Cell(0, 10, Tools::iconv('utf-8', self::encoding(), Configuration::get('PS_SHOP_NAME')).' '.self::l('website using your e-mail address and password (which you created while placing your first order).'), 0, 0, 'C', 0, (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].__PS_BASE_URI__.'history.php');
		}
		else
			$this->Ln(4);
		$this->Ln(9);
		$arrayConf = array('PS_SHOP_NAME', 'PS_SHOP_ADDR1', 'PS_SHOP_CODE', 'PS_SHOP_CITY', 'PS_SHOP_COUNTRY', 'PS_SHOP_DETAILS', 'PS_SHOP_PHONE', 'PS_SHOP_STATE');
		$conf = Configuration::getMultiple($arrayConf);
		$conf['PS_SHOP_NAME_UPPER'] = Tools::strtoupper($conf['PS_SHOP_NAME']);
		foreach($conf as $key => $value)
			$conf[$key] = Tools::iconv('utf-8', self::encoding(), $value);
		foreach ($arrayConf as $key)
			if (!isset($conf[$key]))
				$conf[$key] = '';
		$this->SetFillColor(240, 240, 240);
		$this->SetTextColor(0, 0, 0);
		$this->SetFont(self::fontname(), '', 8);
		$this->Cell(0, 5, $conf['PS_SHOP_NAME_UPPER'].
		(!empty($conf['PS_SHOP_ADDR1']) ? ' - '.self::l('Headquarters:').' '.$conf['PS_SHOP_ADDR1'].(!empty($conf['PS_SHOP_ADDR2']) ? ' '.$conf['PS_SHOP_ADDR2'] : '').' '.$conf['PS_SHOP_CODE'].' '.$conf['PS_SHOP_CITY'].((isset($conf['PS_SHOP_STATE']) AND !empty($conf['PS_SHOP_STATE'])) ? (', '.$conf['PS_SHOP_STATE']) : '').' '.$conf['PS_SHOP_COUNTRY'] : ''), 0, 1, 'C', 1);
		$this->Cell(0, 5,
		(!empty($conf['PS_SHOP_DETAILS']) ? self::l('Details:').' '.$conf['PS_SHOP_DETAILS'].' - ' : '').
		(!empty($conf['PS_SHOP_PHONE']) ? self::l('PHONE:').' '.$conf['PS_SHOP_PHONE'] : ''), 0, 1, 'C', 1);
	}

	public static function multipleInvoices($invoices)
	{
		$pdf = new PDF('P', 'mm', 'A4');
		foreach ($invoices AS $id_order)
		{
			$orderObj = new Order(intval($id_order));
			if (Validate::isLoadedObject($orderObj))
				PDF::invoice($orderObj, 'D', true, $pdf);
		}
		return $pdf->Output('invoices.pdf', 'D');
	}

	public static function multipleDelivery($slips)
	{
		$pdf = new PDF('P', 'mm', 'A4');
		foreach ($slips AS $id_order)
		{
			$orderObj = new Order(intval($id_order));
			if (Validate::isLoadedObject($orderObj))
				PDF::invoice($orderObj, 'D', true, $pdf, false, $orderObj->delivery_number);
		}
		return $pdf->Output('invoices.pdf', 'D');
	}

	public static function orderReturn($orderReturn, $mode = 'D', $multiple = false, &$pdf = NULL)
	{
		$pdf = new PDF('P', 'mm', 'A4');
		self::$orderReturn = $orderReturn;
		$order = new Order($orderReturn->id_order);
		self::$order = $order;
		$pdf->SetAutoPageBreak(true, 35);
		$pdf->StartPageGroup();
		$pdf->AliasNbPages();
		$pdf->AddPage();
		
		/* Display address information */
		$delivery_address = new Address(intval($order->id_address_delivery));
		$deliveryState = $delivery_address->id_state ? new State($delivery_address->id_state) : false;
		$shop_country = Configuration::get('PS_SHOP_COUNTRY');
		$arrayConf = array('PS_SHOP_NAME', 'PS_SHOP_ADDR1', 'PS_SHOP_CODE', 'PS_SHOP_CITY', 'PS_SHOP_COUNTRY', 'PS_SHOP_DETAILS', 'PS_SHOP_PHONE', 'PS_SHOP_STATE');
		$conf = Configuration::getMultiple($arrayConf);
		foreach ($conf as $key => $value)
			$conf[$key] = Tools::iconv('utf-8', self::encoding(), $value);
		foreach ($arrayConf as $key)
			if (!isset($conf[$key]))
				$conf[$key] = '';

		$width = 100;
		$pdf->SetX(10);
		$pdf->SetY(25);
		$pdf->SetFont(self::fontname(), '', 9);

		if (!empty($delivery_address->company))
		{
			$pdf->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $delivery_address->company), 0, 'L');
			$pdf->Ln(5);
		}
		$pdf->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $delivery_address->firstname).' '.Tools::iconv('utf-8', self::encoding(), $delivery_address->lastname), 0, 'L');
		$pdf->Ln(5);
		$pdf->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $delivery_address->address1), 0, 'L');
		$pdf->Ln(5);
		if (!empty($delivery_address->address2))
		{
			$pdf->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $delivery_address->address2), 0, 'L');
			$pdf->Ln(5);
		}
		$pdf->Cell($width, 10, $delivery_address->postcode.' '.Tools::iconv('utf-8', self::encoding(), $delivery_address->city), 0, 'L');
		$pdf->Ln(5);
		$pdf->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $delivery_address->country.($deliveryState ? ' - '.$deliveryState->name : '')), 0, 'L');

		/*
		 * display order information
		 */
		$pdf->Ln(12);
		$pdf->SetFillColor(240, 240, 240);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont(self::fontname(), '', 9);
		$pdf->Cell(0, 6, self::l('RETURN #').sprintf('%06d', self::$orderReturn->id).' '.self::l('from') . ' ' .Tools::displayDate(self::$orderReturn->date_upd, self::$order->id_lang), 1, 2, 'L');
		$pdf->Cell(0, 6, self::l('We have logged your return request.'), 'TRL', 2, 'L');
		$pdf->Cell(0, 6, self::l('We remind you that your package must be returned to us within').' '.Configuration::get('PS_ORDER_RETURN_NB_DAYS').' '.self::l('days of initially receiving your order.'), 'BRL', 2, 'L');
		$pdf->Ln(5);
		$pdf->Cell(0, 6, self::l('List of items marked as returned :'), 0, 2, 'L');
		$pdf->Ln(5);
		$pdf->ProdReturnTab();
		$pdf->Ln(5);
		$pdf->SetFont(self::fontname(), 'B', 10);
		$pdf->Cell(0, 6, self::l('Return reference:').' '.self::l('RET').sprintf('%06d', self::$order->id), 0, 2, 'C');
		$pdf->Cell(0, 6, self::l('Thank you for including this number on your return package.'), 0, 2, 'C');
		$pdf->Ln(5);
		$pdf->SetFont(self::fontname(), 'B', 9);
		$pdf->Cell(0, 6, self::l('REMINDER:'), 0, 2, 'L');
		$pdf->SetFont(self::fontname(), '', 9);
		$pdf->Cell(0, 6, self::l('- All products must be returned in their original packaging without damage or wear.'), 0, 2, 'L');
		$pdf->Cell(0, 6, self::l('- Please print out this document and slip it into your package.'), 0, 2, 'L');
		$pdf->Cell(0, 6, self::l('- The package should be sent to the following address:'), 0, 2, 'L');
		$pdf->Ln(5);
		$pdf->SetFont(self::fontname(), 'B', 10);
		$pdf->Cell(0, 5, Tools::strtoupper($conf['PS_SHOP_NAME']), 0, 1, 'C', 1);
		$pdf->Cell(0, 5, (!empty($conf['PS_SHOP_ADDR1']) ? self::l('Headquarters:').' '.$conf['PS_SHOP_ADDR1'].(!empty($conf['PS_SHOP_ADDR2']) ? ' '.$conf['PS_SHOP_ADDR2'] : '').' '.$conf['PS_SHOP_CODE'].' '.$conf['PS_SHOP_CITY'].' '.$conf['PS_SHOP_COUNTRY'].((isset($conf['PS_SHOP_STATE']) AND !empty($conf['PS_SHOP_STATE'])) ? (', '.$conf['PS_SHOP_STATE']) : '') : ''), 0, 1, 'C', 1);
		$pdf->Ln(5);
		$pdf->SetFont(self::fontname(), '', 9);
		$pdf->Cell(0, 6, self::l('Upon receiving your package, we will inform you by e-mail and will then begin processing the reimbursement of your order total.'), 0, 2, 'L');
		$pdf->Cell(0, 6, self::l('Let us know if you have any questions.'), 0, 2, 'L');
		$pdf->Ln(5);
		$pdf->SetFont(self::fontname(), 'B', 10);
		$pdf->Cell(0, 6, self::l('If the conditions of return listed above are not respected,'), 'TRL', 2, 'C');
		$pdf->Cell(0, 6, self::l('we reserve the right to refuse your package and/or reimbursement.'), 'BRL', 2, 'C');

		return $pdf->Output(sprintf('%06d', self::$order->id).'.pdf', $mode);
	}
	
	/**
	* Product table with references, quantities...
	*/
	public function ProdReturnTab()
	{
		global $ecotax;

		$header = array(
			array(self::l('Description'), 'L'),
			array(self::l('Reference'), 'L'),
			array(self::l('Qty'), 'C')
		);
		$w = array(110, 25, 20);
		$this->SetFont(self::fontname(), 'B', 8);
		$this->SetFillColor(240, 240, 240);
		for ($i = 0; $i < sizeof($header); $i++)
			$this->Cell($w[$i], 5, $header[$i][0], 'T', 0, $header[$i][1], 1);
		$this->Ln();
		$this->SetFont(self::fontname(), '', 7);

		$products = OrderReturn::getOrdersReturnProducts(self::$orderReturn->id, self::$order);
		foreach ($products AS $product)
		{
			$before = $this->GetY();
			$this->MultiCell($w[0], 5, Tools::iconv('utf-8', self::encoding(), $product['product_name']), 'B');
			$lineSize = $this->GetY() - $before;
			$this->SetXY($this->GetX() + $w[0], $this->GetY() - $lineSize);
			$this->Cell($w[1], $lineSize, ($product['product_reference'] != '' ? $product['product_reference'] : '---'), 'B');
			$this->Cell($w[2], $lineSize, $product['product_quantity'], 'B', 0, 'C');
			$this->Ln();
		}
	}

	/**
	* Main
	*
	* @param object $order Order
	* @param string $mode Download or display (optional)
	*/
	public static function invoice($order, $mode = 'D', $multiple = false, &$pdf = NULL, $slip = false, $delivery = false)
	{
	 	global $cookie, $ecotax;

		if (!Validate::isLoadedObject($order) OR (!$cookie->id_employee AND (!OrderState::invoiceAvailable($order->getCurrentState()) AND !$order->invoice_number)))
			die('Invalid order or invalid order state');
		self::$order = $order;
		self::$orderSlip = $slip;
		self::$delivery = $delivery;
		self::$_iso = strtoupper(Language::getIsoById(intval(self::$order->id_lang)));

		if (!$multiple)
			$pdf = new PDF('P', 'mm', 'A4');

		$pdf->SetAutoPageBreak(true, 35);
		$pdf->StartPageGroup();

		self::$currency = new Currency(intval(self::$order->id_currency));

		$pdf->AliasNbPages();
		$pdf->AddPage();
		/* Display address information */
		$invoice_address = new Address(intval($order->id_address_invoice));
		$invoiceState = $invoice_address->id_state ? new State($invoice_address->id_state) : false;
		$delivery_address = new Address(intval($order->id_address_delivery));
		$deliveryState = $delivery_address->id_state ? new State($delivery_address->id_state) : false;
		$shop_country = Configuration::get('PS_SHOP_COUNTRY');

		$width = 100;

		$pdf->SetX(10);
		$pdf->SetY(25);
		$pdf->SetFont(self::fontname(), '', 12);
		$pdf->Cell($width, 10, self::l('Delivery'), 0, 'L');
		$pdf->Cell($width, 10, self::l('Invoicing'), 0, 'L');
		$pdf->Ln(5);
		$pdf->SetFont(self::fontname(), '', 9);

		if (!empty($delivery_address->company) OR !empty($invoice_address->company))
		{
			$pdf->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $delivery_address->company), 0, 'L');
			$pdf->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $invoice_address->company), 0, 'L');
			$pdf->Ln(5);
		}
		$pdf->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $delivery_address->firstname).' '.Tools::iconv('utf-8', self::encoding(), $delivery_address->lastname), 0, 'L');
		$pdf->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $invoice_address->firstname).' '.Tools::iconv('utf-8', self::encoding(), $invoice_address->lastname), 0, 'L');
		$pdf->Ln(5);
		$pdf->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $delivery_address->address1), 0, 'L');
		$pdf->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $invoice_address->address1), 0, 'L');
		$pdf->Ln(5);
		if (!empty($invoice_address->address2) OR !empty($delivery_address->address2))
		{
			$pdf->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $delivery_address->address2), 0, 'L');
			$pdf->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $invoice_address->address2), 0, 'L');
			$pdf->Ln(5);
		}
		$pdf->Cell($width, 10, $delivery_address->postcode.' '.Tools::iconv('utf-8', self::encoding(), $delivery_address->city), 0, 'L');
		$pdf->Cell($width, 10, $invoice_address->postcode.' '.Tools::iconv('utf-8', self::encoding(), $invoice_address->city), 0, 'L');
		$pdf->Ln(5);
		$pdf->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $delivery_address->country.($deliveryState ? ' - '.$deliveryState->name : '')), 0, 'L');
		$pdf->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $invoice_address->country.($invoiceState ? ' - '.$invoiceState->name : '')), 0, 'L');
		$pdf->Ln(5);
		$pdf->Cell($width, 10, $delivery_address->phone, 0, 'L');
		if (!empty($delivery_address->phone_mobile))
		{
			$pdf->Ln(5);
			$pdf->Cell($width, 10, $delivery_address->phone_mobile, 0, 'L');
		}

		/*
		 * display order information
		 */
		$carrier = new Carrier(self::$order->id_carrier);
		if ($carrier->name == '0')
				$carrier->name = Configuration::get('PS_SHOP_NAME');
		$history = self::$order->getHistory(self::$order->id_lang);
		foreach($history as $h)
			if ($h['id_order_state'] == _PS_OS_SHIPPING_)
				$shipping_date = $h['date_add'];
		$pdf->Ln(12);
		$pdf->SetFillColor(240, 240, 240);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont(self::fontname(), '', 9);
		if (self::$orderSlip)
			$pdf->Cell(0, 6, self::l('SLIP #').sprintf('%06d', self::$orderSlip->id).' '.self::l('from') . ' ' .Tools::displayDate(self::$orderSlip->date_upd, self::$order->id_lang), 1, 2, 'L', 1);
		elseif (self::$delivery)
			$pdf->Cell(0, 6, self::l('DELIVERY SLIP #').Configuration::get('PS_DELIVERY_PREFIX', intval($cookie->id_lang)).sprintf('%06d', self::$delivery).' '.self::l('from') . ' ' .Tools::displayDate(self::$order->delivery_date, self::$order->id_lang), 1, 2, 'L', 1);
		else
			$pdf->Cell(0, 6, self::l('INVOICE #').Configuration::get('PS_INVOICE_PREFIX', intval($cookie->id_lang)).sprintf('%06d', self::$order->invoice_number).' '.self::l('from') . ' ' .Tools::displayDate(self::$order->invoice_date, self::$order->id_lang), 1, 2, 'L', 1);
		$pdf->Cell(55, 6, self::l('Order #').sprintf('%06d', self::$order->id), 'L', 0);
		$pdf->Cell(70, 6, self::l('Carrier:'), 'L');
		$pdf->Cell(0, 6, self::l('Payment method:'), 'LR');
		$pdf->Ln(5);
		$pdf->Cell(55, 6, (isset($shipping_date) ? self::l('Shipping date:').' '.Tools::displayDate($shipping_date, self::$order->id_lang) : ' '), 'LB', 0);
		$pdf->Cell(70, 6, Tools::iconv('utf-8', self::encoding(), $carrier->name), 'LRB');
		$pdf->Cell(0, 6, Tools::iconv('utf-8', self::encoding(), $order->payment), 'LRB');
		$pdf->Ln(15);
		$pdf->ProdTab((self::$delivery ? true : ''));

		/* Exit if delivery */
		if (!self::$delivery)
		{
			$pdf->DiscTab();
			/*
			 * Display price summation
			 */
			$pdf->Ln(5);
			$pdf->SetFont(self::fontname(), 'B', 8);
			$width = 165;
			$pdf->Cell($width, 0, self::l('Total products (tax excl.)').' : ', 0, 0, 'R');
			$totalProductsTe = self::$order->getTotalProductsWithoutTaxes((self::$orderSlip ? self::$order->products : false));
			$pdf->Cell(0, 0, self::convertSign(Tools::displayPrice($totalProductsTe, self::$currency, true, false)), 0, 0, 'R');
			$pdf->Ln(4);

			$pdf->SetFont(self::fontname(), 'B', 8);
			$width = 165;
			$pdf->Cell($width, 0, self::l('Total products (tax incl.)').' : ', 0, 0, 'R');
			$totalProductsTi = self::$order->getTotalProductsWithTaxes((self::$orderSlip ? self::$order->products : false));
			$pdf->Cell(0, 0, self::convertSign(Tools::displayPrice($totalProductsTi, self::$currency, true, false)), 0, 0, 'R');
			$pdf->Ln(4);

			if (self::$order->total_discounts != '0.00')
			{
				$pdf->Cell($width, 0, self::l('Total discounts').' : ', 0, 0, 'R');
				$pdf->Cell(0, 0, (!self::$orderSlip ? '-' : '').self::convertSign(Tools::displayPrice(self::$order->total_discounts, self::$currency, true, false)), 0, 0, 'R');
				$pdf->Ln(4);
			}

			if(isset(self::$order->total_wrapping) and (floatval(self::$order->total_wrapping) > 0))
			{
				$pdf->Cell($width, 0, self::l('Total wrapping').' : ', 0, 0, 'R');
				$pdf->Cell(0, 0, self::convertSign(Tools::displayPrice(self::$order->total_wrapping, self::$currency, true, false)), 0, 0, 'R');
				$pdf->Ln(4);
			}

			if (self::$order->total_shipping != '0.00' AND (!self::$orderSlip OR (self::$orderSlip AND self::$orderSlip->shipping_cost)))
			{
				$pdf->Cell($width, 0, self::l('Total shipping').' : ', 0, 0, 'R');
				$pdf->Cell(0, 0, self::convertSign(Tools::displayPrice(self::$order->total_shipping, self::$currency, true, false)), 0, 0, 'R');
				$pdf->Ln(4);
			}

			if (!self::$orderSlip OR (self::$orderSlip AND self::$orderSlip->shipping_cost))
			{
				$pdf->Cell($width, 0, self::l('Total with Tax').' : ', 0, 0, 'R');
				$pdf->Cell(0, 0, self::convertSign(Tools::displayPrice((self::$orderSlip ? ($totalProductsTi + self::$order->total_discounts + self::$order->total_shipping) : self::$order->total_paid), self::$currency, true, false)), 0, 0, 'R');
				$pdf->Ln(4);
			}

			if ($ecotax != '0.00' AND !self::$orderSlip)
			{
				$pdf->Cell($width, 0, self::l('Eco-participation').' : ', 0, 0, 'R');
				$pdf->Cell(0, 0, self::convertSign(Tools::displayPrice($ecotax, self::$currency, true, false)), 0, 0, 'R');
				$pdf->Ln(5);
			}

			$pdf->TaxTab();
		}
		Hook::PDFInvoice($pdf, self::$order->id);

		if (!$multiple)
			return $pdf->Output(sprintf('%06d', self::$order->id).'.pdf', $mode);
	}

	public function ProdTabHeader($delivery = false)
	{
		if (!$delivery)
		{
			$header = array(
				array(self::l('Description'), 'L'),
				array(self::l('Reference'), 'L'),
				array(self::l('U. price'), 'R'),
				array(self::l('Qty'), 'C'),
				array(self::l('Pre-Tax Total'), 'R'),
				array(self::l('Total'), 'R')
			);
			$w = array(90, 15, 25, 10, 25, 25);
		}
		else
		{
			$header = array(
				array(self::l('Description'), 'L'),
				array(self::l('Reference'), 'L'),
				array(self::l('Qty'), 'C'),
			);
			$w = array(120, 30, 10);
		}
		$this->SetFont(self::fontname(), 'B', 8);
		$this->SetFillColor(240, 240, 240);
		if ($delivery)
			$this->SetX(25);
		for($i = 0; $i < sizeof($header); $i++)
			$this->Cell($w[$i], 5, $header[$i][0], 'T', 0, $header[$i][1], 1);
		$this->Ln();
		$this->SetFont(self::fontname(), '', 8);
	}

	/**
	* Product table with price, quantities...
	*/
	public function ProdTab($delivery = false)
	{
		global $ecotax;

		if (!$delivery)
			$w = array(90, 15, 25, 10, 25, 25);
		else
			$w = array(120, 30, 10);
		self::ProdTabHeader($delivery);
		if (isset(self::$order->products) AND sizeof(self::$order->products))
			$products = self::$order->products;
		else
			$products = self::$order->getProducts();
		$ecotax = 0;
		$customizedDatas = Product::getAllCustomizedDatas(intval(self::$order->id_cart));
		Product::addCustomizationPrice($products, $customizedDatas);

		$counter = 0;
		$lines = 25;
		$lineSize = 0;
		$line = 0;

		$isInPreparation = self::$order->isInPreparation();

		foreach($products AS $product)
			if (!$delivery OR (intval($product['product_quantity']) - intval($product['product_quantity_refunded']) > 0))
			{
				if($counter >= $lines)
				{
					$this->AddPage();
					$this->Ln();
					self::ProdTabHeader($delivery);
					$lineSize = 0;
					$counter = 0;
					$lines = 40;
					$line++;
				}
				$counter = $counter + ($lineSize / 5) ;

				$i = -1;
				$ecotax += $product['ecotax'] * intval($product['product_quantity']);

				// Unit vars
				$unit_without_tax = $product['product_price'];
				$unit_with_tax = $product['product_price'] * (1 + ($product['tax_rate'] * 0.01));
				$productQuantity = $delivery ? (intval($product['product_quantity']) - intval($product['product_quantity_refunded'])) : intval($product['product_quantity']);

				if ($productQuantity <= 0)
					continue ;

				// Total prices
				$total_without_tax = $unit_without_tax * $productQuantity;
				$total_with_tax = $unit_with_tax * $productQuantity;

				if (isset($customizedDatas[$product['product_id']][$product['product_attribute_id']]))
				{
					$productQuantity = intval($product['product_quantity']) - intval($product['customizationQuantityTotal']);
					if ($delivery)
						$this->SetX(25);
					$before = $this->GetY();
					$this->MultiCell($w[++$i], 5, Tools::iconv('utf-8', self::encoding(), $product['product_name']).' - '.self::l('Customized'), 'B');
					$lineSize = $this->GetY() - $before;
					$this->SetXY($this->GetX() + $w[0] + ($delivery ? 15 : 0), $this->GetY() - $lineSize);
					$this->Cell($w[++$i], $lineSize, $product['product_reference'], 'B');
					if (!$delivery)
						$this->Cell($w[++$i], $lineSize, self::convertSign(Tools::displayPrice($unit_without_tax, self::$currency, true, false)), 'B', 0, 'R');
					$this->Cell($w[++$i], $lineSize, intval($product['customizationQuantityTotal']), 'B', 0, 'C');
					if (!$delivery)
					{
						$this->Cell($w[++$i], $lineSize, self::convertSign(Tools::displayPrice($unit_without_tax * intval($product['customizationQuantityTotal']), self::$currency, true, false)), 'B', 0, 'R');
						$this->Cell($w[++$i], $lineSize, self::convertSign(Tools::displayPrice($unit_with_tax * intval($product['customizationQuantityTotal']), self::$currency, true, false)), 'B', 0, 'R');
					}
					$this->Ln();
					$i = -1;
					$total_without_tax = $unit_without_tax * $productQuantity;
					$total_with_tax = $unit_with_tax * $productQuantity;
				}
				if ($delivery)
					$this->SetX(25);
				if ($productQuantity)
				{
					$before = $this->GetY();
					$this->MultiCell($w[++$i], 5, Tools::iconv('utf-8', self::encoding(), $product['product_name']), 'B');
					$lineSize = $this->GetY() - $before;
					$this->SetXY($this->GetX() + $w[0] + ($delivery ? 15 : 0), $this->GetY() - $lineSize);
					$this->Cell($w[++$i], $lineSize, $product['product_reference'], 'B');
					if (!$delivery)
						$this->Cell($w[++$i], $lineSize, self::convertSign(Tools::displayPrice($unit_without_tax, self::$currency, true, false)), 'B', 0, 'R');
					$this->Cell($w[++$i], $lineSize, $productQuantity, 'B', 0, 'C');
					if (!$delivery)
					{
						$this->Cell($w[++$i], $lineSize, self::convertSign(Tools::displayPrice($total_without_tax, self::$currency, true, false)), 'B', 0, 'R');
						$this->Cell($w[++$i], $lineSize, self::convertSign(Tools::displayPrice($total_with_tax, self::$currency, true, false)), 'B', 0, 'R');
					}
					$this->Ln();
				}
			}

		if (!sizeof(self::$order->getDiscounts()) AND !$delivery)
			$this->Cell(array_sum($w), 0, '');
	}

	/**
	* Discount table with value, quantities...
	*/
	public function DiscTab()
	{
		$w = array(90, 25, 15, 10, 25, 25);
		$this->SetFont(self::fontname(), 'B', 7);
		$discounts = self::$order->getDiscounts();

		foreach($discounts AS $discount)
		{
			$this->Cell($w[0], 6, self::l('Discount:').' '.$discount['name'], 'B');
			$this->Cell($w[1], 6, '', 'B');
			$this->Cell($w[2], 6, '', 'B');
			$this->Cell($w[3], 6, '1', 'B', 0, 'C');
			$this->Cell($w[4], 6, '', 'B', 0, 'R');
			$this->Cell($w[5], 6, (!self::$orderSlip ? '-' : '').self::convertSign(Tools::displayPrice($discount['value'], self::$currency, true, false)), 'B', 0, 'R');
			$this->Ln();
		}

		if (sizeof($discounts))
			$this->Cell(array_sum($w), 0, '');
	}

	/**
	* Tax table
	*/
	public function TaxTab()
	{
		if (!$id_zone = Address::getZoneById(intval(self::$order->id_address_invoice)))
			die(Tools::displayError());

		if (self::$order->total_paid == '0.00' OR !intval(Configuration::get('PS_TAX')))
			return ;

		// Setting products tax
		if (isset(self::$order->products) AND sizeof(self::$order->products))
			$products = self::$order->products;
		else
			$products = self::$order->getProducts();
		$totalWithTax = array();
		$totalWithoutTax = array();
		$amountWithoutTax = 0;
		$taxes = array();
		/* Firstly calculate all prices */
		foreach ($products AS &$product)
		{
			if (!isset($totalWithTax[$product['tax_rate']]))
				$totalWithTax[$product['tax_rate']] = 0;
			if (!isset($totalWithoutTax[$product['tax_rate']]))
				$totalWithoutTax[$product['tax_rate']] = 0;
			if (!isset($taxes[$product['tax_rate']]))
				$taxes[$product['tax_rate']] = 0;
			/* Without tax */
			$product['priceWithoutTax'] = floatval($product['product_price']) * intval($product['product_quantity']);
			$amountWithoutTax += $product['priceWithoutTax'];
			/* With tax */
			$product['priceWithTax'] = $product['priceWithoutTax'] * (1 + (floatval($product['tax_rate']) / 100));
		}
		
		$tmp = 0;
		$product = &$tmp;

		/* And secondly assign to each tax its own reduction part */
		$discountAmount = floatval(self::$order->total_discounts);
		foreach ($products as $product)
		{
			$ratio = $amountWithoutTax == 0 ? 0 : $product['priceWithoutTax'] / $amountWithoutTax;
			$priceWithTaxAndReduction = $product['priceWithTax'] - ($discountAmount * $ratio);
			$vat = $priceWithTaxAndReduction - ($priceWithTaxAndReduction / ((floatval($product['tax_rate']) / 100) + 1));
			$taxes[$product['tax_rate']] += $vat;
			$totalWithTax[$product['tax_rate']] += $priceWithTaxAndReduction;
			$totalWithoutTax[$product['tax_rate']] += $priceWithTaxAndReduction - $vat;
		}
		
		$carrier = new Carrier(self::$order->id_carrier);
		$carrierTax = new Tax($carrier->id_tax);
		if (($totalWithoutTax == $totalWithTax) AND (!$carrierTax->rate OR $carrierTax->rate == '0.00') AND (!self::$order->total_wrapping OR self::$order->total_wrapping == '0.00'))
			return ;

		// Displaying header tax
		$header = array(self::l('Tax detail'), self::l('Tax %'), self::l('Pre-Tax Total'), self::l('Total Tax'), self::l('Total with Tax'));
		$w = array(60, 30, 40, 30, 30);
		$this->SetFont(self::fontname(), 'B', 8);
		for($i = 0; $i < sizeof($header); $i++)
			$this->Cell($w[$i], 5, $header[$i], 0, 0, 'R');

		$this->Ln();
		$this->SetFont(self::fontname(), '', 7);
		
		$nb_tax = 0;
		
		// Display product tax
		if (intval(Configuration::get('PS_TAX')) AND self::$order->total_paid != '0.00')
		{
			foreach ($taxes AS $tax_rate => $vat)
			{
				if ($tax_rate == '0.00' OR $totalWithTax[$tax_rate] == '0.00')
					continue ;
				$nb_tax++;
				$before = $this->GetY();
				$lineSize = $this->GetY() - $before;
				$this->SetXY($this->GetX(), $this->GetY() - $lineSize + 3);
				$this->Cell($w[0], $lineSize, self::l('Products'), 0, 0, 'R');
				$this->Cell($w[1], $lineSize, number_format($tax_rate, 2, ',', ' '), 0, 0, 'R');
				$this->Cell($w[2], $lineSize, self::convertSign(Tools::displayPrice($totalWithoutTax[$tax_rate], self::$currency, true, false)), 0, 0, 'R');
				$this->Cell($w[3], $lineSize, self::convertSign(Tools::displayPrice($vat, self::$currency, true, false)), 0, 0, 'R');
				$this->Cell($w[4], $lineSize, self::convertSign(Tools::displayPrice($totalWithTax[$tax_rate], self::$currency, true, false)), 0, 0, 'R');
				$this->Ln();
			}
		}

		// Display carrier tax
		if ($carrierTax->rate AND $carrierTax->rate != '0.00' AND self::$order->total_shipping != '0.00' AND Tax::zoneHasTax(intval($carrier->id_tax), intval($id_zone)))
		{
			$nb_tax++;
			$total_shipping_wt = self::$order->total_shipping / (1 + ($carrierTax->rate / 100));
			$before = $this->GetY();
			$lineSize = $this->GetY() - $before;
			$this->SetXY($this->GetX(), $this->GetY() - $lineSize + 3);
			$this->Cell($w[0], $lineSize, self::l('Carrier'), 0, 0, 'R');
			$this->Cell($w[1], $lineSize, number_format($carrierTax->rate, 2, ',', ' '), 0, 0, 'R');
			$this->Cell($w[2], $lineSize, self::convertSign(Tools::displayPrice($total_shipping_wt, self::$currency, true, false)), 0, 0, 'R');
			$this->Cell($w[3], $lineSize, self::convertSign(Tools::displayPrice(self::$order->total_shipping - $total_shipping_wt, self::$currency, true, false)), 0, 0, 'R');
			$this->Cell($w[4], $lineSize, self::convertSign(Tools::displayPrice(self::$order->total_shipping, self::$currency, true, false)), 0, 0, 'R');
			$this->Ln();
		}

		// Display wrapping tax
		if (self::$order->total_wrapping AND self::$order->total_wrapping != '0.00')
		{
			$nb_tax++;
			$wrappingTax = new Tax(Configuration::get('PS_GIFT_WRAPPING_TAX'));
			$taxRate = floatval($wrappingTax->rate);
			$total_wrapping_wt = self::$order->total_wrapping / (1 + ($taxRate / 100));
			$before = $this->GetY();
			$lineSize = $this->GetY() - $before;
			$this->SetXY($this->GetX(), $this->GetY() - $lineSize + 3);
			$this->Cell($w[0], $lineSize, self::l('Wrapping'), 0, 0, 'R');
			$this->Cell($w[1], $lineSize, number_format($taxRate, 2, ',', ' '), 0, 0, 'R');
			$this->Cell($w[2], $lineSize, self::convertSign(Tools::displayPrice($total_wrapping_wt, self::$currency, true, false)), 0, 0, 'R');
			$this->Cell($w[3], $lineSize, self::convertSign(Tools::displayPrice(self::$order->total_wrapping - $total_wrapping_wt, self::$currency, true, false)), 0, 0, 'R');
			$this->Cell($w[4], $lineSize, self::convertSign(Tools::displayPrice(self::$order->total_wrapping, self::$currency, true, false)), 0, 0, 'R');
		}
		
		if (!$nb_tax)
			$this->Cell(190, 10, self::l('No tax'), 0, 0, 'C');
	}

	static private function convertSign($s)
	{
		return str_replace('¥', chr(165), str_replace('£', chr(163), str_replace('€', chr(128), $s)));
	}

	static protected function l($string)
	{
		global $cookie;
		if (@!include(_PS_TRANSLATIONS_DIR_.Language::getIsoById($cookie->id_lang).'/pdf.php'))
			die('Cannot include PDF translation language file : '._PS_TRANSLATIONS_DIR_.Language::getIsoById($cookie->id_lang).'/pdf.php');

		if (!is_array($_LANGPDF))
			return str_replace('"', '&quot;', $string);
		$key = md5(str_replace('\'', '\\\'', $string));
		$str = (key_exists('PDF_invoice'.$key, $_LANGPDF) ? $_LANGPDF['PDF_invoice'.$key] : $string);

		return (Tools::iconv('utf-8', self::encoding(), $str));
	}

	static private function encoding()
	{
		return (isset(self::$_pdfparams[self::$_iso]) AND is_array(self::$_pdfparams[self::$_iso]) AND self::$_pdfparams[self::$_iso]['encoding']) ? self::$_pdfparams[self::$_iso]['encoding'] : 'iso-8859-1';
	}

	static private function embedfont()
	{
		return (((isset(self::$_pdfparams[self::$_iso]) AND is_array(self::$_pdfparams[self::$_iso]) AND self::$_pdfparams[self::$_iso]['font']) AND !in_array(self::$_pdfparams[self::$_iso]['font'], self::$_fpdf_core_fonts)) ? self::$_pdfparams[self::$_iso]['font'] : false);
	}

	static private function fontname()
	{
		$font = self::embedfont();
		return $font ? $font : 'Arial';
 	}
	
}
