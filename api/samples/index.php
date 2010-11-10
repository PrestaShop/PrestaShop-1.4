<?php
// PrestaShop RESTful Web Service : manage states
// Author: Lucas CHERIFI, PrestaShop
// October 4, 2010

// settings
$ws_url = 'http://dev.cherifi.info/v1';
$ws_auth_key = 'N4OURKQYXPI3WKIU0F49EWUEWYZFX8K2';
$ws_lib_path = 'PrestaShopWebservice.php';
$debug = true;

// config
echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
error_reporting(E_ALL);
ini_set('display_error', 'on');
require_once('PrestaShopWebservice.php');

// useful fonction for these tests

function displayResource($fields) {
	echo '<div style="max-height:350px;overflow:auto;display:table;">';
	echo displayResourceRecursive($fields);
	echo '</div>';
	if (isset($fields->name))
		echo 'This resource is <strong>'.$fields->name.'</strong>.<hr />';
}

function displayResourceRecursive($data) {
  $ret = '';
  $values = $data->children();
  if (count($values) > 0)
  {
    $ret .= '<table border="1"><tr><th>Field</td><th>Value</th></tr>';
    foreach ($values as $name => $value)
	    $ret .= '<tr><td>'.$name.'</td><td>'.displayResourceRecursive($value).'</td></tr>';
	  $ret .= '</table>';
	}
	else
	  $ret .= $data;
	return $ret;
}

function displayResources($resources, $namespaces) {
	echo '<div style="max-height:350px;overflow:auto;display:table;"><table border="1"><tr><th>Id</td><th>Link to this resource</th></tr>';
	foreach ($resources as $resource)
		echo '<tr><td>'.$resource->attributes().'</td><td>'.$resource->attributes($namespaces['xlink']).'</td></tr>';
	echo '</table></div>';
}

function displayException($e) {
	echo '
		<div style="max-height:350px;overflow:auto;display:table;border:1px dashed red;padding:15px;background-color:pink">
		<h4>A webservice error has been catched...</h4>
		<div>'.$e.'</div>
		</div>';
}

// instanciate PrestaShop Webservice library
$ws = new PrestaShopWebservice($ws_url, $ws_auth_key, $debug);
/*
require_once('1_get_list_of_states.php');
require_once('2_get_last_state.php');
require_once('3_get_list_of_states_of_america.php');
require_once('3_1_get_countries.php');
require_once('3_2_get_filtered_countries_with_error.php');
require_once('3_3_get_filtered_countries_empty.php');
require_once('3_4_get_filtered_countries_evolved.php');
require_once('3_5_get_filtered_states_for_one_country.php');
require_once('4_add_state.php');
require_once('5_get_state.php');
require_once('6_edit_state.php');
require_once('7_delete_state.php');
require_once('8_get_not_existing_state.php');
require_once('9_get_filtered_category.php');*/
//require_once('10_edit_i18n_and_associations_of_a_product.php');
require_once('11_duplicate_a_product.php');



