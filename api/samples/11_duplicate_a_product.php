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
	$object = $xml->children()->product;
	unset($object->id);
	$xpath = '/p:prestashop/p:product/associations/i18n/name/*[@id=1]';
  $element = reset($xml->xpath($xpath));
  $parent = reset($xml->xpath($xpath.'/..'));
  $parent->{$element->getName()} = $parent->{$element->getName()}.' (copy)';
	$xml = $ws->add(array('resource' => 'products', 'postXml' => $xml->asXml()));
	$object = $xml->children()->product;
	displayResource($object);
}
catch (PrestaShopWebserviceException $e)
{
	displayException($e);
}
