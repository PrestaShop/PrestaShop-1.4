<?php



// Display the list of product with category = ipod

echo '<h4>Display the list of product with category = ipod</h4>';


// Display the structure of a product

try
{
	$xml = $ws->get(
		array(
			'resource' => 'products',
			'id' => 1
			)
	);
	$namespaces = $xml->getNameSpaces(true);
	$resources = $xml->children($namespaces['p'])->products[0];
	displayResources($resources, $namespaces);
}
catch (PrestaShopWebserviceException $e)
{
	displayException($e);
}


// Display the category ipod

try
{
	$xml = $ws->get(
		array(
			'resource' => 'categories',
			'filter' => array(
				'i18n' => array(
				'id_lang' => '1',
				'name' => '%[ipod]%',
				),
			)
		)
	);
	$namespaces = $xml->getNameSpaces(true);
	$resources = $xml->children($namespaces['p'])->categories[0];
	displayResources($resources, $namespaces);
}
catch (PrestaShopWebserviceException $e)
{
	displayException($e);
}


// Display the products of the category ipod

try
{
	$xml = $ws->get(
		array(
			'resource' => 'products',
			'i' => 1
			)
	);
	$namespaces = $xml->getNameSpaces(true);
	$resources = $xml->children($namespaces['p'])->products[0];
	displayResources($resources, $namespaces);
}
catch (PrestaShopWebserviceException $e)
{
	displayException($e);
}
