<?php

class sendToAFriend extends Module
{
 	function __construct()
 	{
 	 	$this->name = 'sendtoafriend';
 	 	$this->version = '1.1';
 	 	$this->tab = 'Products';
		
		parent::__construct();
		
		$this->displayName = $this->l('Send to a Friend module');
		$this->description = $this->l('Allows customers to send a product link to a friend');
 	}

	function install()
	{
	 	if (!parent::install())
	 		return false;
	 	return $this->registerHook('extraLeft');
	}
	
	function hookExtraLeft($params)
	{
		global $smarty;
		$smarty->assign('this_path', $this->_path);
		return $this->display(__FILE__, 'product_page.tpl');
	}

	public function displayFrontForm()
	{
		global $smarty;
		$error = false;
		$confirm = false;
		
		if (isset($_POST['submitAddtoafriend']))
		{
			global $cookie, $link;
			/* Product informations */
			$product = new Product(intval(Tools::getValue('id_product')), false, intval($cookie->id_lang));
			$productLink = $link->getProductLink($product);
			
			/* Fields verifications */
			if (empty($_POST['email']) OR empty($_POST['name']))
				$error = $this->l('You must fill all fields.');
			elseif (!Validate::isEmail($_POST['email']))
				$error = $this->l('Your friend\'s email is invalid.');
			elseif (!Validate::isName($_POST['name']))
				$error = $this->l('Your friend\'s name is invalid.');
			elseif (!isset($_GET['id_product']) OR !is_numeric($_GET['id_product']))
				$error = $this->l('An error occurred during the process.');
			else
			{
				/* Email generation */
				$subject = ($cookie->customer_firstname ? $cookie->customer_firstname.' '.$cookie->customer_lastname : $this->l('A friend')).' '.$this->l('send you a link to').' '.$product->name;
				$templateVars = array(
					'{product}' => $product->name,
					'{product_link}' => $productLink,
					'{customer}' => ($cookie->customer_firstname ? $cookie->customer_firstname.' '.$cookie->customer_lastname : $this->l('A friend')),
					'{name}' => Tools::safeOutput($_POST['name'])
				);
				
				/* Email sending */
				if (!Mail::Send(intval($cookie->id_lang), 'send_to_a_friend', $subject, $templateVars, $_POST['email'], NULL, ($cookie->email ? $cookie->email : NULL), ($cookie->customer_firstname ? $cookie->customer_firstname.' '.$cookie->customer_lastname : NULL), NULL, NULL, dirname(__FILE__).'/mails/'))
					$error = $this->l('An error occurred during the process.');
				else
					$confirm = $this->l('An email has been sent successfully to').' '.Tools::safeOutput($_POST['email']).'.';
			}
		}
		else
		{
			global $cookie, $link;
			/* Product informations */
			$product = new Product(intval(Tools::getValue('id_product')), false, intval($cookie->id_lang));
			$productLink = $link->getProductLink($product);
		}
		
		/* Image */
		$images = $product->getImages(intval($cookie->id_lang));
		foreach ($images AS $k => $image)
			if ($image['cover'])
			{
				$cover['id_image'] = intval($product->id).'-'.intval($image['id_image']);
				$cover['legend'] = $image['legend'];
			}
		
		if (!isset($cover))
			$cover = array('id_image' => Language::getIsoById(intval($cookie->id_lang)).'-default', 'legend' => 'No picture');
		
		$smarty->assign(array(
			'cover' => $cover,
			'errors' => $error,
			'confirm' => $confirm,
			'product' => $product,
			'productLink' => $productLink
		));

		return $this->display(__FILE__, 'sendtoafriend.tpl');
	}
}
