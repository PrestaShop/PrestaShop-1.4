<?php

////////////////////////////
// get the list of states //
////////////////////////////

echo '<h3>Get the list of states</h3>';

try
{
	$xml = $ws->get(array('resource' => 'states'));
	$namespaces = $xml->getNameSpaces(true);
	$resources = $xml->children($namespaces['p'])->states[0];
	displayResources($resources, $namespaces);
}
catch (PrestaShopWebserviceException $e)
{
	displayException($e);
}
