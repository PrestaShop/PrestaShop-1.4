<?php



// Display the list of product with category = ipod

echo '<h3>Display the list of product with category = ipod</h3>';


// Display the structure of a product

echo '<h4>Display the structure of a product</h4>';

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
	//displayResource($object);
	
	
}
catch (PrestaShopWebserviceException $e)
{
	displayException($e);
}

// Edit the english product\'s description and its linked categories
echo '<h4>Edit the english product\'s description and its linked categories</h4>';

$listOfIds = array(rand(1,5), rand(6,10));
$xpath = '/p:prestashop/p:product/associations';
$element = reset($xml->xpath($xpath));
$element->categories = new SimpleXmlElement('<categories />');
foreach ($listOfIds as $categId)
  $element->categories->addChild('category')->addChild('id', $categId);

$xpath = '/p:prestashop/p:product/associations/i18n/description/*[@id=1]';
$element = reset($xml->xpath($xpath));
$parent = reset($xml->xpath($xpath.'/..'));
$parent->{$element->getName()} = 'The new english stranslation';
try
{
	$xml = $ws->edit(array('resource' => 'products', 'id' => 1, 'putXml' => $xml->asXml()));
	$namespaces = $xml->getNameSpaces(true);
	$object = $xml->children($namespaces['p'])->product;
	displayResource($object);
}
catch (PrestaShopWebserviceException $e)
{
	displayException($e);
}
die;
