<?php

abstract class ModuleCarrier extends Module
{
	
/** @var boolean True if the carrier need Range */
public $needRange = false;
	
abstract function getOrderShippingCost($params);	


}


?>