<?php

// Display the list of countries with name in any language = US (we do an error in the name of the lang table name)

echo '<h4>Display the list of countries with name in any language = US (there is an error in the name of the lang table name)</h4>';

try
{
	$object = $ws->get(
		array(
			'resource' => 'countries',
			'filter' => array(
				'language' => array(
				'id_lang' => '1',
				'name' => 'US',
				),
			)
		)
	);
}
catch (PrestaShopWebserviceException $e)
{
	displayException($e);
}
