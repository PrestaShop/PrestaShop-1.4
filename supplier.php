<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

//will be initialized bellow...
if(intval(Configuration::get('PS_REWRITING_SETTINGS')) === 1)
	$rewrited_url = null;

if (!isset($objectType))
	$objectType = 'supplier';

$className = ucfirst($objectType);
$errors = array();
	
if ($id = intval(Tools::getValue('id_'.$objectType)))
{
	include(dirname(__FILE__).'/product-sort.php');
	
	$object = new $className(intval($id), $cookie->id_lang);
	if (!Validate::isLoadedObject($object))
		$errors[] = Tools::displayError('object does not exist');
	else
	{
		/* rewrited url set */
		if ($objectType == 'supplier')
			$rewrited_url = $link->getSupplierLink($object->id, $object->link_rewrite);
		elseif ($objectType == 'manufacturer')
			$rewrited_url = $link->getManufacturerLink($object->id, $object->link_rewrite);
		include(dirname(__FILE__).'/header.php');
		
		$nbProducts = $object->getProducts($id, NULL, NULL, NULL, $orderBy, $orderWay, true);
		include(dirname(__FILE__).'/pagination.php');
		$smarty->assign(array(
			'nb_products' => $nbProducts,
			'products' => $object->getProducts($id, intval($cookie->id_lang), intval($p), intval($n), $orderBy, $orderWay),
			$objectType => $object));
	}
	
	$smarty->assign(array(
		'errors' => $errors,
		'path' => Tools::safeOutput($object->name),
		'id_lang' => intval($cookie->id_lang),
	));
	$smarty->display(_PS_THEME_DIR_.$objectType.'.tpl');
}
else
{
	include(dirname(__FILE__).'/header.php');
	$data = call_user_func(array($className, 'get'.$className.'s'), false, intval($cookie->id_lang), true);
	$nbProducts = sizeof($data);
	include(dirname(__FILE__).'/pagination.php');

	$data = call_user_func(array($className, 'get'.$className.'s'), true, intval($cookie->id_lang), true, $p, $n);
	$imgDir = $objectType == 'supplier' ? _PS_SUPP_IMG_DIR_ : _PS_MANU_IMG_DIR_;
	foreach ($data AS &$item)
		$item['image'] = (!file_exists($imgDir.'/'.$item['id_'.$objectType].'-medium.jpg')) ? 
			Language::getIsoById(intval($cookie->id_lang)).'-default' :	$item['id_'.$objectType];

	$smarty->assign(array(
		'pages_nb' => ceil($nbProducts / intval($n)),
		'nb'.$className.'s' => $nbProducts,
		$objectType.'s' => $data
	));
	$smarty->display(_PS_THEME_DIR_.$objectType.'-list.tpl');
}

include(dirname(__FILE__).'/footer.php');

?>
