<?php

/////////////////
// get a state //
/////////////////

echo '<h3>Get a state</h3>';
$id = (string)$fields->id;
try
{
	$xml = $ws->get(array('resource' => 'states', 'id' => $id));
	$object = $xml->children()->state;
	$fields = $object->children();
	displayResource($fields);
}
catch (PrestaShopWebserviceException $e)
{
	displayException($e);
}
