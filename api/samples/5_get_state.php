<?php

/////////////////
// get a state //
/////////////////

echo '<h3>Get a state</h3>';
$id = (string)$fields->id;
try
{
	$xml = $ws->get(array('resource' => 'states', 'id' => $id));
	$namespaces = $xml->getNameSpaces(true);
	$object = $xml->children($namespaces['p'])->state;
	$fields = $object->children();
	displayResource($fields);
}
catch (PrestaShopWebserviceException $e)
{
	displayException($e);
}
