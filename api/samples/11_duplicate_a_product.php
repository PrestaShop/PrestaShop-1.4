<?php



// Duplicate a product

echo '<h3>Duplicate a product</h3>';


try
{
	$xml = $ws->get(
		array(
			'resource' => 'products',
			'id' => 1
			)
	);
	$namespaces = $xml->getNameSpaces(true);
	$object = $xml->children($namespaces['p'])->product;
	unset($object->id);
	$xpath = '/p:prestashop/p:product/associations/i18n/name/*[@id=1]';
  $element = reset($xml->xpath($xpath));
  $parent = reset($xml->xpath($xpath.'/..'));
  $parent->{$element->getName()} = $parent->{$element->getName()}.' (copy)';
	$xml = $ws->add(array('resource' => 'products', 'postXml' => $xml->asXml()));
	$namespaces = $xml->getNameSpaces(true);
	$object = $xml->children($namespaces['p'])->product;
	displayResource($object);
}
catch (PrestaShopWebserviceException $e)
{
	displayException($e);
}
