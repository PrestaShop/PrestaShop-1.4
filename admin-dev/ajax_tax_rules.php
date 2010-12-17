<?php
define('PS_ADMIN_DIR', getcwd());
include(PS_ADMIN_DIR.'/../config/config.inc.php');
include(PS_ADMIN_DIR.'/functions.php');
include(PS_ADMIN_DIR.'/tabs/AdminTaxRulesGroup.php');
/* Getting cookie or logout */
require_once(dirname(__FILE__).'/init.php');

if (Tools::getValue('token') != Tools::getAdminTokenLite('AdminTaxRule') AND
	Tools::getValue('token') != Tools::getAdminTokenLite('AdminProducts') AND
	Tools::getValue('token') != Tools::getAdminTokenLite('AdminTaxRulesGroup')
	)
	die(1);


if (Tools::isSubmit('getCountryTaxes'))
{
	$id_country = (int)Tools::getValue('id_country');
	$id_lang = (int)Tools::getValue('id_lang');

	if (empty($id_country) OR empty($id_lang))
		die(Tools::displayError());

	$id_taxes = Country::getIdsOfAssociatedTaxes($id_country);
	$taxes = array();
	foreach ($id_taxes AS $id_tax)
		$taxes[] = new Tax($id_tax, $id_lang);

	die(Tools::jsonEncode($taxes));
}

if (Tools::isSubmit('submitGetTaxRulesSelect'))
{
	$id_group = (int)Tools::getValue('id_group');

	if (empty($id_group))
		die(Tools::displayError());

	die(AdminTaxRulesGroup::renderTaxRulesSelect($id_group,  Tools::getValue('token')));
}

if (Tools::isSubmit('submitAddRule'))
{
	$id_group = (int)Tools::getValue('id_group');
	$id_rule  = (int)Tools::getValue('id_rule');

	if (empty($id_group) OR empty($id_rule))
		die(Tools::displayError());

	if (TaxRulesGroup::addRuleStatic($id_group, $id_rule))
		die(AdminTaxRulesGroup::renderTaxRulesList($id_group, Tools::getValue('token')));

	die(Tools::displayError());
}


if (Tools::isSubmit('submitRemoveRule'))
{
	$id_group = (int)Tools::getValue('id_group');
	$id_rule  = (int)Tools::getValue('id_rule');

	if (empty($id_group) OR empty($id_rule))
		die(Tools::displayError());

	if (TaxRulesGroup::removeRuleStatic($id_group, $id_rule))
		die(AdminTaxRulesGroup::renderTaxRulesList($id_group, Tools::getValue('token')));
}

