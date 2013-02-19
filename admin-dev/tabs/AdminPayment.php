<?php
/*
* 2007-2013 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminPayment extends AdminTab
{
	public $paymentModules = array();

	public function __construct()
	{
		$modules_infos = PaymentModule::getInstalledPaymentModules();
		foreach ($modules_infos AS $module_infos)
		{
			$module = Module::getInstanceByName($module_infos['name']);
			if (!$module)
				continue;

			if ($module->tab == 'payments_gateways')
			{
				if ($module->id)
				{
					if (!get_class($module) == 'SimpleXMLElement')
						$module->country = array();
					$countries = Db::getInstance()->ExecuteS('SELECT id_country FROM '._DB_PREFIX_.'module_country WHERE id_module = '.(int)($module->id));
					foreach ($countries as $country)
						$module->country[] = $country['id_country'];

					if (!get_class($module) == 'SimpleXMLElement')
						$module->currency = array();
					$currencies = Db::getInstance()->ExecuteS('SELECT id_currency FROM '._DB_PREFIX_.'module_currency WHERE id_module = '.(int)($module->id));
					foreach ($currencies as $currency)
						$module->currency[] = $currency['id_currency'];

					if (!get_class($module) == 'SimpleXMLElement')
						$module->group = array();
					$groups = Db::getInstance()->ExecuteS('SELECT id_group FROM '._DB_PREFIX_.'module_group WHERE id_module = '.(int)($module->id));
					foreach ($groups as $group)
						$module->group[] = $group['id_group'];
				}
				else
				{
					$module->country = NULL;
					$module->currency = NULL;
					$module->group = NULL;
				}

				$this->paymentModules[] = $module;
			}
		}
		parent::__construct();
	}

	public function postProcess()
	{
		if (Tools::isSubmit('submitModulecountry'))
			$this->saveRestrictions('country');
		elseif (Tools::isSubmit('submitModulecurrency'))
			$this->saveRestrictions('currency');
		elseif (Tools::isSubmit('submitModulegroup'))
			$this->saveRestrictions('group');
	}

	private function saveRestrictions($type)
	{
		global $currentIndex;
		Db::getInstance()->Execute('TRUNCATE '._DB_PREFIX_.'module_'.$type.'');
		foreach ($this->paymentModules as $module)
			if ($module->active AND isset($_POST[$module->name.'_'.$type.'']))
				foreach ($_POST[$module->name.'_'.$type.''] as $selected)
					$values[] = '('.(int)($module->id).', '.(int)($selected).')';

		if (sizeof($values))
			Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'module_'.$type.' (`id_module`, `id_'.$type.'`) VALUES '.implode(',', $values));
		Tools::redirectAdmin($currentIndex.'&conf=4'.'&token='.$this->token);
	}

	public function display()
	{
		global $cookie;

		$tokenModules = Tools::getAdminToken('AdminModules'.(int)(Tab::getIdFromClassName('AdminModules')).(int)($cookie->id_employee));
		echo '<h2 class="space">'.$this->l('Payment modules list').'</h2>';
		if (isset($this->paymentModules[0]))
			echo '<input type="button" class="button" onclick="document.location=\'index.php?tab=AdminModules&token='.$tokenModules.'&module_name='.$this->paymentModules[0]->name.'&tab_module=payments_gateways\'" value="'.$this->l('Click to see the list of payment modules.').'" /><br>';

		$displayRestrictions = false;
		foreach ($this->paymentModules as $module)
			if ($module->active)
				$displayRestrictions= true;
		if ($displayRestrictions)
		{

			echo '<br /><h2 class="space">'.$this->l('Payment module restrictions').'</h2>';
			$textCurrencies = $this->l('Please mark the checkbox(es) for the currency or currencies for which you want the payment module(s) to be available.').'<br /><em>'.$this->l('Only active currencies are listed.').'</em>';
			$textCountries = $this->l('Please mark the checkbox(es) for the country or countries for which you want the payment module(s) to be available.').'<br /><em>'.$this->l('Only active countries are listed.').'</em>';
			$textGroups = $this->l('Please mark the checkbox(es) for the groups for which you want the payment module(s) available.');
			$this->displayModuleRestrictions(Currency::getCurrencies(false, true), $this->l('Currency restrictions'), 'currency', $textCurrencies, 'dollar');
			echo '<br />';
			$this->displayModuleRestrictions(Group::getGroups((int)($cookie->id_lang)), $this->l('Group restrictions'), 'group', $textGroups, 'group');
			echo '<br />';
			$this->displayModuleRestrictions(Country::getCountries((int)$cookie->id_lang, true, false, false), $this->l('Country restrictions'), 'country', $textCountries, 'world');
		}
		else
		{
			echo '<br>';
			echo $this->displayWarning($this->l('No payment module installed'));
		}
	}

	public function displayModuleRestrictions($items, $title, $nameId, $desc, $icon)
	{
		global $currentIndex;
		$irow = 0;

		echo '
		<form action="'.$currentIndex.'&token='.$this->token.'" method="post" id="form_'.$nameId.'">
			<fieldset>
				<legend><img src="../img/admin/'.$icon.'.gif" alt="" />'.$title.'</legend>
				<p>'.$desc.'<p>
				<table cellpadding="0" cellspacing="0" class="table">
					<tr>
						<th style="width: 200px">'.$title.'</th>';
		foreach ($this->paymentModules as $module)
		{
			if ($module->active)
			{
				echo '
						<th>';
				if ($nameId != 'currency' || ($nameId == 'currency' && $module->currencies_mode == 'checkbox'))
					echo '
							<input type="hidden" id="checkedBox_'.$nameId.'_'.$module->name.'" value="checked">
							<a href="javascript:checkPaymentBoxes(\''.$nameId.'\', \''.$module->name.'\')" style="text-decoration:none;">';
				echo '
							&nbsp;<img src="'.__PS_BASE_URI__.'modules/'.$module->name.'/logo.gif" alt="'.$module->name.'" title="'.$module->displayName.'" />';
				if ($nameId != 'currency' || ($nameId == 'currency' && $module->currencies_mode == 'checkbox'))
					echo '
							</a>';
				echo '
						</th>';
			}
		}
		echo '
			</tr>';
		foreach ($items as $item)
		{
			echo '<tr'.($irow++ % 2 ? ' class="alt_row"' : '').'>
					<td>'.$item['name'].'</td>';
			foreach ($this->paymentModules as $module)
			{
				if ($module->active)
				{
					if (isset($module->{$nameId}))
						$value = $module->{$nameId};
					else
						$value = array();
					echo '
						<td style="text-align: center">';

					if ($nameId == 'country' && isset($module->limited_countries) &&
						count($module->limited_countries))
					{
						if (in_array(strtoupper($item['iso_code']), array_map('strtoupper', $module->limited_countries)))
							echo '<input  type="checkbox" name="'.$module->name.'_'.
								$nameId.'[]" value="'.$item['id_'.$nameId].'"'.
								(in_array($item['id_'.$nameId.''], $value) ?
								' checked="checked"' : '').' />';
						else
							echo '--';
					}
					elseif ($nameId != 'currency' OR ($nameId == 'currency' AND $module->currencies AND $module->currencies_mode == 'checkbox'))

						echo '
							<input type="checkbox" name="'.$module->name.'_'.$nameId.'[]" value="'.$item['id_'.$nameId].'"'.(in_array($item['id_'.$nameId.''], $value) ? ' checked="checked"' : '').' />';
					elseif ($nameId == 'currency' AND $module->currencies AND $module->currencies_mode == 'radio')
						echo '
							<input type="radio" name="'.$module->name.'_'.$nameId.'[]" value="'.$item['id_'.$nameId].'"'.(in_array($item['id_'.$nameId.''], $value) ? ' checked="checked"' : '').' />';
					elseif ($nameId == 'currency')
						echo '--';
					echo '
						</td>';
				}
			}
			echo '
					</tr>';
		}
		if ($nameId == 'currency')
		{
			echo '
				<tr'.($irow++ % 2 ? ' class="alt_row"' : '').'>
					<td>'.$this->l('Customer currency').'</td>';
			foreach ($this->paymentModules as $module)
				if ($module->active)
					echo '
					<td style="text-align: center">'.(($module->currencies AND $module->currencies_mode == 'radio') ? '<input type="radio" name="'.$module->name.'_'.$nameId.'[]" value="-1"'.((is_array($module->currency) AND in_array(-1, $module->currency)) ? ' checked="checked"' : '').' />' : '--').'</td>';
			echo '
				</tr>';
			echo '
				<tr'.($irow++ % 2 ? ' class="alt_row"' : '').'>
					<td>'.$this->l('Shop default currency').'</td>';
			foreach ($this->paymentModules as $module)
				if ($module->active)
					echo '
					<td style="text-align: center">'.(($module->currencies AND $module->currencies_mode == 'radio') ? '<input type="radio" name="'.$module->name.'_'.$nameId.'[]" value="-2"'.((is_array($module->currency) AND in_array(-2, $module->currency)) ? ' checked="checked"' : '').' />' : '--').'</td>';
			echo '
				</tr>';
		}
		echo '
				</table>
				<div style="text-align:center;"><input type="submit" class="button space" name="submitModule'.$nameId.'" value="'.$this->l('Save restrictions').'" /></div>
			</fieldset>
		</form>';

	}

}

