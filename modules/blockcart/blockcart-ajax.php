<?php

global $cookie;

//call module
include(dirname(__FILE__).'/blockcart.php');
$cart = new Cart(intval($cookie->id_cart));
$cart->id_lang = intval($cookie->id_lang);
$hookArgs = array();

$hookArgs['cookie'] = $cookie;
$hookArgs['cart'] = $cart;

//show module
$blockCart = new BlockCart();

echo $blockCart->hookAjaxCall($hookArgs);
?>