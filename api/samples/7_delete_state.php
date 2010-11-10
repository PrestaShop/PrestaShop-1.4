<?php

////////////////////
// delete a state //
////////////////////

echo '<h3>Delete a state</h3>';
try
{
	echo $ws->delete(array('resource' => 'states', 'id' => $id)) ? 'The state #'.$id.' has been successfully deleted' : 'An error occured when deleting this state';
}
catch (PrestaShopWebserviceException $e)
{
	displayException($e);
}
