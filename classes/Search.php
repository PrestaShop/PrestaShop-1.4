<?php

/**
  * Search class, Search.php
  * Search management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

define('PS_SEARCH_MAX_WORD_LENGTH', 15);
  
/* Copied from Drupal search module, except for -\x{2f} in SEARCH_EXCLUDE because it's used to exclude words in a search */
define('PREG_CLASS_SEARCH_EXCLUDE',
'\x{0}\x{3a}-\x{40}\x{5b}-\x{60}\x{7b}-\x{bf}\x{d7}\x{f7}\x{2b0}-'.
'\x{385}\x{387}\x{3f6}\x{482}-\x{489}\x{559}-\x{55f}\x{589}-\x{5c7}\x{5f3}-'.
'\x{61f}\x{640}\x{64b}-\x{65e}\x{66a}-\x{66d}\x{670}\x{6d4}\x{6d6}-\x{6ed}'.
'\x{6fd}\x{6fe}\x{700}-\x{70f}\x{711}\x{730}-\x{74a}\x{7a6}-\x{7b0}\x{901}-'.
'\x{903}\x{93c}\x{93e}-\x{94d}\x{951}-\x{954}\x{962}-\x{965}\x{970}\x{981}-'.
'\x{983}\x{9bc}\x{9be}-\x{9cd}\x{9d7}\x{9e2}\x{9e3}\x{9f2}-\x{a03}\x{a3c}-'.
'\x{a4d}\x{a70}\x{a71}\x{a81}-\x{a83}\x{abc}\x{abe}-\x{acd}\x{ae2}\x{ae3}'.
'\x{af1}-\x{b03}\x{b3c}\x{b3e}-\x{b57}\x{b70}\x{b82}\x{bbe}-\x{bd7}\x{bf0}-'.
'\x{c03}\x{c3e}-\x{c56}\x{c82}\x{c83}\x{cbc}\x{cbe}-\x{cd6}\x{d02}\x{d03}'.
'\x{d3e}-\x{d57}\x{d82}\x{d83}\x{dca}-\x{df4}\x{e31}\x{e34}-\x{e3f}\x{e46}-'.
'\x{e4f}\x{e5a}\x{e5b}\x{eb1}\x{eb4}-\x{ebc}\x{ec6}-\x{ecd}\x{f01}-\x{f1f}'.
'\x{f2a}-\x{f3f}\x{f71}-\x{f87}\x{f90}-\x{fd1}\x{102c}-\x{1039}\x{104a}-'.
'\x{104f}\x{1056}-\x{1059}\x{10fb}\x{10fc}\x{135f}-\x{137c}\x{1390}-\x{1399}'.
'\x{166d}\x{166e}\x{1680}\x{169b}\x{169c}\x{16eb}-\x{16f0}\x{1712}-\x{1714}'.
'\x{1732}-\x{1736}\x{1752}\x{1753}\x{1772}\x{1773}\x{17b4}-\x{17db}\x{17dd}'.
'\x{17f0}-\x{180e}\x{1843}\x{18a9}\x{1920}-\x{1945}\x{19b0}-\x{19c0}\x{19c8}'.
'\x{19c9}\x{19de}-\x{19ff}\x{1a17}-\x{1a1f}\x{1d2c}-\x{1d61}\x{1d78}\x{1d9b}-'.
'\x{1dc3}\x{1fbd}\x{1fbf}-\x{1fc1}\x{1fcd}-\x{1fcf}\x{1fdd}-\x{1fdf}\x{1fed}-'.
'\x{1fef}\x{1ffd}-\x{2070}\x{2074}-\x{207e}\x{2080}-\x{2101}\x{2103}-\x{2106}'.
'\x{2108}\x{2109}\x{2114}\x{2116}-\x{2118}\x{211e}-\x{2123}\x{2125}\x{2127}'.
'\x{2129}\x{212e}\x{2132}\x{213a}\x{213b}\x{2140}-\x{2144}\x{214a}-\x{2b13}'.
'\x{2ce5}-\x{2cff}\x{2d6f}\x{2e00}-\x{3005}\x{3007}-\x{303b}\x{303d}-\x{303f}'.
'\x{3099}-\x{309e}\x{30a0}\x{30fb}-\x{30fe}\x{3190}-\x{319f}\x{31c0}-\x{31cf}'.
'\x{3200}-\x{33ff}\x{4dc0}-\x{4dff}\x{a015}\x{a490}-\x{a716}\x{a802}\x{a806}'.
'\x{a80b}\x{a823}-\x{a82b}\x{d800}-\x{f8ff}\x{fb1e}\x{fb29}\x{fd3e}\x{fd3f}'.
'\x{fdfc}-\x{fe6b}\x{feff}-\x{ff0f}\x{ff1a}-\x{ff20}\x{ff3b}-\x{ff40}\x{ff5b}-'.
'\x{ff65}\x{ff70}\x{ff9e}\x{ff9f}\x{ffe0}-\x{fffd}');

define('PREG_CLASS_NUMBERS',
'\x{30}-\x{39}\x{b2}\x{b3}\x{b9}\x{bc}-\x{be}\x{660}-\x{669}\x{6f0}-\x{6f9}'.
'\x{966}-\x{96f}\x{9e6}-\x{9ef}\x{9f4}-\x{9f9}\x{a66}-\x{a6f}\x{ae6}-\x{aef}'.
'\x{b66}-\x{b6f}\x{be7}-\x{bf2}\x{c66}-\x{c6f}\x{ce6}-\x{cef}\x{d66}-\x{d6f}'.
'\x{e50}-\x{e59}\x{ed0}-\x{ed9}\x{f20}-\x{f33}\x{1040}-\x{1049}\x{1369}-'.
'\x{137c}\x{16ee}-\x{16f0}\x{17e0}-\x{17e9}\x{17f0}-\x{17f9}\x{1810}-\x{1819}'.
'\x{1946}-\x{194f}\x{2070}\x{2074}-\x{2079}\x{2080}-\x{2089}\x{2153}-\x{2183}'.
'\x{2460}-\x{249b}\x{24ea}-\x{24ff}\x{2776}-\x{2793}\x{3007}\x{3021}-\x{3029}'.
'\x{3038}-\x{303a}\x{3192}-\x{3195}\x{3220}-\x{3229}\x{3251}-\x{325f}\x{3280}-'.
'\x{3289}\x{32b1}-\x{32bf}\x{ff10}-\x{ff19}');

define('PREG_CLASS_PUNCTUATION',
'\x{21}-\x{23}\x{25}-\x{2a}\x{2c}-\x{2f}\x{3a}\x{3b}\x{3f}\x{40}\x{5b}-\x{5d}'.
'\x{5f}\x{7b}\x{7d}\x{a1}\x{ab}\x{b7}\x{bb}\x{bf}\x{37e}\x{387}\x{55a}-\x{55f}'.
'\x{589}\x{58a}\x{5be}\x{5c0}\x{5c3}\x{5f3}\x{5f4}\x{60c}\x{60d}\x{61b}\x{61f}'.
'\x{66a}-\x{66d}\x{6d4}\x{700}-\x{70d}\x{964}\x{965}\x{970}\x{df4}\x{e4f}'.
'\x{e5a}\x{e5b}\x{f04}-\x{f12}\x{f3a}-\x{f3d}\x{f85}\x{104a}-\x{104f}\x{10fb}'.
'\x{1361}-\x{1368}\x{166d}\x{166e}\x{169b}\x{169c}\x{16eb}-\x{16ed}\x{1735}'.
'\x{1736}\x{17d4}-\x{17d6}\x{17d8}-\x{17da}\x{1800}-\x{180a}\x{1944}\x{1945}'.
'\x{2010}-\x{2027}\x{2030}-\x{2043}\x{2045}-\x{2051}\x{2053}\x{2054}\x{2057}'.
'\x{207d}\x{207e}\x{208d}\x{208e}\x{2329}\x{232a}\x{23b4}-\x{23b6}\x{2768}-'.
'\x{2775}\x{27e6}-\x{27eb}\x{2983}-\x{2998}\x{29d8}-\x{29db}\x{29fc}\x{29fd}'.
'\x{3001}-\x{3003}\x{3008}-\x{3011}\x{3014}-\x{301f}\x{3030}\x{303d}\x{30a0}'.
'\x{30fb}\x{fd3e}\x{fd3f}\x{fe30}-\x{fe52}\x{fe54}-\x{fe61}\x{fe63}\x{fe68}'.
'\x{fe6a}\x{fe6b}\x{ff01}-\x{ff03}\x{ff05}-\x{ff0a}\x{ff0c}-\x{ff0f}\x{ff1a}'.
'\x{ff1b}\x{ff1f}\x{ff20}\x{ff3b}-\x{ff3d}\x{ff3f}\x{ff5b}\x{ff5d}\x{ff5f}-'.
'\x{ff65}');  

class Search
{
	public static function sanitize($string, $id_lang, $indexation = false)
	{
		$string = Tools::strtolower(strip_tags($string));
		$string = html_entity_decode($string, ENT_NOQUOTES, 'utf-8');

		$string = preg_replace('/(['.PREG_CLASS_NUMBERS.']+)['.PREG_CLASS_PUNCTUATION.']+(?=['.PREG_CLASS_NUMBERS.'])/u', '\1', $string);
		if ($indexation)
		{
			$string = preg_replace('/[._-]+/', '', $string);
			$string = preg_replace('/['.PREG_CLASS_SEARCH_EXCLUDE.'-\x{2f}]+/u', ' ', $string);
		}
		else
		{
			$string = preg_replace('/[._]+/', '', $string);
			$string = ltrim(preg_replace('/([^ ])-/', '$1', ' '.$string));
			$string = preg_replace('/[._]+/', '', $string);
			$string = preg_replace('/[^\s]-+/', '', $string);
			$string = preg_replace('/['.PREG_CLASS_SEARCH_EXCLUDE.']+/u', ' ', $string);
		}
		$blacklist = Configuration::get('PS_SEARCH_BLACKLIST', $id_lang);
		if (!empty($blacklist))
		{
			$string = preg_replace('/(?<=\s)('.$blacklist.')(?=\s)/Su', '', $string);
			$string = preg_replace('/^('.$blacklist.')(?=\s)/Su', '', $string);
			$string = preg_replace('/(?<=\s)('.$blacklist.')$/Su', '', $string);
			$string = preg_replace('/^('.$blacklist.')$/Su', '', $string);
		}
		
		if (!$indexation)
		{
			$alias = new Alias(NULL, $string);
			if (Validate::isLoadedObject($alias))
				$string = $alias->search;
		}
		
		if ($indexation)
		{
			$minWordLen = Configuration::get('PS_SEARCH_MINWORDLEN');
			if ($minWordLen)
			{
				$string = preg_replace('/(?<=\s)[^\s]{1,'.$minWordLen.'}(?=\s)/Su', ' ', $string);
				$string = preg_replace('/^[^\s]{1,'.$minWordLen.'}(?=\s)/Su', '', $string);
				$string = preg_replace('/(?<=\s)[^\s]{1,'.$minWordLen.'}$/Su', '', $string);
				$string = preg_replace('/^[^\s]{1,'.$minWordLen.'}$/Su', '', $string);
			}
		}
		
		$string = trim(preg_replace('/\s+/', ' ', $string));
		return $string;
	}

	public static function find($id_lang, $expr, $pageNumber = 1, $pageSize = 1, $orderBy = 'position', $orderWay = 'desc', $ajax = false)
	{
		global $cookie;

		// TODO : smart page management
		if ($pageNumber < 1) $pageNumber = 1;
		if ($pageSize < 1) $pageSize = 1;
		
		if (!Validate::isOrderBy($orderBy) OR !Validate::isOrderWay($orderWay))
			die(Tools::displayError());
			
		$whereArray = array();
		$scoreArray = array();
		$words = explode(' ', Search::sanitize($expr, $id_lang));

		foreach ($words as $key => $word)
			if (!empty($word))
			{
				$word = str_replace('%', '\\%', $word);
				$word = str_replace('_', '\\_', $word);
				$whereArray[] = ' p.id_product '.($word[0] == '-' ? 'NOT' : '').' IN (
					SELECT id_product
					FROM '._DB_PREFIX_.'search_word sw
					LEFT JOIN '._DB_PREFIX_.'search_index si ON sw.id_word = si.id_word
					WHERE sw.id_lang = '.intval($id_lang).'
					AND sw.word LIKE '.($word[0] == '-' ? ' \''.pSQL(substr($word, 1, PS_SEARCH_MAX_WORD_LENGTH)).'%\'' : '\''.pSQL(substr($word, 0, PS_SEARCH_MAX_WORD_LENGTH)).'%\'').'
				) ';
				if ($word[0] != '-')
					$scoreArray[] = 'sw.word LIKE \''.pSQL(substr($word, 0, PS_SEARCH_MAX_WORD_LENGTH)).'%\'';
			}
			else
				unset($words[$key]);
		if (!sizeof($words))
			return ($ajax ? array() : array('total' => 0, 'result' => array()));
			
		$score = '';
		if (sizeof($scoreArray))
			$score = ',(
				SELECT SUM(weight)
				FROM '._DB_PREFIX_.'search_word sw
				LEFT JOIN '._DB_PREFIX_.'search_index si ON sw.id_word = si.id_word
				WHERE sw.id_lang = '.intval($id_lang).'
				AND si.id_product = p.id_product
				AND ('.implode(' OR ', $scoreArray).')
			) as position';
		
		if ($ajax)
		{
			$queryResults = '
			SELECT p.id_product, pl.name as pname, IF(cl.name REGEXP "^[0-9]{2}\\.", SUBSTRING(cl.name, 4), cl.name) as cname	'.$score.', cl.link_rewrite as crewrite, pl.link_rewrite as prewrite
			FROM '._DB_PREFIX_.'product p
			LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (p.`id_category_default` = cl.`id_category` AND cl.`id_lang` = '.intval($id_lang).')
			WHERE '.implode(' AND ', $whereArray).'
			AND p.active = 1
			AND p.`id_product` IN (
				SELECT cp.`id_product`
				FROM `'._DB_PREFIX_.'category_group` cg
				LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
				WHERE cg.`id_group` '.(!$cookie->id_customer ?  '= 1' : 'IN (SELECT id_group FROM '._DB_PREFIX_.'customer_group WHERE id_customer = '.intval($cookie->id_customer).')').'
			)
			ORDER BY position DESC
			LIMIT 10';
			return Db::getInstance()->ExecuteS($queryResults);
		}

		$queryResults = '
		SELECT SQL_CALC_FOUND_ROWS p.*, pl.`description_short`, pl.`available_now`, pl.`available_later`, pl.`link_rewrite`, pl.`name`,
		t.`rate`, i.`id_image`, il.`legend`, m.`name` AS manufacturer_name 
		'.$score.'
		FROM '._DB_PREFIX_.'product p
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
		LEFT OUTER JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'tax` t ON (p.`id_tax` = t.`id_tax`)
		LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
		WHERE '.implode(' AND ', $whereArray).'
		AND p.active = 1
		AND p.`id_product` IN (
			SELECT cp.`id_product`
			FROM `'._DB_PREFIX_.'category_group` cg
			LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
			WHERE cg.`id_group` '.(!$cookie->id_customer ?  '= 1' : 'IN (SELECT id_group FROM '._DB_PREFIX_.'customer_group WHERE id_customer = '.intval($cookie->id_customer).')').'
		)
		'.($orderBy ? 'ORDER BY  '.$orderBy : '').($orderWay ? ' '.$orderWay : '').'
		LIMIT '.intval(($pageNumber - 1) * $pageSize).','.intval($pageSize);
		$result = Db::getInstance()->ExecuteS($queryResults);
		$total = Db::getInstance()->getValue('SELECT FOUND_ROWS()');

		Module::hookExec('search', array('expr' => $expr, 'total' => $total));

		return array('total' => $total,'result' => Product::getProductsProperties($id_lang, $result));
	}
	
	public static function getTags($db, $id_product, $id_lang)
	{
		$tags = '';
		$tagsArray = $db->ExecuteS('
		SELECT t.name FROM '._DB_PREFIX_.'product_tag pt
		LEFT JOIN '._DB_PREFIX_.'tag t ON (pt.id_tag = t.id_tag AND t.id_lang = '.intval($id_lang).')
		WHERE pt.id_product = '.intval($id_product));
		foreach ($tagsArray as $tag)
			$tags .= $tag['name'].' ';
		return $tags;
	}

	public static function getAttributes($db, $id_product, $id_lang)
	{
		$attributes = '';
		$attributesArray = $db->ExecuteS('
		SELECT al.name FROM '._DB_PREFIX_.'product_attribute pa
		LEFT JOIN '._DB_PREFIX_.'product_attribute_combination pac ON pa.id_product_attribute = pac.id_product_attribute
		LEFT JOIN '._DB_PREFIX_.'attribute_lang al ON (pac.id_attribute = al.id_attribute AND al.id_lang = '.intval($id_lang).')
		WHERE pa.id_product = '.intval($id_product));
		foreach ($attributesArray as $attribute)
			$attributes .= $attribute['name'].' ';
		return $attributes;
	}

	public static function getFeatures($db, $id_product, $id_lang)
	{
		$features = '';
		$featuresArray = $db->ExecuteS('
		SELECT fvl.value FROM '._DB_PREFIX_.'feature_product fp
		LEFT JOIN '._DB_PREFIX_.'feature_value_lang fvl ON (fp.id_feature_value = fvl.id_feature_value AND fvl.id_lang = '.intval($id_lang).')
		WHERE fp.id_product = '.intval($id_product));
		foreach ($featuresArray as $feature)
			$features .= $feature['value'].' ';
		return $features;
	}
	
	public static function indexation($full = false)
	{
		$db = Db::getInstance();
		
		if ($full)
		{
			$db->Execute('TRUNCATE '._DB_PREFIX_.'search_index');
			$db->Execute('TRUNCATE '._DB_PREFIX_.'search_word');
			$db->Execute('UPDATE '._DB_PREFIX_.'product SET indexed = 0');
		}
		else
			$db->Execute('DELETE FROM '._DB_PREFIX_.'search_index WHERE id_product IN (SELECT id_product FROM '._DB_PREFIX_.'product WHERE indexed = 0)');
	
		$weightArray = array(
			'pname' => Configuration::get('PS_SEARCH_WEIGHT_PNAME'),
			'reference' => Configuration::get('PS_SEARCH_WEIGHT_REF'),
			'ean13' => Configuration::get('PS_SEARCH_WEIGHT_REF'),
			'description_short' => Configuration::get('PS_SEARCH_WEIGHT_SHORTDESC'),
			'description' => Configuration::get('PS_SEARCH_WEIGHT_DESC'),
			'cname' => Configuration::get('PS_SEARCH_WEIGHT_CNAME'),
			'mname' => Configuration::get('PS_SEARCH_WEIGHT_MNAME'),
			'tags' => Configuration::get('PS_SEARCH_WEIGHT_TAG'),
			'attributes' => Configuration::get('PS_SEARCH_WEIGHT_ATTRIBUTE'),
			'features' => Configuration::get('PS_SEARCH_WEIGHT_FEATURE')
		);
		
		$products = $db->ExecuteS('
		SELECT p.id_product, pl.id_lang, pl.name as pname, p.reference, p.ean13, pl.description_short, pl.description, cl.name as cname, m.name as mname
		FROM '._DB_PREFIX_.'product p
		LEFT JOIN '._DB_PREFIX_.'product_lang pl ON p.id_product = pl.id_product
		LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (cl.id_category = p.id_category_default AND pl.id_lang = cl.id_lang)
		LEFT JOIN '._DB_PREFIX_.'manufacturer m ON m.id_manufacturer = p.id_manufacturer
		WHERE p.indexed = 0', false);
		
		while ($product = $db->nextRow($products))
		{
			$product['tags'] = self::getTags($db, $product['id_product'], $product['id_lang']);
			$product['attributes'] = self::getAttributes($db, $product['id_product'], $product['id_lang']);
			$product['features'] = self::getFeatures($db, $product['id_product'], $product['id_lang']);

			$pArray = array();
			foreach ($product as $key => $value)
				if (strncmp($key, 'id_', 3))
				{
					if ($key == 'cname')
						$value = Category::hideCategoryPosition($value);
					$words = explode(' ', self::sanitize($value, $product['id_lang'], true));
					foreach ($words as $word)
						if (!empty($word))
						{
							$word = substr($word, 0, PS_SEARCH_MAX_WORD_LENGTH);
							if (!isset($pArray[$word]))
								$pArray[$word] = $weightArray[$key];
							else
								$pArray[$word] += $weightArray[$key];
						}
				}
			$queryArray = array();
			$queryArray2 = array();
			foreach ($pArray as $word => $weight)
			{
				$queryArray[] = '('.intval($product['id_lang']).',\''.pSQL($word).'\')';
				$queryArray2[] = '('.intval($product['id_product']).',(SELECT id_word FROM '._DB_PREFIX_.'search_word WHERE word = \''.pSQL($word).'\' AND id_lang = '.intval($product['id_lang']).' LIMIT 1),'.intval($weight).')';
			}
			if (sizeof($queryArray) AND sizeof($queryArray2))
			{
				if (!($rows = $db->Execute('INSERT IGNORE INTO '._DB_PREFIX_.'search_word (id_lang, word) VALUES '.implode(',',$queryArray))) OR $rows != sizeof($queryArray))
					Tools::d(array(mysql_error(), $queryArray));
				if (!($rows = $db->Execute('INSERT INTO '._DB_PREFIX_.'search_index (id_product, id_word, weight) VALUES '.implode(',',$queryArray2).' ON DUPLICATE KEY UPDATE weight = weight + VALUES(weight)')) OR $rows != sizeof($queryArray2))
					Tools::d(array(mysql_error(), $queryArray2));
			}
			$db->Execute('UPDATE '._DB_PREFIX_.'product SET indexed = 1 WHERE id_product = '.intval($product['id_product']));
		}
		$db->Execute('DELETE FROM '._DB_PREFIX_.'search_word WHERE id_word NOT IN (SELECT id_word FROM '._DB_PREFIX_.'search_index)');
		return true;
	}
}
	
?>
