<?php
/*
* 2007-2011 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  2007-2011 Prestashop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class BlockLayered extends Module
{
	public function __construct()
	{
		$this->name = 'blocklayered';
		$this->tab = 'front_office_features';
		$this->version = 1.1;

		parent::__construct();

		$this->displayName = $this->l('Layered navigation block');
		$this->description = $this->l('Displays a block with layered navigation filters');
	}

	public function install()
	{		
		if ($result = parent::install() AND $this->registerHook('leftColumn') AND $this->registerHook('header'))
			Configuration::updateValue('PS_LAYERED_NAVIGATION_CHECKBOXES', 1);

		return $result;
	}
	
	public function uninstall()
	{
		/* Delete all configurations */
		Configuration::deleteByName('PS_LAYERED_NAVIGATION_CHECKBOXES');
		
		return parent::uninstall();
	}
	
	private function _getLayeredSubcategories($id_category)
	{
		global $cookie;

		/*  We are using GROUP_CONCAT() & the nested tree implementation to have a performance gain */
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT c.id_category, cl.link_rewrite, cl.name, 0 n,
		(SELECT GROUP_CONCAT(c2.id_category) FROM '._DB_PREFIX_.'category c2 WHERE c2.level_depth > c.level_depth AND c2.active = 1 AND c2.nleft > c.nleft and c2.nright < c.nright) subcategories
		FROM '._DB_PREFIX_.'category c
		LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (cl.id_category = c.id_category)
		WHERE c.id_parent = '.(int)$id_category.' AND c.active = 1 AND cl.id_lang = '.(int)$cookie->id_lang.'
		ORDER BY c.position ASC');
	}
	
	public function generateFilters($products = NULL, $filters = NULL)
	{
		global $smarty, $link, $cookie;
		
		/* If the current category isn't defined of if it's homepage, we have nothing to display */
		$id_parent = (int)Tools::getValue('id_category', Tools::getValue('id_category_layered', 1));
		if ($id_parent == 1)
			return;
		
		/* First we need to get all subcategories of current category */
		$layeredSubcategories = $this->_getLayeredSubcategories((int)$id_parent);
		
		/* If we have no results, we should get one level higher */
		if (!sizeof($layeredSubcategories))
			$layeredSubcategories[0] = array('id_category' => (int)$id_parent, 'subcategories' => '', 'subcategoriesArray' => array(), 'n' => 0);
		/*{
			$id_parent_parent = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT id_parent FROM '._DB_PREFIX_.'category WHERE id_category = '.(int)$id_parent);
			if ($id_parent_parent)
				$layeredSubcategories = $this->_getLayeredSubcategories((int)$id_parent_parent);
			else
				return;
		}*/
		
		$categoriesId = (int)$id_parent;
		foreach ($layeredSubcategories AS &$layeredSubcategory)
		{
			$currentCategoriesId = (int)$layeredSubcategory['id_category'].(!empty($layeredSubcategory['subcategories']) ? ','.$layeredSubcategory['subcategories'] : '');
			$categoriesId .= ','.$currentCategoriesId;
			$tmpTab = explode(',', $currentCategoriesId);
			foreach ($tmpTab AS $id_category)
				$layeredSubcategory['subcategoriesArray'][(int)$id_category] = 1;
		}
		
		/* Product condition (New, Used, Refurbished) */
		$layeredConditions = array('new' => array('name' => $this->l('New'), 'n' => 0), 'used' => array('name' => $this->l('Used'), 'n' => 0), 'refurbished' => array('name' => $this->l('Refurbished'), 'n' => 0));
		
		/* Then, we can now retrieve all the associated products */
		if (isset($products))
			$layeredProducts = $products;
		else
			$layeredProducts = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT cp.id_product, cp.id_category, p.id_manufacturer, p.condition
			FROM '._DB_PREFIX_.'category_product cp
			LEFT JOIN '._DB_PREFIX_.'product p ON (p.id_product = cp.id_product)
			WHERE p.active = 1 AND cp.id_category IN ('.pSQL(ltrim($categoriesId, ',')).')');
		
		$countManufacturers = array();
		$countManufacturers[0] = 0; /* Prevent from no manufacturers case */
		$productIds = array(0);
		foreach ($layeredProducts AS $layeredProduct)
		{
			/* Count manufacturers */
			if (!isset($countManufacturers[(int)$layeredProduct['id_manufacturer']]))
				$countManufacturers[(int)$layeredProduct['id_manufacturer']] = 1;
			else
				$countManufacturers[(int)$layeredProduct['id_manufacturer']]++;
				
			/* Count conditions */
			$layeredConditions[$layeredProduct['condition']]['n']++;
			foreach ($layeredConditions AS $key => &$layeredCondition)
			{
				if (isset($filters['condition']) AND sizeof($filters['condition']))
					$layeredCondition['checked'] = in_array('\''.$key.'\'', $filters['condition']) ? 1 : 0;
			}
			
			/* Count categories */
			foreach ($layeredSubcategories AS &$layeredSubcategory)
			{
				if (isset($layeredSubcategory['subcategoriesArray'][(int)$layeredProduct['id_category']]))
					$layeredSubcategory['n']++;
				if (isset($filters['category']) AND sizeof($filters['category']))
					$layeredSubcategory['checked'] = in_array($layeredSubcategory['id_category'], $filters['category']) ? 1 : 0;
			}
			
			/* Build product IDs list */
			$productIds[] = (int)$layeredProduct['id_product'];
		}
		
		/* Get features names */
		$layeredFeatures = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT fp.id_feature
		FROM '._DB_PREFIX_.'feature_product fp
		LEFT JOIN '._DB_PREFIX_.'feature_value fv ON (fv.id_feature_value = fp.id_feature_value)
		WHERE fv.custom = 0 AND fp.id_product IN ('.implode(',', $productIds).')
		GROUP BY fp.id_feature');
		
		$layeredFeaturesIds = array();
		foreach ($layeredFeatures AS $layeredFeature)
			$layeredFeaturesIds[] = (int)$layeredFeature['id_feature'];
		
		$layeredFeatures = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT fl.id_feature, fl.name, fvl.id_feature_value, fvl.value, fp.id_product
		FROM '._DB_PREFIX_.'feature_lang fl
		LEFT JOIN '._DB_PREFIX_.'feature_value fv ON (fv.id_feature = fl.id_feature)
		LEFT JOIN '._DB_PREFIX_.'feature_value_lang fvl ON (fvl.id_feature_value = fv.id_feature_value)
		LEFT JOIN '._DB_PREFIX_.'feature_product fp ON (fp.id_feature_value = fv.id_feature_value)
		WHERE fv.custom = 0 AND fl.id_feature IN ('.implode(',', $layeredFeaturesIds).') AND fl.id_lang = '.(int)$cookie->id_lang.' AND fvl.id_lang = '.(int)$cookie->id_lang);
		
		$sortedLayeredFeatures = array();
		foreach ($layeredFeatures AS $layeredFeature)
		{
			$sortedLayeredFeatures[(int)$layeredFeature['id_feature']]['name'] = $layeredFeature['name'];
			$sortedLayeredFeatures[(int)$layeredFeature['id_feature']]['values'][(int)$layeredFeature['id_feature_value']]['name'] = $layeredFeature['value'];
			
			/* Count product for feach feature value */
			if (!isset($sortedLayeredFeatures[(int)$layeredFeature['id_feature']]['values'][(int)$layeredFeature['id_feature_value']]['n']))
					$sortedLayeredFeatures[(int)$layeredFeature['id_feature']]['values'][(int)$layeredFeature['id_feature_value']]['n'] = 0;
					
			if (!empty($layeredFeature['id_product']))
				$sortedLayeredFeatures[(int)$layeredFeature['id_feature']]['values'][(int)$layeredFeature['id_feature_value']]['n']++;
		}
		
		/* Get manufacturers names */
		$layeredManufacturers = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT m.id_manufacturer, m.name
		FROM '._DB_PREFIX_.'manufacturer m
		WHERE m.id_manufacturer IN ('.implode(',', array_keys($countManufacturers)).')');
		
		foreach ($layeredManufacturers AS &$layeredManufacturer)
		{			
			$layeredManufacturer['n'] = (int)$countManufacturers[(int)$layeredManufacturer['id_manufacturer']];			
			if (isset($filters['manufacturer']) AND sizeof($filters['manufacturer']))
				$layeredManufacturer['checked'] = in_array($layeredManufacturer['id_manufacturer'], $filters['manufacturer']) ? 1 : 0;
		}
		
		$smarty->assign(array(
		'id_category_layered' => (int)$id_parent,
		'layered_features' => $sortedLayeredFeatures,
		'layered_subcategories' => $layeredSubcategories,
		'layered_manufacturers' => $layeredManufacturers,
		'layered_conditions' => $layeredConditions,
		'layered_use_checkboxes' => 1)); /* We need to add this option in the admin panel (int)Configuration::get('PS_LAYERED_NAVIGATION_CHECKBOXES'))); */

		return $smarty->fetch(_PS_MODULE_DIR_.$this->name.'/blocklayered.tpl');
	}
   
	public function hookLeftColumn($params)
	{
		return $this->generateFilters();
	}
	
	public function ajaxCall()
	{
		global $smarty, $cookie;
		
		$output = array();
		$filters = array('category' => array(), 'manufacturer' => array(), 'condition' => array());
		foreach ($_GET AS $key => $value)
			if (substr($key, 0, 8) == 'layered_')
			{
				$tmpTab = explode('_', $key);
				if (isset($tmpTab[1]))
				{
					switch ($tmpTab[1])
					{
						case 'category':
							$filters['category'][] = (int)$value;
							break;
							
						case 'manufacturer':
							$filters['manufacturer'][] = (int)$value;
							break;
							
						case 'condition':
							if (in_array($value, array('new', 'used', 'refurbished')))
								$filters['condition'][] = '\''.$value.'\'';
							break;
							
						default:
							continue(2);
					}
				}
				else
					continue;
			}
		
		$categoriesID = '';
		if (empty($filters['category']))
			$filters['category'][] = (int)Tools::getValue('id_category_layered', 0);
		$categoriesID .= implode($filters['category'], ',').',';

		foreach ($filters['category'] AS $id_category)
		{
			$layeredSubcategories = $this->_getLayeredSubcategories((int)$id_category);
			if (sizeof($layeredSubcategories))
				foreach ($layeredSubcategories AS $layeredSubcategory)
				{
					$categoriesID .= (int)$layeredSubcategory['id_category'].',';
					if (isset($layeredSubcategory['subcategories']))
						$categoriesID .= $layeredSubcategory['subcategories'].',';
				}
		}
		$categoriesID = rtrim($categoriesID, ',');
		
		/*
		*
		* Todo:
		*
		* - Add a check on the category_group table
		* - Add other filters (prices, attributes & colors, weight)
		* - Manage products sort & pagination
		* - Manage SEO links (no ajax actions in JS disabled, real links instead)
		* - Test on a large catalog & improve performances
		* - Add admin panel options
		* - Manage the breadcrumb (+ ability to delete a selected filter)
		* - Real time URL building + ability to give the URL to someone
		* 
		*/
		
		$products = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT cp.id_product, pa.id_product_attribute, p.*, pl.description_short, pl.link_rewrite, pl.name, i.id_image, il.legend, m.name manufacturer_name,
		DATEDIFF(p.`date_add`, DATE_SUB(NOW(), INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY)) > 0 AS new,
		cp.id_category
		FROM '._DB_PREFIX_.'category_product cp
		LEFT JOIN '._DB_PREFIX_.'product p ON (p.id_product = cp.id_product)
		LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = p.id_product)
		LEFT JOIN '._DB_PREFIX_.'image i ON (i.id_product = p.id_product AND i.cover = 1)
		LEFT JOIN '._DB_PREFIX_.'image_lang il ON (i.id_image = il.id_image AND il.id_lang = '.(int)($cookie->id_lang).')
		LEFT JOIN '._DB_PREFIX_.'product_attribute pa ON (p.id_product = pa.id_product AND default_on = 1)
		LEFT JOIN '._DB_PREFIX_.'manufacturer m ON (m.id_manufacturer = p.id_manufacturer)
		WHERE p.active = 1 AND pl.id_lang = '.(int)$cookie->id_lang.' AND cp.id_category IN ('.$categoriesID.')
		'.(sizeof($filters['manufacturer']) ? ' AND p.id_manufacturer IN ('.implode($filters['manufacturer'], ',').')' : '').'
		'.(sizeof($filters['condition']) ? ' AND p.condition IN ('.implode($filters['condition'], ',').')' : '').'
		GROUP BY cp.id_product');

		$products = Product::getProductsProperties((int)$cookie->id_lang, $products);

		$smarty->assign(array(
			'products' => $products,
			'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY')));

		$filterProducts = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT cp.id_product, p.condition, p.id_manufacturer, cp.id_category
		FROM '._DB_PREFIX_.'category_product cp
		LEFT JOIN '._DB_PREFIX_.'product p ON (p.id_product = cp.id_product)
		WHERE p.active = 1 AND cp.id_category IN ('.$categoriesID.')');
			
		$output['layered_block_left'] = $this->generateFilters($filterProducts, $filters);		
		$output['product_list'] = $smarty->fetch(_PS_THEME_DIR_.'product-list.tpl');

		return Tools::jsonEncode($output);
	}
	
	public function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params);
	}
	
	public function hookHeader($params)
	{
		Tools::addJS(($this->_path).'blocklayered.js');
		Tools::addCSS(($this->_path).'blocklayered.css', 'all');
	}
}