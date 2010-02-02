<?php

global $cookie;

include(dirname(__FILE__).'/blockcart.php');

$cart = new Cart(intval($cookie->id_cart));
$cart->id_lang = intval($cookie->id_lang);

$blockCart = new BlockCart();
echo $blockCart->hookAjaxCall(array('cookie' => $cookie, 'cart' => $cart));

?>