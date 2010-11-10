<?php

/////////////////
// add a state //
/////////////////

echo '<h3>Add a state</h3>';
try
{
	$postArgs = array('attributes' => array(
			'name' => 'PrestaLand', 'active' => 0,
			'id_country' => 3, 'id_zone' => 2,
			'iso_code' => 'PSL', 'tax_behavior' => 0
	));
	$xml = $ws->add(array('resource' => 'states', 'postArgs' => $postArgs));
	$namespaces = $xml->getNameSpaces(true);
	$object = $xml->children($namespaces['p'])->state;
	$fields = $object->children();
	displayResource($fields);
}
catch (PrestaShopWebserviceException $e)
{
	displayException($e);
}
