<?php
/*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 9074 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class shoppingfluxexport extends Module
{

	public function __construct()
	{
	 	$this->name = 'shoppingfluxexport';
	 	$this->tab = 'smart_shopping';
	 	$this->version = '1.5.1';

	 	parent::__construct();

		$this->displayName = $this->l('Export Shopping Flux');
		$this->description = $this->l('Exportez vos produits vers plus de 100 comparateurs de prix et places de marché');
		$this->confirmUninstall = $this->l('Êtes-vous sur de vouloir supprimer ce module ?');
	}

	public function install()
	{
		// Create Token
		if (!Configuration::updateValue('SHOPPING_FLUX_TOKEN', md5(rand())))
			return false;

		// Install Module
		if (!parent::install())
			return false;

		return true;
	}

	public function uninstall()
	{
		// Delete Token
		if (!Configuration::deleteByName('SHOPPING_FLUX_TOKEN'))
			return false;

		// Uninstall Module
		if (!parent::uninstall())
			return false;

		return true;
	}

	public function getContent()
	{
		//uri feed
		$uri = 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/shoppingfluxexport/flux.php?token='.Configuration::get('SHOPPING_FLUX_TOKEN');
		//uri images
		$uri_img = 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/shoppingfluxexport/screens/';
		//owner object
		$owner = new Employee(1);
		//post process
		if (isset($_POST['envoi_mail']) && $_POST['envoi_mail'] != NULL)
			$this->sendMail();
		
		//first fieldset
		$this->_html .= '<h2>'.$this->displayName.'</h2>
		<fieldset>
				<legend>'.$this->l('Informations').'</legend>
				<p><b>'.$this->l('Shopping Flux vous permettra de :').'</b></p>
				<p>
					<ol>
						<li>'.$this->l('Promouvoir vos produits sur plus  de 100 Comparateurs de Prix (Google Shopping, Leguide.com, Kelkoo, Cherchons.com, etc…)').'</li>
						<li>'.$this->l('Choisir les produits que vous souhaitez diffuser et en calculer la rentabilité en fonction du Comparateur de Prix').'</li>
						<li>'.$this->l('Vendre sur toutes les Places de Marché (PriceMinister, Amazon, RueDuCommerce, eBay, Cdiscount, Fnac, etc…)').'</li>
						<li>'.$this->l('Ré-importer vos commandes Places de Marché directement dans votre Prestashop').'</li>
						<li>'.$this->l('Créer en masse des campagnes Adwords pour chacune de vos fiches produit.').'</li>
					</ol>
				</p>
				<p>'.$this->l('Le tout, via une interface unique, pratique et agréable d’utilisation').' :</p>
				<p style="text-align:center">
		';
				
		//add 6 screens
		for($i=1; $i<=6; $i++)
			$this->_html .= '<a href="'.$uri_img.$i.'.jpg" target="_blank"><img style="margin:10px" src="'.$uri_img.$i.'.jpg" width="250" /></a>';
				
		$this->_html .= '</p></fieldset><br/>';
	
		//second fieldset
		$this->_html .= '
		<form method="post" action="'.htmlentities($_SERVER['REQUEST_URI']).'">
			<fieldset>
				<legend>'.$this->l('Demandez ici votre clé d’activation').'</legend>
				<p style="margin-bottom:20px" >'.$this->l('Ce module vous est offert par Shopping Flux et est utilisable via une souscription mensuelle au service. Envoyez-nous simplement ce formualaire :').'</p>
				<p><label>'.$this->l('Nom du site').' : </label><input type="text" name="site" value="'.Configuration::get('PS_SHOP_NAME').'"></p>
				<p><label>'.$this->l('Nom').' : </label><input type="text" name="nom" value="'.$owner->lastname.'"></p>
				<p><label>'.$this->l('Prenom').' : </label><input type="text" name="prenom" value="'.$owner->firstname.'"></p>
				<p><label>'.$this->l('E-mail').' : </label><input type="text" name="email" value="'.Configuration::get('PS_SHOP_EMAIL').'"></p>
				<p><label>'.$this->l('Téléphone').' : </label><input type="text" name="telephone" value="'.Configuration::get('PS_SHOP_PHONE').'"></p>
				<input type="hidden" name="flux" value="'.$uri.'"/>
				<p style="text-align:center" ><input type="submit" value="'.$this->l('Envoyer la demande').'" name="envoi_mail" class="button"/></p>
			</fieldset>
		</form>
		';
		
		return $this->_html;
	}
	
	private function sendMail(){
		$this->_html .= $this->displayConfirmation($this->l('Votre enregistrement Shopping Flux est effectif, vous serez contacté sous peu.')).'
		<img src="http://www.prestashop.com/partner/shoppingflux/image.php?site='.htmlentities($_POST['site']).'&nom='.htmlentities($_POST['nom']).'&prenom='.htmlentities($_POST['prenom']).'&email='.htmlentities($_POST['email']).'&telephone='.htmlentities($_POST['telephone']).'&flux='.htmlentities($_POST['flux']).'" border="0" />';
	}

	private function clean($string)
	{
		$string = strip_tags($string); 
		$string = str_replace("\r\n","",$string);
		
		return $string;
	}

	public function generateFlux()
	{
		if (Tools::getValue('token') == '' || Tools::getValue('token') != Configuration::get('SHOPPING_FLUX_TOKEN'))
			die('Invalid Token');
		
		$titles = array(
			0 => 'id-produit',
			1 => 'nom-produit',
			2 => 'url-produit',
			3 => 'url-image',
			4 => 'description',
			5 => 'description-courte',
			6 => 'prix',
			7 => 'prix-barre',
			8 => 'frais-de-port',
			9 => 'delai-de-livraison',
			10 => 'marque',
			11 => 'rayon',
			12 => 'stock',
			13 => 'qte_stock',
			14 => 'eab',
			15 => 'poids',
			16 => 'ecotaxe',
			17 => 'tva',
			18 => 'ref-constructeur',
			19 => 'ref-fournisseur'
		);
		
		//For Shipping
		$configuration = Configuration::getMultiple(array('PS_TAX_ADDRESS_TYPE','PS_CARRIER_DEFAULT','PS_COUNTRY_DEFAULT', 'PS_LANG_DEFAULT', 'PS_SHIPPING_FREE_PRICE', 'PS_SHIPPING_HANDLING', 'PS_SHIPPING_METHOD', 'PS_SHIPPING_FREE_WEIGHT'));
		
		$products = Product::getSimpleProducts($configuration['PS_LANG_DEFAULT']);
		
		$defaultCountry = new Country($configuration['PS_COUNTRY_DEFAULT'], Configuration::get('PS_LANG_DEFAULT'));
		$id_zone = (int)$defaultCountry->id_zone;
			
		$carrier = new Carrier((int)$configuration['PS_CARRIER_DEFAULT']);
		$carrierTax = Tax::getCarrierTaxRate((int)$carrier->id, (int)$this->{$configuration['PS_TAX_ADDRESS_TYPE']});
		
		//Header Feed
		echo "<?xml version='1.0' encoding='utf-8'?>\r\n";
		echo "<produits>\r\n";
		
		foreach ($products as $key => $produit)
		{
			$product = new Product((int)($produit['id_product']), true, $configuration['PS_LANG_DEFAULT']);
			
			//For links
			$link = new Link();
			
			//For images
			$cover = $product->getCover($product->id);
			$ids = $product->id.'-'.$cover['id_image'];
			
			//For shipping
			
			if ($product->getPrice(true, NULL, 2, NULL, false, true, 1) >= (float)($configuration['PS_SHIPPING_FREE_PRICE']) AND (float)($configuration['PS_SHIPPING_FREE_PRICE']) > 0)
				$shipping = 0;
			elseif (isset($configuration['PS_SHIPPING_FREE_WEIGHT']) AND $product->weight >= (float)($configuration['PS_SHIPPING_FREE_WEIGHT']) AND (float)($configuration['PS_SHIPPING_FREE_WEIGHT']) > 0)
				$shipping = 0;
			else
			{
				if (isset($configuration['PS_SHIPPING_HANDLING']) AND $carrier->shipping_handling)
				$shipping = (float)($configuration['PS_SHIPPING_HANDLING']);
				
				if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT)
					$shipping += $carrier->getDeliveryPriceByWeight($product->weight, $id_zone);
				else
					$shipping += $carrier->getDeliveryPriceByPrice($product->getPrice(true, NULL, 2, NULL, false, true, 1), $id_zone);
	  
				$shipping *= 1 + ($carrierTax / 100);
				$shipping = (float)(Tools::ps_round((float)($shipping), 2));
				
			}
			
			$data = array();
			$data[0] = $product->id;
			$data[1] = $product->name;
			$data[2] = $link->getProductLink($product);
			$data[3] = $link->getImageLink($product->link_rewrite, $ids, 'large');
			$data[4] = $product->description;
			$data[5] = $product->description_short;
			$data[6] = $product->getPrice(true, NULL, 2, NULL, false, true, 1);
			$data[7] = $product->getPrice(true, NULL, 2, NULL, false, false, 1);
			$data[8] = $shipping;
			$data[9] = $carrier->delay[2];
			$data[10] = $product->manufacturer_name;
			$data[11] = $product->category;
			$data[12] = ($product->quantity > 0) ? 'oui' : 'non';
			$data[13] = $product->quantity;
			$data[14] = $product->ean13;
			$data[15] = $product->weight;
			$data[16] = $product->ecotax;
			$data[17] = $product->tax_rate;
			$data[18] = $product->reference;
			$data[19] = $product->supplier_reference;
			
			//Product Node
			echo "\t<produit>\r\n";
			
			foreach ($titles as $key => $balise)
				//strip_tags node content
				echo "\t\t<$balise><![CDATA[".$this->clean($data[$key])."]]></$balise>\r\n";
				
			echo "\t</produit>\r\n";
		}
		
		//End Feed
		echo "</produits>";
	}
}

