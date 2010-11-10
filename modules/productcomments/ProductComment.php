<?php

/**
  * ProductComment class, ProductComment.php
  * Product Comments management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  *
  */

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class ProductComment extends ObjectModel
{
	public 		$id;
	
	/** @var integer Product's id */
	public 		$id_product;
	
	/** @var integer Customer's id */
	public 		$id_customer;

	/** @var integer Guest's id */
	public 		$id_guest;
	
	
	/** @var integer Customer name */
	public 		$customer_name;
	
	/** @var string Content */
	public 		$content;
	
	/** @var integer Grade */
	public 		$grade;
	
	/** @var boolean Validate */
	public 		$validate = 0;
	
	public 		$deleted = 0;
	
	/** @var string Object creation date */
	public 		$date_add;
	
 	protected 	$fieldsRequired = array('id_product', 'id_customer', 'content');
 	protected 	$fieldsSize = array('content' => 65535);
 	protected 	$fieldsValidate = array('id_product' => 'isUnsignedId', 'id_customer' => 'isUnsignedId', 'content' => 'isMessage',
		'grade' => 'isFloat', 'validate' => 'isBool');

	protected 	$table = 'product_comment';
	protected 	$identifier = 'id_product_comment';

	public	function getFields()
	{
	 	parent::validateFields(false);
		$fields['id_product'] = intval($this->id_product);
		$fields['id_customer'] = intval($this->id_customer);
		$fields['id_guest'] = intval($this->id_guest);
		$fields['customer_name'] = pSQL($this->customer_name);
		$fields['content'] = pSQL($this->content);
		$fields['grade'] = floatval($this->grade);
		$fields['validate'] = intval($this->validate);
		$fields['deleted'] = intval($this->deleted);
		$fields['date_add'] = pSQL($this->date_add);
		return ($fields);
	}
	
	/**
	 * Get comments by IdProduct
	 *
	 * @return array Comments
	 */
	static public function getByProduct($id_product, $p = 1, $n = null)
	{
		if (!Validate::isUnsignedId($id_product))
			die(Tools::displayError());
		$validate = Configuration::get('PRODUCT_COMMENTS_MODERATE');
		$p = intval($p);
		$n = intval($n);
		if ($p <= 1)
			$p = 1;
		if ($n != null AND $n <= 0)
			$n = 5;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT pc.`id_product_comment`, IF(c.id_customer, CONCAT(c.`firstname`, \' \',  LEFT(c.`lastname`, 1)), pc.customer_name) customer_name, pc.`content`, pc.`grade`, pc.`date_add`
		  FROM `'._DB_PREFIX_.'product_comment` pc
		LEFT JOIN `'._DB_PREFIX_.'customer` c ON c.`id_customer` = pc.`id_customer`
		WHERE pc.`id_product` = '.intval($id_product).($validate == '1' ? ' AND pc.`validate` = 1' : '').'
		ORDER BY pc.`date_add` DESC
		'.($n ? 'LIMIT '.intval(($p - 1) * $n).', '.intval($n) : ''));
	}
	
	static public function getByCustomer($id_product, $id_customer, $last = false, $id_guest = false)
	{
		$results = Db::getInstance()->ExecuteS('
		SELECT * 
		FROM `'._DB_PREFIX_.'product_comment` pc
		WHERE pc.`id_product` = '.intval($id_product).' AND '.(!$id_guest ? 'pc.`id_customer` = '.intval($id_customer) : 'pc.`id_guest` = '.intval($id_guest)).'
		ORDER BY pc.`date_add` DESC '
		.($last ? 'LIMIT 1' : '')
		);
		
		if ($last) 
			return array_shift($results);
		
		return $results;
	}
	
	/**
	 * Get Grade By product
	 *
	 * @return array Grades
	 */
	static public function getGradeByProduct($id_product, $id_lang)
	{
		if (!Validate::isUnsignedId($id_product) ||
			!Validate::isUnsignedId($id_lang))
			die(Tools::displayError());
		$validate = Configuration::get('PRODUCT_COMMENTS_MODERATE');

		return (Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT pc.`id_product_comment`, pcg.`grade`, pccl.`name`, pcc.`id_product_comment_criterion`
		FROM `'._DB_PREFIX_.'product_comment` pc
		LEFT JOIN `'._DB_PREFIX_.'product_comment_grade` pcg ON (pcg.`id_product_comment` = pc.`id_product_comment`)
		LEFT JOIN `'._DB_PREFIX_.'product_comment_criterion` pcc ON (pcc.`id_product_comment_criterion` = pcg.`id_product_comment_criterion`)
		LEFT JOIN `'._DB_PREFIX_.'product_comment_criterion_lang` pccl ON (pccl.`id_product_comment_criterion` = pcg.`id_product_comment_criterion`)
		WHERE pc.`id_product` = '.intval($id_product).'
		AND pccl.`id_lang` = '.intval($id_lang).
		($validate == '1' ? ' AND pc.`validate` = 1' : '')));
	}

	static public function getAveragesByProduct($id_product, $id_lang)
	{
		/* Get all grades */
		$grades = ProductComment::getGradeByProduct(intval($id_product), intval($id_lang));
		$total = ProductComment::getGradedCommentNumber(intval($id_product));
		if (!sizeof($grades) OR (!$total))
			return array();

		/* Addition grades for each criterion */
		$criterionsGradeTotal = array();
		for ($i = 0; $i < count($grades); ++$i)
			if (array_key_exists($grades[$i]['id_product_comment_criterion'], $criterionsGradeTotal) === false)
				$criterionsGradeTotal[$grades[$i]['id_product_comment_criterion']] = intval($grades[$i]['grade']);
			else
				$criterionsGradeTotal[$grades[$i]['id_product_comment_criterion']] += intval($grades[$i]['grade']);

		/* Finally compute the averages */
		$averages = array();
		foreach ($criterionsGradeTotal AS $key => $criterionGradeTotal)
			$averages[intval($key)] = intval($total) ? (intval($criterionGradeTotal) / intval($total)) : 0;
		return $averages;
	}

	/**
	 * Return number of comments and average grade by products
	 *
	 * @return array Info
	 */
	static public function getCommentNumber($id_product)
	{
		if (!Validate::isUnsignedId($id_product))
			die(Tools::displayError());
		$validate = intval(Configuration::get('PRODUCT_COMMENTS_MODERATE'));
		if (($result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT COUNT(`id_product_comment`) AS "nbr"
		FROM `'._DB_PREFIX_.'product_comment` pc
		WHERE `id_product` = '.intval($id_product).($validate == '1' ? ' AND `validate` = 1' : ''))) === false)
			return false;
		return intval($result['nbr']);
	}

	/**
	 * Return number of comments and average grade by products
	 *
	 * @return array Info
	 */
	static public function getGradedCommentNumber($id_product)
	{
		if (!Validate::isUnsignedId($id_product))
			die(Tools::displayError());
		$validate = intval(Configuration::get('PRODUCT_COMMENTS_MODERATE'));

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT COUNT(pc.`id_product`) AS nbr
		FROM `'._DB_PREFIX_.'product_comment` pc
		WHERE `id_product` = '.intval($id_product).($validate == '1' ? ' AND `validate` = 1' : '').'
		AND `grade` > 0');
		return intval($result['nbr']);
	}

	/**
	 * Get comments by Validation
	 *
	 * @return array Comments
	 */
	static public function getByValidate($validate = '0', $deleted = false)
	{
		global $cookie;

		return (Db::getInstance()->ExecuteS('
		SELECT pc.`id_product_comment`, pc.`id_product`, IF(c.id_customer, CONCAT(c.`firstname`, \' \',  c.`lastname`), pc.customer_name) customer_name, pc.`content`, pc.`grade`, pc.`date_add`, pl.`name`
		FROM `'._DB_PREFIX_.'product_comment` pc
		LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = pc.`id_customer`) 
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = pc.`id_product`) 
		WHERE pc.`validate` = '.intval($validate).'
		AND pl.`id_lang` = '.intval($cookie->id_lang).'
		ORDER BY pc.`date_add` DESC'));
	}
	
	/**
	 * Validate a comment
	 *
	 * @return boolean succeed
	 */
	public function validate($validate = '1')
	{
		if (!Validate::isUnsignedId($this->id))
			die(Tools::displayError());
		return (Db::getInstance()->Execute('
		UPDATE `'._DB_PREFIX_.'product_comment` SET
		`validate` = '.intval($validate).'
		WHERE `id_product_comment` = '.intval($this->id)));
	}
	
	/**
	 * Delete Grades
	 *
	 * @return boolean succeed
	 */
	static public function deleteGrades($id_product_comment)
	{
		if (!Validate::isUnsignedId($id_product_comment))
			die(Tools::displayError());
		return (Db::getInstance()->Execute('
		DELETE FROM `'._DB_PREFIX_.'product_comment_grade`
		WHERE `id_product_comment` = '.intval($id_product_comment)));
	}
};
