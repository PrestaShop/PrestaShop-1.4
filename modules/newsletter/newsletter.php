<?php

class Newsletter extends Module
{
    private $_postErrors = array();
    private $_html = '';
    private $_postSucess;

    public function __construct()
    {
		global $cookie;

        $this->name = 'newsletter';
        $this->tab = 'Tools';
        $this->version = 2.0;

        parent::__construct();

        $this->displayName = $this->l('Newsletter');
        $this->description = $this->l('Generates a .CSV file for mass mailings');
		$this->_file = 'export_'.Configuration::get('PS_NEWSLETTER_RAND').'.csv';
		$this->_postValid = array();

		// Getting data...
		$id_lang = intval($cookie->id_lang);
		$_countries = Country::getCountries($id_lang);

		// ...formatting array
		$countries[0] = $this->l('All countries');
		foreach ($_countries as $country)
			$countries[$country['id_country']] = $country['name'];

		// And filling fields to show !
		$this->_fieldsExport = array(
			'COUNTRY' => array(
				'title' => $this->l('Customers\' country'),
				'desc' => $this->l('Operate a filter on customers\' country.'),
				'type' => 'select',
				'value' => $countries,
				'value_default' => 0
				),
			'SUSCRIBERS' => array(
				'title' => $this->l('Newsletter\'s suscribers'),
				'desc' => $this->l('Filter newsletter subscribers.'),
				'type' => 'select',
				'value' => array(0 => $this->l('All customers'), 2 => $this->l('Subscribers'), 1 => $this->l('Non-subscribers')),
				'value_default' => 2
				),
			'OPTIN' => array(
				'title' => $this->l('Opted-in subscribers'),
				'desc' => $this->l('Filter opted-in subscribers.'),
				'type' => 'select',
				'value' => array(0 => $this->l('All customers'), 2 => $this->l('Subscribers'), 1 => $this->l('Non-subscribers')),
				'value_default' => 0
				),
			);
    }

	public function install()
	{
		return (parent::install() AND Configuration::updateValue('PS_NEWSLETTER_RAND', rand().rand()));
	}
	
    private function _postProcess()
    {
       if (isset($_POST['submitExport']) AND isset($_POST['action']))
		{
			if ($_POST['action'] == 'customers')
				$result = $this->_getCustomers();
			else
			{
				if (!Module::isInstalled('blocknewsletter'))
					$this->_html .= $this->displayError('The module "blocknewsletter" is required for this feature');
				else
					$result = $this->_getBlockNewsletter();
			}
			if (!$nb = intval(Db::getInstance()->NumRows()))
				$this->_html .= $this->displayError($this->l('No customers were found with these filters !'));
			elseif ($fd = @fopen(dirname(__FILE__).'/'.strval(preg_replace('#\.{2,}#', '.', $_POST['action'])).'_'.$this->_file, 'w'))
			{
				foreach ($result AS $tab)
					$this->_my_fputcsv($fd, $tab);
				fclose($fd);
				$this->_html .= $this->displayConfirmation($this->l('The .CSV file has been successfully exported').' ('.$nb.' '.$this->l('customers found').')<br />> <a href="../modules/newsletter/'.strval($_POST['action']).'_'.$this->_file.'"><b>'.$this->l('Download the file').'</b></a>');
			}
			else
				$this->_html .= $this->displayError($this->l('Error: cannot write to').' '.dirname(__FILE__).'/'.strval($_POST['action']).'_'.$this->_file.' !');
		}
    }

	private function _getCustomers()
	{
		$rq = Db::getInstance()->ExecuteS('
		SELECT c.`id_customer`, c.`lastname`, c.`firstname`, c.`email`, c.`ip_registration_newsletter`, c.`newsletter_date_add`
		FROM `'._DB_PREFIX_.'customer` c
		WHERE 1
		'.((isset($_POST['SUSCRIBERS']) AND intval($_POST['SUSCRIBERS']) != 0) ? 'AND c.`newsletter` = '.intval($_POST['SUSCRIBERS'] - 1) : '').'
		'.((isset($_POST['OPTIN']) AND intval($_POST['OPTIN']) != 0) ? 'AND c.`optin` = '.intval($_POST['OPTIN'] - 1) : '').'
		'.((isset($_POST['COUNTRY']) AND intval($_POST['COUNTRY']) != 0) ? 'AND (SELECT COUNT(a.`id_address`) as nb_country FROM `'._DB_PREFIX_.'address` a WHERE a.`id_customer` = c.`id_customer` AND a.`id_country` = '.intval($_POST['COUNTRY']).') >= 1' : '').'
		GROUP BY c.`id_customer`');
		$header = array('id_customer', 'lastname', 'firstname', 'email', 'ip_address', 'newsletter_date_add');
		$result = (is_array($rq) ? array_merge(array($header), $rq) : $header);
		return $result;
	}

	private function _getBlockNewsletter()
	{
		$rq = Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'newsletter`');
		$header = array('id_customer', 'email', 'newsletter_date_add', 'ip_address');
		$result = (is_array($rq) ? array_merge(array($header), $rq) : $header);
		return $result;
	}

	private function _my_fputcsv($fd, $array)
	{
		$line = implode(';', $array);
		$line .= "\n";
		if (!fwrite($fd, utf8_decode($line), 4096))
			$this->_postErrors[] = $this->l('Error: cannot write to').' '.dirname(__FILE__).'/'.$this->_file.' !';
	}

    private function _displayFormExport()
	{
		$this->_html .= '
		<fieldset class="width3">
		'.$this->l('There are two sorts for this module:').'
		<p><ol>
			<li>
				1. '.$this->l('Persons who have subscribed using the BlockNewsletter block in the front office.').'<br />
                '.$this->l('This will be a list of email addresses for persons coming to your store and not becoming a customer but wanting to get your newsletter. Using the "Export Newsletter Subscribers" below will generate a .CSV file based on the BlockNewsletter subscribers data.').'<br /><br />'.'
            </li>
            <li>
                2. '.$this->l('Customers that have checked "yes" to receive a newsletter in their customer profile.').'<br />
                '.$this->l('The "Export Customers" section below filters which customers you want to send a newsletter.').'
            </li>
        </ol></p>
        </fieldset><br />
        <fieldset class="width3"><legend>'.$this->l('Export Newsletter Subscribers').'</legend>
        <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<input type="hidden" name="action" value="blockNewsletter">
			'.$this->l('Generate a .CSV file based on BlockNewsletter subscribers data.').'.<br /><br />';
		$this->_html .= '<br />
		<center><input type="submit" class="button" name="submitExport" value="'.$this->l('Export .CSV file').'" /></center>
        </form></fieldset><br />
		<fieldset class="width3"><legend>'.$this->l('Export customers').'</legend>
        <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<input type="hidden" name="action" value="customers">
			'.$this->l('Generate an .CSV file from customers account data').'.<br /><br />';
		foreach ($this->_fieldsExport as $key => $field)
		{
			$this->_html .= '
			<label style="margin-top:15px;">'.$field['title'].' </label>
			<div class="margin-form" style="margin-top:15px;">
				'.$field['desc'].'<br /><br />';
			switch ($field['type'])
			{
				case 'select':
					$this->_html .= '<select name="'.$key.'">';
					foreach ($field['value'] AS $k => $value)
						$this->_html .= '<option value="'.$k.'"'.(($k == Tools::getValue($key, $field['value_default'])) ? ' selected="selected"' : '').'>'.$value.'</option>';
					$this->_html .= '</select>';
					break;
				default:
					break;
			}
			if (isset($field['example']) AND !empty($field['example']))
				$this->_html .= '<p style="clear: both;">'.$field['example'].'</p>';
			$this->_html .= '
			</div>';
		}
		$this->_html .= '<br />
		<center><input type="submit" class="button" name="submitExport" value="'.$this->l('Export .CSV file').'" /></center>
        </form></fieldset>';
	}

    private function _displayForm()
    {
		$this->_displayFormExport();
    }

    public function getContent()
    {
        $this->_html .= '<h2>'.$this->displayName.'</h2>';

        if (!empty($_POST))
			$this->_html .= $this->_postProcess();
        $this->_displayForm();

		return $this->_html;
    }
}
?>
