<?php



// Display the list of categories with name containing a "a"

echo '<h3>Display the list of categories with name containing a "a"</h3>';
try
{
	$xml = $ws->get(
		array(
			'resource' => 'categories',
			'filter' => array(
				'i18n' => array(
				'id_lang' => '1',
				'name' => '%[a]%',
				),
			)
		)
	);
	$namespaces = $xml->getNameSpaces(true);
	$resources = $xml->children()->categories[0];
	displayResources($resources, $namespaces);
}
catch (PrestaShopWebserviceException $e)
{
	displayException($e);
}
