<?php

//////////////////////////////
// get a not existing state //
//////////////////////////////

echo '<h3>Get a not existing state</h3>';
try
{
	$object = $ws->get(array('resource' => 'states', 'id' => $id));
}
catch (PrestaShopWebserviceException $e)
{
	echo 'The state #'.$id.' was not found.';
}
