<?php

class BlockWishList extends Module
{
	const INSTALL_SQL_FILE = 'install.sql';

    private $_html = '';
    private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'blockwishlist';
		$this->tab = 'Blocks';
		$this->version = 0.2;
		parent::__construct();
		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Wishlist block');
		$this->description = $this->l('Adds a block containing the customer\'s wishlists');
	}
	
	public function install()
	{
		if (!file_exists(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			return (false);
		else if (!$sql = file_get_contents(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			return (false);
		$sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
		$sql = preg_split("/;\s*[\r\n]+/",$sql);
		foreach ($sql AS $k=>$query)
			Db::getInstance()->Execute(trim($query));
		if (!parent::install() OR
						!$this->registerHook('rightColumn') OR
						!$this->registerHook('productActions') OR
						!$this->registerHook('cart') OR
						!$this->registerHook('customerAccount') OR
						!$this->registerHook('header') OR
						!Configuration::updateValue('PS_BLOCK_WISHLIST_ACTIVATED', 1)
					)
			return false;
		/* This hook is optional */
		$this->registerHook('myAccountBlock');
		return true;
	}
	
	public function uninstall()
	{
		return (Configuration::deleteByName('PS_BLOCK_WISHLIST_ACTIVATED') AND
						Db::getInstance()->Execute('DROP TABLE '._DB_PREFIX_.'wishlist') AND
						Db::getInstance()->Execute('DROP TABLE '._DB_PREFIX_.'wishlist_email') AND
						Db::getInstance()->Execute('DROP TABLE '._DB_PREFIX_.'wishlist_product') AND
						Db::getInstance()->Execute('DROP TABLE '._DB_PREFIX_.'wishlist_product_cart') AND 
						 parent::uninstall());
						
	}

	public function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitSettings'))
		{
			$activated = Tools::getValue('activated');
			if ($activated != 0 AND $activated != 1)
				$this->_html .= '<div class="alert error">'.$this->l('Activate module : Invalid choice.').'</div>';
			else
				Configuration::updateValue('PS_BLOCK_WISHLIST_ACTIVATED', intval($activated));
			$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}
		$this->_displayForm();
		return ($this->_html);
	}

	private function _displayForm()
	{
		$this->_displayFormSettings();
		$this->_displayFormView();
	}

	private function _displayFormSettings()
	{
		$this->_html .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
				<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>

				<label>'.$this->l('Activate module').'</label>
				<div class="margin-form">
					<input type="radio" name="activated" id="activated_on" value="1" '.(Tools::getValue('activated', Configuration::get('PS_BLOCK_WISHLIST_ACTIVATED')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="activated_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="activated" id="activated_off" value="0" '.(!Tools::getValue('activated', Configuration::get('PS_BLOCK_WISHLIST_ACTIVATED')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="activated_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p class="clear">'.$this->l('Activate module (Add blockwishlist and "Add to my wishlist" button)').'</p>
				</div>

				<center><input type="submit" name="submitSettings" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		</form>';
	}
	
	private function _displayFormView()
	{
		global $cookie;

		$customers = Customer::getCustomers();
		if (!sizeof($customers))
			return;
		$id_customer = intval(Tools::getValue('id_customer'));
		if (!$id_customer)
			$id_customer = $customers[0]['id_customer'];
		$this->_html .= '<br />
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="listing">
			<fieldset>
				<legend><img src="'.$this->_path.'img/icon/package_go.png" alt="" title="" />'.$this->l('Listing').'</legend>

				<label>'.$this->l('Customers').'</label>
				<div class="margin-form">
					<select name="id_customer" onchange="$(\'#listing\').submit();">';
		foreach ($customers as $customer)
		{
			$this->_html .= '<option value="'.intval($customer['id_customer']).'"';
			if ($customer['id_customer'] == $id_customer)
				$this->_html .= ' selected="selected"';
			$this->_html .= '>'.htmlentities($customer['firstname'], ENT_COMPAT, 'UTF-8').' '.htmlentities($customer['lastname'], ENT_COMPAT, 'UTF-8').'</option>';
		}
		$this->_html .= '
					</select>
				</div>';
		require_once(dirname(__FILE__).'/WishList.php');
		$wishlists = WishList::getByIdCustomer($id_customer);
		if (!sizeof($wishlists))
			return ($this->_html .= '</fieldset></form>');
		$id_wishlist = intval(Tools::getValue('id_wishlist'));
		if (!$id_wishlist)
			$id_wishlist = $wishlists[0]['id_wishlist'];
		$this->_html .= '
				<label>'.$this->l('Wishlists').'</label>
				<div class="margin-form">
					<select name="id_wishlist" onchange="$(\'#listing\').submit();">';
		foreach ($wishlists as $wishlist)
		{
			$this->_html .= '<option value="'.intval($wishlist['id_wishlist']).'"';
			if ($wishlist['id_wishlist'] == $id_wishlist)
			{
				$this->_html .= ' selected="selected"';
				$counter = $wishlist['counter'];
			}
			$this->_html .= '>'.htmlentities($wishlist['name'], ENT_COMPAT, 'UTF-8').'</option>';
		}
		$this->_html .= '
					</select>
				</div>';
		$products = WishList::getProductByIdCustomer(intval($id_wishlist), intval($id_customer), intval($cookie->id_lang));
		for ($i = 0; $i < sizeof($products); ++$i)
		{
			$obj = new Product(intval($products[$i]['id_product']), false, intval($cookie->id_lang));
			if (!Validate::isLoadedObject($obj))
				continue;
			else
			{
				$images = $obj->getImages(intval($cookie->id_lang));
				foreach ($images AS $k => $image)
				{
					if ($image['cover'])
					{
						$products[$i]['cover'] = $obj->id.'-'.$image['id_image'];
						break;
					}
				}
				if (!isset($products[$i]['cover']))
					$products[$i]['cover'] = Language::getIsoById(intval($cookie->id_lang)).'-default';
			}
		}
		$this->_html .= '
		<table class="table">
			<thead>
				<tr>
					<th class="first_item" style="width:600px;">'.$this->l('Product').'</th>
					<th class="item" style="text-align:center;width:150px;">'.$this->l('Quantity').'</th>
					<th class="item" style="text-align:center;width:150px;">'.$this->l('Priority').'</th>
				</tr>
			</thead>
			<tbody>';
			$priority = array($this->l('High'), $this->l('Medium'), $this->l('Low'));
			foreach ($products as $product)
			{
				$this->_html .= '
				<tr>
					<td class="first_item">
						<img src="'._THEME_PROD_DIR_.$product['cover'].'-small.jpg" alt="'.htmlentities($product['name'], ENT_COMPAT, 'UTF-8').'" style="float:left;" />
						'.$product['name'];
				if (isset($product['attributes_small']))
					$this->_html .= '<br /><i>'.htmlentities($product['attributes_small'], ENT_COMPAT, 'UTF-8').'</i>';
				$this->_html .= '
					</td>
					<td class="item" style="text-align:center;">'.intval($product['quantity']).'</td>
					<td class="item" style="text-align:center;">'.$priority[intval($product['priority']) % 3].'</td>
				</tr>';
			}
		$this->_html .= '
			</tbody>
		</table>
			</fieldset>
		</form>';
	}
		
	public function hookRightColumn($params)
	{
		global $smarty;
		global $errors;

		if (Configuration::get('PS_BLOCK_WISHLIST_ACTIVATED') == 0)
			return (null);
		require_once(dirname(__FILE__).'/WishList.php');
		if ($params['cookie']->isLogged())
		{
			$wishlists = Wishlist::getByIdCustomer($params['cookie']->id_customer);
			if (empty($params['cookie']->id_wishlist) === true ||
				WishList::exists($params['cookie']->id_wishlist, $params['cookie']->id_customer) === false)
			{
				if (!sizeof($wishlists))
					$id_wishlist = false;
				else
				{
					$id_wishlist = intval($wishlists[0]['id_wishlist']);
					$params['cookie']->id_wishlist = intval($id_wishlist);
				}
			}
			else
				$id_wishlist = $params['cookie']->id_wishlist;
			$smarty->assign(array(
				'id_wishlist' => $id_wishlist,
				'isLogged' => true,
				'products' => ($id_wishlist == false ? false : WishList::getProductByIdCustomer($id_wishlist, $params['cookie']->id_customer, $params['cookie']->id_lang, null, true)),
				'wishlists' => $wishlists,
				'ptoken' => Tools::getToken(false)));
		}
		else
			$smarty->assign(array('products' => false, 'wishlists' => false));
		return ($this->display(__FILE__, 'blockwishlist.tpl'));
	}
	
	public function hookProductActions($params)
	{
		global $smarty;
		
		$smarty->assign('id_product', intval(Tools::getValue('id_product')));
		return ($this->display(__FILE__, 'blockwishlist-extra.tpl'));
	}
	
	public function hookCustomerAccount($params)
	{
		global $smarty;
		return $this->display(__FILE__, 'my-account.tpl');
	}
	
	public function hookMyAccountBlock($params)
	{
		return $this->hookCustomerAccount($params);
	}
}


?>
