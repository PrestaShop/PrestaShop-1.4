<?php

abstract class CarrierModule extends Module
{
	
abstract function getOrderShippingCost($params,$shipping_cost);	


}

abstract function getOrderShippingCostExternal($params);	


}


?>