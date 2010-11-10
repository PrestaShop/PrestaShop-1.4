<?php

// Display the list of countries

echo '<h4>Display the list of countries</h4>';
try
{
	$xml = $ws->get(array('resource' => 'countries'));
	$namespaces = $xml->getNameSpaces(true);
	$resources = $xml->children($namespaces['p'])->countries[0];
	displayResources($resources, $namespaces);
}
catch (PrestaShopWebserviceException $e)
{
	displayException($e);
}
