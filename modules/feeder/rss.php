<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

// Get data
$number = (intval(Tools::getValue('n')) ? intval(Tools::getValue('n')) : 10);
$orderByValues = array(0 => 'name', 1 => 'price', 2 => 'date_add', 3 => 'date_upd', 4 => 'position');
$orderWayValues = array(0 => 'ASC', 1 => 'DESC');
$orderBy = Tools::strtolower(Tools::getValue('orderby', $orderByValues[intval(Configuration::get('PS_PRODUCTS_ORDER_BY'))]));
$orderWay = Tools::strtoupper(Tools::getValue('orderway', $orderWayValues[intval(Configuration::get('PS_PRODUCTS_ORDER_WAY'))]));
if (!in_array($orderBy, $orderByValues))
	$orderBy = $orderByValues[0];
if (!in_array($orderWay, $orderWayValues))
	$orderWay = $orderWayValues[0];
$id_category = (intval(Tools::getValue('id_category')) ? intval(Tools::getValue('id_category')) : 1);
$products = Product::getProducts(intval($cookie->id_lang), 0, ($number > 10 ? 10 : $number), $orderBy, $orderWay, $id_category, true);
$currency = new Currency(intval($cookie->id_currency));
$affiliate = (Tools::getValue('ac') ? '?ac='.intval(Tools::getValue('ac')) : '');

// Send feed
header("Content-Type:text/xml; charset=utf-8");
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
?>
<rss version="2.0">
	<channel>
		<title><![CDATA[<?php echo Configuration::get('PS_SHOP_NAME') ?>]]></title>
		<link><?php echo _PS_BASE_URL_.__PS_BASE_URI__; ?></link>
		<mail><?php echo Configuration::get('PS_SHOP_EMAIL') ?></mail>
		<generator>PrestaShop</generator>
		<language><?php echo Language::getIsoById(intval($cookie->id_lang)); ?></language>
		<image>
			<title><![CDATA[<?php echo Configuration::get('PS_SHOP_NAME') ?>]]></title>
			<url><?php echo _PS_BASE_URL_.__PS_BASE_URI__.'img/logo.jpg'; ?></url>
			<link><?php echo _PS_BASE_URL_.__PS_BASE_URI__; ?></link>
		</image>
<?php
	foreach ($products AS $product)
	{
		$image = Image::getImages(intval($cookie->id_lang), $product['id_product']);
		echo "\t\t<item>\n";
		echo "\t\t\t<title><![CDATA[".$product['name']." - ".html_entity_decode(Tools::displayPrice(Product::getPriceStatic($product['id_product']), $currency), ENT_COMPAT, 'UTF-8')." ]]></title>\n";
		echo "\t\t\t<description>&lt;img src=&quot;"._PS_BASE_URL_.__PS_BASE_URI__."img/p/".$image[0]['id_product']."-".$image[0]['id_image']."-small.jpg&quot; title=&quot;".$product['name']."&quot; alt=&quot;thumb&quot; /&gt;
		<![CDATA[".$product['description_short']."]]></description>\n";
		echo "\t\t\t<link><![CDATA[".htmlspecialchars($link->getproductLink($product['id_product'], $product['link_rewrite'], intval(Tools::getValue('id_category')))).$affiliate."]]></link>\n";
		echo "\t\t</item>\n";
	}
?>
	</channel>
</rss>
