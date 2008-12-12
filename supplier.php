<?php

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/header.php');

if (!isset($objectType))
	$objectType = 'supplier';

$className = ucfirst($objectType);
$errors = array();
	
if ($id = intval(Tools::getValue('id_'.$objectType)))
{
	include(dirname(__FILE__).'/product-sort.php');
	
	$object = new $className(intval($id));
	if (!Validate::isLoadedObject($object))
		$errors[] = Tools::displayError('object does not exist');
	else
	{
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
	$data = call_user_func(array($className, 'get'.$className.'s'), true, false, intval($cookie->id_lang), false);
	$imgDir = $objectType == 'supplier' ? _PS_SUPP_IMG_DIR_ : _PS_MANU_IMG_DIR_;
	foreach ($data AS &$item)
		$item['image'] = (!file_exists($imgDir.'/'.$item['id_'.$objectType].'-medium.jpg')) ? 
			Language::getIsoById(intval($cookie->id_lang)).'-default' :	$item['id_'.$objectType];

	$nbProducts = sizeof($data); // pagination hack
	include(dirname(__FILE__).'/pagination.php');
	
	$smarty->assign(array(
		'pages_nb' => ceil(sizeof($data) / intval($n)),
		'nb'.$className.'s' => sizeof($data),
		$objectType.'s' => $data
	));

	$smarty->display(_PS_THEME_DIR_.$objectType.'-list.tpl');
}

include(dirname(__FILE__).'/footer.php');

?>
