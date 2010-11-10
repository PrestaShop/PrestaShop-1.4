<?php

// Display the list of states with id_country = [the value we found]

echo '<h4>Display the list of states with id_country = [the value we found]</h4>';

try
{
	$xml = $ws->get(array('resource' => 'states', 'filter' => array('id_country' => 21)));
	$namespaces = $xml->getNameSpaces(true);
	$resources = $xml->children($namespaces['p'])->states[0];
	displayResources($resources, $namespaces);
}
catch (PrestaShopWebserviceException $e)
{
	displayException($e);
}
