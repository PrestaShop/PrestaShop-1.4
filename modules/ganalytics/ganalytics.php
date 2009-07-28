<?php

class GAnalytics extends Module
{	
	function __construct()
	{
	 	$this->name = 'ganalytics';
	 	$this->tab = 'Stats';
	 	$this->version = '1.2';
        $this->displayName = 'Google Analytics';
		
	 	parent::__construct();
		
		if (!Configuration::get('GANALYTICS_ID'))
			$this->warning = $this->l('You have not yet set your Google Analytics ID');
        $this->description = $this->l('Integrate the Google Analytics script into your shop');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
	}
	
    function install()
    {
        if (!parent::install() OR !$this->registerHook('footer') OR !$this->registerHook('orderConfirmation'))
			return false;
		return true;
    }
	
	function uninstall()
	{
		if (!Configuration::deleteByName('GANALYTICS_ID') OR !parent::uninstall())
			return false;
		return true;
	}
	
	public function getContent()
	{
		$output = '<h2>Google Analytics</h2>';
		if (Tools::isSubmit('submitGAnalytics') AND ($gai = Tools::getValue('ganalytics_id')))
		{
			Configuration::updateValue('GANALYTICS_ID', $gai);
			$output .= '
			<div class="conf confirm">
				<img src="../img/admin/ok.gif" alt="" title="" />
				'.$this->l('Settings updated').'
			</div>';
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		$output = '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset class="width2">
				<legend><img src="../img/admin/cog.gif" alt="" class="middle" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Your username').'</label>
				<div class="margin-form">
					<input type="text" name="ganalytics_id" value="'.Tools::getValue('ganalytics_id', Configuration::get('GANALYTICS_ID')).'" />
					<p class="clear">'.$this->l('Example:').' UA-1234567-1</p>
				</div>
				<center><input type="submit" name="submitGAnalytics" value="'.$this->l('Update ID').'" class="button" /></center>
			</fieldset>
		</form>';
		
		$output .= '
		<fieldset class="space">
			<legend><img src="../img/admin/unknown.gif" alt="" class="middle" />'.$this->l('Help').'</legend>
			 <h3>'.$this->l('The first step of tracking e-commerce transactions is to enable e-commerce reporting for your website\'s profile.').'</h3>
			 '.$this->l('To enable e-Commerce reporting, please follow these steps:').'
			 <ol>
			 	<li>'.$this->l('Log in to your account').'</li>
			 	<li>'.$this->l('Click Edit next to the profile you\'d like to enable').'</li>
			 	<li>'.$this->l('On the Profile Settings page, click edit next to Main Website Profile Information').'</li>
			 	<li>'.$this->l('Change the e-Commerce Website radio button from No to Yes').'</li>
			</ol>
			<h3>'.$this->l('To set up your goals, enter Goal Information:').'</h3>
			<ol>
				<li>'.$this->l('Return to Your Account main page').'</li>
				<li>'.$this->l('Find the profile for which you will be creating goals, then click Edit').'</li>
				<li>'.$this->l('Select one of the 4 goal slots available for that profile, then click Edit').'</li>
				<li>'.$this->l('Enter the Goal URL. Reaching this page marks a successful conversion').'</li>
				<li>'.$this->l('Enter the Goal name as it should appear in your Google Analytics account').'</li>
				<li>'.$this->l('Turn the Goal on').'</li>
			</ol>
			<h3>'.$this->l('Then, define a funnel by following these steps:').'</h3>
			<ol>
				<li>'.$this->l('Enter the URL of the first page of your conversion funnel. This page should be a page that is common to all users working their way towards your Goal.').'</li>
				<li>'.$this->l('Enter a Name for this step.').'</li>
				<li>'.$this->l('If this step is a Required step in the conversion process, mark the checkbox to the right of the step.').'</li>
				<li>'.$this->l('Continue entering goal steps until your funnel has been completely defined. You may enter up to 10 steps, or as few as a single step.').'</li>
			</ol>
			'.$this->l('Finally, configure Additional settings by following the steps below:').'
			<ol>
				<li>'.$this->l('If the URLs entered above are Case sensitive, mark the checkbox.').'</li>
				<li>'.$this->l('Select the appropriate goal Match Type. (').'<a href="http://www.google.com/support/analytics/bin/answer.py?answer=72285">'.$this->l('Learn more').'</a> '.$this->l('about Match Types and how to choose the appropriate goal Match Type for your goal.)').'</li>
				<li>'.$this->l('Enter a Goal value. This is the value used in Google Analytics\' ROI calculations.').'</li>
				<li>'.$this->l('Click Save Changes to create this Goal and funnel, or Cancel to exit without saving.').'</li>
			</ol>
			<h3>'.$this->l('Demonstration: The order process').'</h3>
			<ol>
				<li>'.$this->l('After having enabled your e-commerce reports and selected the respective profile enter \'order-confirmation.php\' as the targeted page URL').'</li>
				<li>'.$this->l('Name this goal (for example \'Order process\')').'</li>
				<li>'.$this->l('Activate the goal').'</li>
				<li>'.$this->l('Add \'product.php\' as the first page of your conversion funnel').'</li>
				<li>'.$this->l('Give it a name (for example, \'Product page\')').'</li>
				<li>'.$this->l('Do not mark \'required\' checkbox because the customer could be visiting directly from an \'adding to cart\' button such as in the homefeatured block on the homepage').'</li>
				<li>'.$this->l('Continue by entering the following URLs as goal steps:').'
					<ul>
						<li>order/step0.html '.$this->l('(required)').'</li>
						<li>authentication.php '.$this->l('(required)').'</li>
						<li>order/step1.html '.$this->l('(required)').'</li>
						<li>order/step2.html '.$this->l('(required)').'</li>
						<li>order/step3.html '.$this->l('(required)').'</li>
					</ul>
				</li>
				<li>'.$this->l('Check the \'Case sensitive\' option').'</li>
				<li>'.$this->l('Save this new goal').'</li>
			</ol>
		</fieldset>';
		
		return $output;
	}
	
	function hookFooter($params)
	{
		global $step, $protocol_content;

		$output = '
		<script type="text/javascript">
			document.write(unescape("%3Cscript src=\''.$protocol_content.'www.google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
		</script>
		<script type="text/javascript">
		try
		{
			var pageTracker = _gat._getTracker("'.Configuration::get('GANALYTICS_ID').'");
			pageTracker._trackPageview();
			'.(strpos($_SERVER['REQUEST_URI'], __PS_BASE_URI__.'order.php') === 0 ? 'pageTracker._trackPageview("/order/step'.intval($step).'.html");' : '').'
		}
		catch(err)
			{}
		</script>';
		return $output;
	}
	
	function hookOrderConfirmation($params)
	{
		global $protocol_content;

		$order = $params['objOrder'];
		if (Validate::isLoadedObject($order))
		{
			$deliveryAddress = new Address(intval($order->id_address_delivery));
			
			/* Order general informations */
			$output = '
			<script src="'.$protocol_content.'www.google-analytics.com/ga.js" type="text/javascript"></script>
	
			<script type="text/javascript">
			  var pageTracker = _gat._getTracker("'.Configuration::get('GANALYTICS_ID').'");
			  pageTracker._initData();
			
			  pageTracker._addTrans(
				"'.intval($order->id).'",               	// Order ID
				"PrestaShop",      							// Affiliation
				"'.floatval($order->total_paid).'",       	// Total
				"0",               							// Tax
				"'.floatval($order->total_shipping).'",     // Shipping
				"'.$deliveryAddress->city.'",           	// City
				"",         								// State
				"'.$deliveryAddress->country.'"             // Country
			  );';

			/* Product informations */
			$products = $order->getProducts();
			foreach ($products AS $product)
			{
				$output .= '
				pageTracker._addItem(
					"'.intval($order->id).'",						// Order ID
					"'.$product['product_reference'].'",			// SKU
					"'.$product['product_name'].'",					// Product Name 
					"",												// Category
					"'.floatval($product['product_price_wt']).'",		// Price
					"'.intval($product['product_quantity']).'"		// Quantity
				);';
			}
			
			$output .= '
			  pageTracker._trackTrans();
			</script>';
			
			return $output;
		}
	}
}
