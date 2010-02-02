<?php

/**
  * ProductCommentCriterion class, ProductCommentCriterion.php
  * Product Comments Criterion management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */
class ProductCommentCriterion
{
	/**
	 * Add a Comment Criterion
	 *
	 * @return boolean succeed
	 */
	static public function add($id_lang, $name)
	{
		if (!Validate::isUnsignedId($id_lang) ||
			!Validate::isMessage($name))
			die(Tools::displayError());
		return (Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'product_comment_criterion`
		(`id_lang`, `name`) VALUES(
		'.intval($id_lang).',
		\''.pSQL($name).'\')'));
	}
	
	/**
	 * Link a Comment Criterion to a product
	 *
	 * @return boolean succeed
	 */
	static public function addToProduct($id_product_comment_criterion, $id_product)
	{
		if (!Validate::isUnsignedId($id_product_comment_criterion) ||
			!Validate::isUnsignedId($id_product))
			die(Tools::displayError());
		return (Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'product_comment_criterion_product`
		(`id_product_comment_criterion`, `id_product`) VALUES(
		'.intval($id_product_comment_criterion).',
		'.intval($id_product).')'));
	}
	
	/**
	 * Add grade to a criterion
	 *
	 * @return boolean succeed
	 */
	static public function addGrade($id_product_comment, $id_product_comment_criterion, $grade)
	{
		if (!Validate::isUnsignedId($id_product_comment) ||
			!Validate::isUnsignedId($id_product_comment_criterion))
			die(Tools::displayError());
		if ($grade < 0)
			$grade = 0;
		else if ($grade > 10)
			$grade = 10;
		return (Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'product_comment_grade`
		(`id_product_comment`, `id_product_comment_criterion`, `grade`) VALUES(
		'.intval($id_product_comment).',
		'.intval($id_product_comment_criterion).',
		'.intval($grade).')'));
	}
	
	/**
	 * Update criterion
	 *
	 * @return boolean succeed
	 */
	static public function update($id_product_comment_criterion, $id_lang, $name)
	{
		if (!Validate::isUnsignedId($id_product_comment_criterion) ||
			!Validate::isUnsignedId($id_lang) ||
			!Validate::isMessage($name))
			die(Tools::displayError());
		return (Db::getInstance()->Execute('
		UPDATE `'._DB_PREFIX_.'product_comment_criterion` SET
		`name` = \''.pSQL($name).'\'
		WHERE `id_product_comment_criterion` = '.intval($id_product_comment_criterion).' AND
		`id_lang` = '.intval($id_lang)));
	}
	
	/**
	 * Get criterion by Product
	 *
	 * @return array Criterion
	 */
	static public function getByProduct($id_product, $id_lang)
	{
		if (!Validate::isUnsignedId($id_product) ||
			!Validate::isUnsignedId($id_lang))
			die(Tools::displayError());
		return (Db::getInstance()->ExecuteS('
		SELECT pcc.`id_product_comment_criterion`, pcc.`name`
		FROM `'._DB_PREFIX_.'product_comment_criterion` pcc
		INNER JOIN `'._DB_PREFIX_.'product_comment_criterion_product` pccp ON pcc.`id_product_comment_criterion` = pccp.`id_product_comment_criterion`
		WHERE pccp.`id_product` = '.intval($id_product).' AND 
		pcc.`id_lang` = '.intval($id_lang)));
	}
	
	/**
	 * Get Criterions
	 *
	 * @return array Criterions
	 */
	static public function get($id_lang)
	{
		if (!Validate::isUnsignedId($id_lang))
			die(Tools::displayError());
		return (Db::getInstance()->ExecuteS('
		SELECT pcc.`id_product_comment_criterion`, pcc.`name`
		  FROM `'._DB_PREFIX_.'product_comment_criterion` pcc
		WHERE pcc.`id_lang` = '.intval($id_lang).'
		ORDER BY pcc.`name` ASC'));
	}
	
	/**
	 * Delete product criterion by product
	 *
	 * @return boolean succeed
	 */
	static public function deleteByProduct($id_product)
	{
		if (!Validate::isUnsignedId($id_product))
			die(Tools::displayError());
		return (Db::getInstance()->Execute('
		DELETE FROM `'._DB_PREFIX_.'product_comment_criterion_product`
		WHERE `id_product` = '.intval($id_product)));
	}
	
	/**
	 * Delete all reference of a criterion
	 *
	 * @return boolean succeed
	 */
	static public function delete($id_product_comment_criterion)
	{
		if (!Validate::isUnsignedId($id_product_comment_criterion))
			die(Tools::displayError());
		$result = Db::getInstance()->Execute('
		DELETE FROM `'._DB_PREFIX_.'product_comment_grade`
		WHERE `id_product_comment_criterion` = '.intval($id_product_comment_criterion));
		if ($result === false)
			return ($result);
		$result = Db::getInstance()->Execute('
		DELETE FROM `'._DB_PREFIX_.'product_comment_criterion_product`
		WHERE `id_product_comment_criterion` = '.intval($id_product_comment_criterion));
		if ($result === false)
			return ($result);
		return (Db::getInstance()->Execute('
		DELETE FROM `'._DB_PREFIX_.'product_comment_criterion`
		WHERE `id_product_comment_criterion` = '.intval($id_product_comment_criterion)));
	}
};