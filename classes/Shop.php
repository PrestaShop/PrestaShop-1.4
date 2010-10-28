<?php

/**
  * Shop class, Shop.php
  * Shop management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

class ShopCore extends ObjectModel
{
	static public function getShops()
	{
		/*return Db::getInstance()->ExecuteS('
			SELECT * FROM `'._DB_PREFIX_.'shops`
		');*/
		return array(
			array('id_shop' => 1, 'name' => 'Default shop')
		);
	}

	static public function getCurrentShop()
	{
		// During implementation, remind you to NOT trust the cookie, you may be called from a payment module (Mouhahahaha!)
		return 1;
	}
}

?>
