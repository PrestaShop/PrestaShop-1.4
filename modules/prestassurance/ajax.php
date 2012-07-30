<?php
/*
* 2007-2011 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once('prestassurance.php');
include_once('classes/psaRequest.php');
include_once('classes/psaDisaster.php');

if (Tools::getValue('token') != sha1(_COOKIE_KEY_.'prestassurance'))
	die('INVALID TOKEN');

global $cookie;
$psa = new prestassurance();

$culture = new Language((int)$cookie->id_lang);

if (Tools::isSubmit('getCgvPsa'))
{
	$request = new psaRequest($culture->iso_code.'/cgv/fma.json');
	$request->setUsername(Configuration::get('PSA_ID_MERCHANT'));
	$request->setPassword(Configuration::get('PSA_KEY'));
	$request->execute();
	$response = Tools::jsonDecode($request->getResponseBody());

	if (isset($response->updated_at) AND (strtotime($response->updated_at)) > strtotime(Configuration::get('PSA_CGV_UPDATED')))
		$response->upToDate = true;
	else
		$response->upToDate = false;

	die(Tools::jsonEncode($response));
}

if (Tools::isSubmit('getCgvHelp'))
{
	$request = new psaRequest($culture->iso_code.'/cgv/help.json');
	$request->setUsername(Configuration::get('PSA_ID_MERCHANT'));
	$request->setPassword(Configuration::get('PSA_KEY'));
	$request->execute();
	die($request->getResponseBody());
}

if (Tools::isSubmit('updateCGVDate'))
{
	if (Configuration::updateValue('PSA_CGV_UPDATED', date('Y-m-d h:m:s')))
		die('{"ok": true}');
}

if (Tools::isSubmit('getChildrenCategories'))
{
	$id_category_parent = (int)Tools::getValue('id');
	$children_categories = $psa->getChildrenCategories($id_category_parent);
	die(Tools::jsonEncode($children_categories));
}

if (Tools::isSubmit('getHomeCategories'))
{
	$home_categories = $psa->getHomeCategories();
	die(Tools::jsonEncode($home_categories));
}

if (Tools::isSubmit('getFmaSubCategories') AND $id_category = Tools::getValue('id_category'))
{
	die($psa->getFmaSubCategory((int)$id_category));
}

if (Tools::isSubmit('saveMatchCategory') 
	AND $id_category = Tools::getValue('id_category') 
	AND $id_psa_category = Tools::getValue('id_psa_category') 
	AND $minimum_price = Tools::getValue('minimum_price')
	AND $name = Tools::getValue('name')
	AND $maximum_price = Tools::getValue('maximum_price')
	AND $maximum_product_price = Tools::getValue('maximum_product_price')
	AND $minimum_product_price = Tools::getValue('minimum_product_price'))
{
	die(Tools::jsonEncode($psa->saveMatchCategory((int)$id_category, (int)$id_psa_category, (float)$minimum_price, $name, (float)$maximum_price, (float)$minimum_product_price, (float)$maximum_product_price)));
}

if (Tools::isSubmit('saveImpact') 
	AND $id_category = Tools::getValue('id_category') 
	AND $impact_type = Tools::getValue('impact_type')
	AND $impact_value = Tools::getValue('impact_value')
	AND $selling_price = Tools::getValue('selling_price')
	AND $benefit = Tools::getValue('benefit'))
{
	die(Tools::jsonEncode($psa->saveImpact((int)$id_category, $impact_type, (float)$impact_value, (float)$selling_price, (float)$benefit)));
}

if (Tools::isSubmit('displaySelectCategoryWidget') AND $id_category = Tools::getValue('id_category'))
{
	$psa->getCategoriesMatch($id_category);
	die($psa->displaySelectCategoryWidget($id_category));
}

if (Tools::isSubmit('setCategory') AND $id_category = Tools::getValue('id_category'))
{
	$categories = $psa->getRootCategories();
	$html = '<script type="text/javascript">cat_psa_cache["0"] = '.$categories.'; </script>
			<div class="hint clear" style="display:block;margin:10px">
				<p style="text-align:justify">Pour choisir une catégorie d\'assurance cliquez sur la liste ci-dessous afin de naviguer dans l\'arborescence.
				Vous devez choisir dans la liste la catégorie qui correspond le mieux aux articles que vous vendez dans cette catégorie.</p>
			</div>';
	
	$categories = Tools::jsonDecode($categories);
	
	if (isset($categories->has_errors))
	{
		$html .= '<div style="border: 1px solid #EC9B9B;background-color: #FAE2E3;">
					<table id="fma_category">
						<tr><td style="padding:10px">';
		foreach($categories->message as $message)
			$html .= $message.'<br/>';
		$html .= '</td></tr></table></div>';
	
	}
	else
	{
		$html .= '
				<table id="fma_category">
					<tr>
						<td id="level_0">
							<select class="fma_categories" id="fma_category_level_0" size="30" onchange="getFmaSubCategories(parseInt(this.value), 0);">';
						foreach($categories as $category)
							$html .= '<option value="'.$category->id.'">'.$category->name.'</option>';
	$html .=			'</select>
						</td>
					</tr>
				</table>';
	}
	die($html);
}

if (Tools::isSubmit('setImpact') AND $id_category = Tools::getValue('id_category'))
{
	$impact_value = Configuration::get('PSA_DEFAULT_IMPACT_VALUE');
	$psa->getCategoriesMatch((int)$id_category);
	$conf = $psa->categoriesMatch[(int)$id_category];
	
	if ($conf['impact_value'] == 0)
		$conf['impact_value'] = Tools::ps_round($conf['minimum_price'], 2);
	
	$conf['selling_price'] = $psa->calcFinalPrice($conf['minimum_price'], $conf['impact_value'], $conf['maximum_price']);
	$conf['benefit'] = $psa->calcBenefit($conf['selling_price']);
	die($psa->displayPriceForm($id_category, $conf));
}
if (Tools::isSubmit('applyMatchingChildren') AND $id_category = Tools::getValue('id_category'))
{
	$category = new Category((int)$id_category); 
	$psa->getCategoriesMatch((int)$id_category);
	$conf = $psa->categoriesMatch[(int)$id_category];
		
	$sub_categories = Db::getInstance()->ExecuteS('SELECT `id_category` FROM `ps_category` c WHERE c.`nleft` > '.(int)$category->nleft.' AND c.`nright` < '.(int)$category->nright.'');
	
	$html = array();
	foreach($sub_categories as $category)
	{
		$tmp = $psa->saveMatchCategory((int)$category['id_category'], (int)$conf['id_psa_category'], (float)$conf['minimum_price'], $conf['name'], (float)$conf['maximum_price'], (float)$conf['minimum_product_price'], (float)$conf['maximum_product_price']);
		
		$html[] = array_merge(array('id_category' => $category['id_category']), $tmp['html']);
	}
	
	die(Tools::jsonEncode(
		array(
			'hasError' => false, 
			'html' => $html
			)
		));
}

if (Tools::isSubmit('applyPriceChildren') AND $id_category = Tools::getValue('id_category'))
{
	$category = new Category((int)$id_category); 
	$psa->getCategoriesMatch((int)$id_category);
	$conf = $psa->categoriesMatch[(int)$id_category];
		
	$sub_categories = Db::getInstance()->ExecuteS('SELECT `id_category` FROM `ps_category` c WHERE c.`nleft` > '.(int)$category->nleft.' AND c.`nright` < '.(int)$category->nright.'');
	
	$html = array();
	foreach($sub_categories as $category)
	{
		$tmp = $psa->saveImpact((int)$category['id_category'], 'fixed_price', (float)$conf['impact_value'], (float)$conf['selling_price'], (float)$conf['benefit']);
		$html[] = array_merge(array('id_category' => $category['id_category']), $tmp['html']);
	}
	
	die(Tools::jsonEncode(
		array(
			'hasError' => false, 
			'html' => $html
			)
		));
}
if (Tools::isSubmit('calcFinalPriceAndBenefit') 
	AND $minimum_price = Tools::getValue('minimum_price') 
	AND $maximum_price = Tools::getValue('maximum_price')
	AND $impact_value = Tools::getValue('impact_value'))
{
	$return['selling_price'] = $psa->calcFinalPrice((float)$minimum_price, (float)$impact_value, (float)$maximum_price);
	$return['benefit'] = $psa->calcBenefit((float)$return['selling_price']);
	die(Tools::jsonEncode($return));
}

if (Tools::isSubmit('reSubmitSouscription') AND $id_order = Tools::getValue('id_order'))
{
	global $cookie;
	$order = new Order((int)$id_order);
	$cart = new Cart((int)$order->id_cart);
	$psa->processSuscription(array('order' => $order, 'cart' => $cart, 'cookie' => $cookie));
	
	die($psa->hookadminOrder(array('id_order' => (int)$id_order)));
}

if (Tools::isSubmit('validateSignIn') AND Tools::isSubmit('PSA_ID_MERCHANT') AND Tools::isSubmit('PSA_KEY'))
{
	$post_fields = array('PSA_ID_MERCHANT' => Tools::getValue('PSA_ID_MERCHANT'),
						 'PSA_KEY' => Tools::getValue('PSA_KEY'),
						 'insurer_id' => Tools::getValue('insurer_id'),
						 'insurer_limited_country' => Tools::getValue('insurer_limited_country')
						 );
	$request = new psaRequest('signin/step/4.json', 'POST', $post_fields);
	$request->setUsername(Tools::getValue('PSA_ID_MERCHANT'));
	$request->setPassword(Tools::getValue('PSA_KEY'));
	$request->execute();
	
	$response = (array)Tools::jsonDecode($request->getResponseBody());
	
	if ($response['result'] == 'OK')
	{
		$psa->saveSignInInfos(Tools::getValue('PSA_ID_MERCHANT'), Tools::getValue('PSA_KEY'));
		$psa->updateLimitedInsurerCountry($post_fields['insurer_id'], $post_fields['insurer_limited_country']);
		Configuration::updateValue('PSA_INSURER_ID', (int)$post_fields['insurer_id']);
		die('OK');
	}
}

if (Tools::isSubmit('updateLimitedInsurerCountry') AND $insurer_id =  Tools::getValue('insurer_id') AND $insurer_limited_country = Tools::getValue('insurer_limited_country'))
{
	$psa->updateLimitedInsurerCountry($insurer_id, $insurer_limited_country);
}

if (Tools::isSubmit('exportConfig'))
{
	
	$data = $psa->exportConfig();
	$request = new psaRequest('config_customer/save', 'POST', $data);
	$request->setUsername(Configuration::get('PSA_ID_MERCHANT'));
	$request->setPassword(Configuration::get('PSA_KEY'));
	$request->execute();
	die($request->getResponseBody());
}

if (Tools::isSubmit('importConfig') AND $unique_id =  Tools::getValue('unique_id') AND $email =  Tools::getValue('email'))
{
	$data = array(
		'id_marchant' => $unique_id,
		'email' => $email,
		'signin_url_return' => $psa->getSignInUrlReturn()
		);
	
	$request = new psaRequest('config_customer/get', 'POST', $data);
	$request->setUsername(Configuration::get('PSA_ID_MERCHANT'));
	$request->setPassword(Configuration::get('PSA_KEY'));
	$request->execute();
	$response = (array)Tools::jsonDecode($request->getResponseBody());

	if (!$response['hasErrors'])
		die(Tools::jsonEncode(array('hasErrors' => !$psa->importConfig($response['config']))));
	else
		die(Tools::jsonEncode(array('hasErrors' => true)));
}

if (Tools::isSubmit('deleteMatching') AND $id_category =  Tools::getValue('id_category'))
{
	die(Tools::jsonEncode($psa->deleteMatching($id_category)));
}


if (Tools::isSubmit('addNewComment') AND $id_psa_disaster =  Tools::getValue('id_psa_disaster') AND $comment =  Tools::getValue('comment'))
{
	
	$id_disaster = Db::getInstance()->getValue('SELECT `id_disaster` FROM `'._DB_PREFIX_.'psa_disaster` WHERE `id_psa_disaster` ='.(int)$id_psa_disaster);
	$psa->addDisasterComment($id_disaster, 0, $comment);
}

if (Tools::isSubmit('changeDisasterStatus') AND $id_psa_disaster =  Tools::getValue('id_psa_disaster') AND $status =  Tools::getValue('status'))
{
	$id_disaster = Db::getInstance()->getValue('SELECT `id_disaster` FROM `'._DB_PREFIX_.'psa_disaster` WHERE `id_psa_disaster` ='.(int)$id_psa_disaster); //TODO faire one methode pour dans psqDisaster
		
	$disaster = new psaDisaster((int)$id_disaster);
	$disaster->status = $status;
	$disaster->save();
}

