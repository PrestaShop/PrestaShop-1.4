<?php

// Display the list of countries with name in any language = LIKE US%

echo '<h4>Display the list of countries with name in any language = LIKE US%</h4>';
try
{
	$xml = $ws->get(
		array(
			'resource' => 'countries',
			'filter' => array(
				'i18n' => array(
				'id_lang' => '1',
				'name' => '[US]%',
				),
			)
		)
	);
	$namespaces = $xml->getNameSpaces(true);
	$resources = $xml->children($namespaces['p'])->countries[0];
	displayResources($resources, $namespaces);
}
catch (PrestaShopWebserviceException $e)
{
	displayException($e);
}
