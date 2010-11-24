<?php

//////////////////
// edit a state //
//////////////////

echo '<h3>Edit a state</h3>';
$object->iso_code = strtoupper(base_convert(rand(10, 35), 10, 36).base_convert(rand(10, 35), 10, 36).base_convert(rand(10, 35), 10, 36));
$object->active = true;
try
{
	$xml = $ws->edit(array('resource' => 'states', 'id' => $id, 'putXml' => $xml->asXml()));
	$object = $xml->children()->state;
	$fields = $object->children();
	displayResource($fields);
}
catch (PrestaShopWebserviceException $e)
{
	displayException($e);
}
