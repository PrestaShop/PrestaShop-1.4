<?php

/**
  * Statistics
  * @category stats
  *
  * @author Damien Metzger / Epitech
  * @copyright Epitech / PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  */
  
class Page extends ObjectModel
{
	public $id_page_type;
	public $id_object;
	
	public $name;

	protected $fieldsRequired = array ('id_page_type');	
	protected $fieldsValidate = array ('id_page_type' => 'isUnsignedId', 'id_object' => 'isUnsignedId');

	protected $table = 'page';
	protected $identifier = 'id_page';
	
	public function getFields()
	{
		parent::validateFields();
		$fields['id_page_type'] = intval($this->id_page_type);
		$fields['id_object'] = intval($this->id_object);
		return $fields;
	}
	
	public static function getCurrentId()
	{
		$phpSelf = isset($_SERVER['PHP_SELF']) ? substr($_SERVER['PHP_SELF'], strlen(__PS_BASE_URI__)) : '';
		
		// Some pages must be distinguished in order to record exactly what is being seen
		$specialArray = array(
			'product.php' => 'id_product',
			'category.php' => 'id_category',
			'order.php' => 'step',
			'manufacturer.php' => 'id_manufacturer');
		if (array_key_exists($phpSelf, $specialArray))
		{
			$id_object = Tools::getValue($specialArray[$phpSelf]);
			$result = Db::getInstance()->getRow('
			SELECT p.`id_page`
			FROM `'._DB_PREFIX_.'page` p
			LEFT JOIN `'._DB_PREFIX_.'page_type` pt ON p.`id_page_type` = pt.`id_page_type`
			WHERE pt.`name` = \''.pSQL($phpSelf).'\'
			AND p.`id_object` = \''.intval($id_object).'\'');
			if ($result['id_page'])
				return $result['id_page'];
			else
			{
				Db::getInstance()->Execute('
				INSERT INTO `'._DB_PREFIX_.'page` (`id_page_type`,`id_object`)
				VALUES ((SELECT pt.`id_page_type` FROM `'._DB_PREFIX_.'page_type` pt WHERE pt.`name` = \''.pSQL($phpSelf).'\'),
						'.intval($id_object).')');
				return Db::getInstance()->Insert_ID();
			}
		}
		else
		{
			$result = Db::getInstance()->getRow('
			SELECT p.`id_page`
			FROM `'._DB_PREFIX_.'page` p
			LEFT JOIN `'._DB_PREFIX_.'page_type` pt ON p.`id_page_type` = pt.`id_page_type`
			WHERE pt.`name` = \''.pSQL($phpSelf).'\'');
			if ($result['id_page'])
				return $result['id_page'];
			else
			{
				Db::getInstance()->Execute('
				INSERT INTO `'._DB_PREFIX_.'page_type` (`name`)
				VALUES (\''.pSQL($phpSelf).'\')');
				Db::getInstance()->Execute('
				INSERT INTO `'._DB_PREFIX_.'page` (`id_page_type`)
				VALUES ('.intval(Db::getInstance()->Insert_ID()).')');
				return Db::getInstance()->Insert_ID();
			}
		}
	}
	
	public static function setPageViewed($id_page)
	{
		$id_date_range = DateRange::getCurrentRange();
		
		// Try to increment the visits counter
		Db::getInstance()->Execute('
		UPDATE `'._DB_PREFIX_.'page_viewed`
		SET `counter` = `counter` + 1
		WHERE `id_date_range` = '.intval($id_date_range).'
		AND `id_page` = '.intval($id_page));
		
		// If no one has seen the page in this date range, it is added
		if (Db::getInstance()->Affected_Rows() == 0)
			Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'page_viewed` (`id_date_range`,`id_page`,`counter`)
			VALUES ('.intval($id_date_range).','.intval($id_page).',1)');
	}
}

?>
