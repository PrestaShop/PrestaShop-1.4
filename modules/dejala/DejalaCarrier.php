<?php
class		DejalaCarrier extends Carrier
{
	
	public function __construct($id = NULL, $id_lang = NULL)
	{
		parent::__construct($id, $id_lang);
//		print "============== Dejala Carrier instanciated<br>\n" ;
	}

	public function setDeliveryPrice($price)
	{
//		print "============ Setting price to : " . $price . "<br>\n" ;
		parent::$priceByWeight[$this->id] = $price ;
		parent::$priceByPrice[$this->id] = $price ;
	}

}
?>
