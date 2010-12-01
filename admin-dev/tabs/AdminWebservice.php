<?php
/**
  * Webservice tab for admin panel, AdminWebservice.php
  * @Webservice admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  *
  */

include_once(dirname(__FILE__).'/../../classes/AdminTab.php');

class AdminWebservice extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'webservice_account';
	 	$this->className = 'Webservice';
	 	$this->lang = false;
	 	$this->edit = true;
	 	$this->delete = true;
	 	
 		$this->id_lang_default = Configuration::get('PS_LANG_DEFAULT');
		
		$this->fieldsDisplay = array(
		'key' => array('title' => $this->l('Key'), 'align' => 'center', 'width' => 32),
		'active' => array('title' => $this->l('Enabled'), 'align' => 'center', 'active' => 'status', 'type' => 'bool', 'orderby' => false)
		);
		
		$this->optionTitle = $this->l('Configuration');
		$this->_fieldsOptions = array(
		'PS_WEBSERVICE' => array('title' => $this->l('Enable PrestaShop Webservice:'), 'desc' => ''.$this->l('Before activating the webservice, you must be sure to: ').'<ol><li>'.$this->l('be certain URL rewrite is available on this server').'</li><li>'.$this->l('be certain that the 4 methods GET, POST, PUT, DELETE and HEAD are supported by this server').'</li></ol>', 'cast' => 'intval', 'type' => 'bool'),
		);
	
		parent::__construct();
	}
	
	protected function afterAdd($object) {
		Webservice::setPermissionForAccount($object->id, Tools::getValue('resources', array()));
	}
	
	protected function afterUpdate($object) {
		Webservice::setPermissionForAccount($object->id, Tools::getValue('resources', array()));
	}
	
	public function display()
	{
		global $cookie, $currentIndex;
		parent::display();
		if (Tools::getValue('updatewebservice_account') === false)
		{
			$keys = Webservice::getAuthenticationKeys();
			echo '
			<form style="margin-top:15px;overflow:auto;" class="width6" method="post" name="Tests" id="Tests" action="'.$currentIndex.'&token='.$this->token.'#Tests">
				<fieldset><legend><img src="../img/admin/enabled.gif">Webservice tests</legend>
					<label>'.$this->l('Authentication key:').'</label>
					<div class="margin-form">
						<select name="auth_key">
						<option value="">'.$this->l('Select a key').'</option>';
			foreach ($keys as $key)
				echo '<option value="'.$key.'" '.((Tools::getValue('auth_key') == $key) ? 'selected="selected"' : '').'>'.$key.'</option>';
					echo '
						<option value="">'.$this->l('Empty key').'</option>
						<option value="AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA">'.$this->l('Wrong Key').'</option>
						</select>
					</div>';
	$resources = $this->getResources();
	echo '
					<label>'.$this->l('Select resource(s):').'</label>
					<div class="margin-form">
						<select name="resource">
						<option value="">'.$this->l('All resources').'</option>';
			foreach (array_keys($resources) as $resource)
				echo '<option value="'.$resource.'" '.((Tools::getValue('resource') == $resource) ? 'selected="selected"' : '').'>'.$resource.'</option>';
					echo '
						</select>
					</div>';
	$cases = $this->getCases();
	echo ' <label>'.$this->l('Select case(s):').'</label>
					<div class="margin-form">
						<select name="case">
						<option value="">'.$this->l('Select a case').'</option>';
			foreach ($cases as $caseName => $case)
				echo '<option value="'.$caseName.'" '.((Tools::getValue('case') == $caseName) ? 'selected="selected"' : '').'>'.$case.'</option>';
					echo '
						</select>
					</div>';
	echo ' <div class="margin-form">
						<p style="color:red">'.$this->l('Be careful with this test button, just read the warning at the beginning of the page!').'</p>
						<input type="submit" class="button" name="submitWebserviceTests" value="'.$this->l('Start the test').'">
					</div>';
					if (Tools::isSubmit('submitWebserviceTests'))
						$this->executeTests($cases, $resources);
	echo '
				</fieldset>
			</form>
			';
		}
	}
	
	public function displayList()
	{
		global $cookie, $currentIndex;
		$warnings = array();
		
		if (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') === false)
			$warnings[] = $this->l('To avoid operating problems, please use an Apache server.');
		{
			$apache_modules = apache_get_modules();
			if (!in_array('mod_auth_basic', $apache_modules))
				$warnings[] = $this->l('Please activate the Apache module \'mod_auth_basic\' to allow authentication of PrestaShop webservice.');
			if (!in_array('mod_rewrite', $apache_modules))
				$warnings[] = $this->l('Please activate the Apache module \'mod_rewrite\' to allow using of PrestaShop webservice.');
		}
		if (!extension_loaded('curl'))
			$warnings[] = $this->l('Please activate the PHP extension \'curl\' to allow testing of PrestaShop webservice.');
		if (!extension_loaded('SimpleXML'))
			$warnings[] = $this->l('Please activate the PHP extension \'SimpleXML\' to allow testing of PrestaShop webservice.');
		if (!configuration::get('PS_SSL_ENABLED'))
			$warnings[] = $this->l('if you have the possibility, it is preferable to use the SSL (https) for webservice calls, it avoids the security issues of type "man in the middle".');
		
		$warnings[] = $this->l('Be careful !! When you run the tests, some of your data will be deleted from your database, on the other hand, new dummy data will be added in this one. Other data will be replaced with dummy content.');
		
		$this->displayWarning($warnings);
		
		parent::displayList();
	}
	
	public function displayForm($isMainTab = true)
	{
		global $currentIndex;
		parent::displayForm();
		
		$obj = $this->loadObject(true);
		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" enctype="multipart/form-data">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/access.png" />'.$this->l('Webservice Accounts').'</legend>
				<label>'.$this->l('Key:').'</label>
				<div class="margin-form">
					<input type="text" size="38" name="key" id="code" value="'.htmlentities(Tools::getValue('key', $obj->key), ENT_COMPAT, 'UTF-8').'" />
					<input type="button" value="'.$this->l('   Generate!   ').'" class="button" onclick="gencode(32)" />
					<sup>*</sup>
					<p class="clear">'.$this->l('Webservice account key').'</p>
				</div>
				<label>'.$this->l('Status:').' </label>
				<div class="margin-form">
					<input type="radio" name="active" id="active_on" value="1" '.((!$obj->id OR Tools::getValue('active', $obj->active)) ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="active" id="active_off" value="0" '.((!Tools::getValue('active', $obj->active) AND $obj->id) ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
				</div>
				<label>'.$this->l('Permissions:').' </label>
				<div class="margin-form">
					<p>'.$this->l('Set the resource permissions for this key:').'</p>
					<table border="0" cellspacing="0" cellpadding="0" class="permissions">
						<thead>
							<tr>
								<th>'.$this->l('Resource').'</th>
								<th width="30"></th>
								<th width="50">'.$this->l('View (GET)').'</th>
								<th width="50">'.$this->l('Modify (PUT)').'</th>
								<th width="50">'.$this->l('Add (POST)').'</th>
								<th width="50">'.$this->l('Delete (DELETE)').'</th>
								<th width="50">'.$this->l('Fast view (HEAD)').'</th>
							</tr>
							
						</thead>
						<tbody>
						<tr class="all" style="vertical-align:cen">
								<th></th>
								<th></th>
								<th><input type="checkbox" class="all_get get " /></th>
								<th><input type="checkbox" class="all_put put " /></th>
								<th><input type="checkbox" class="all_post post " /></th>
								<th><input type="checkbox" class="all_delete delete" /></th>
								<th><input type="checkbox" class="all_head head" /></th>
							</tr>
						';
$ressources = Webservice::getResources();
$permissions = Webservice::getPermissionForAccount($obj->key);
foreach ($ressources as $resourceName => $resource)
echo '
							<tr>
								<th>'.$resourceName.'</th>
								<th><input type="checkbox" class="all"/></th>
								<td><input type="checkbox" class="get" name="resources['.$resourceName.'][GET]" '.(isset($permissions[$resourceName]) && in_array('GET', $permissions[$resourceName]) ? 'checked="checked"' : '').' /></td>
								<td><input type="checkbox" class="put" name="resources['.$resourceName.'][PUT]" '.(isset($permissions[$resourceName]) && in_array('PUT', $permissions[$resourceName]) ? 'checked="checked"' : '').'/></td>
								<td><input type="checkbox" class="post" name="resources['.$resourceName.'][POST]" '.(isset($permissions[$resourceName]) && in_array('POST', $permissions[$resourceName]) ? 'checked="checked"' : '').'/></td>
								<td><input type="checkbox" class="delete" name="resources['.$resourceName.'][DELETE]" '.(isset($permissions[$resourceName]) && in_array('DELETE', $permissions[$resourceName]) ? 'checked="checked"' : '').'/></td>
								<td><input type="checkbox" class="head" name="resources['.$resourceName.'][HEAD]" '.(isset($permissions[$resourceName]) && in_array('HEAD', $permissions[$resourceName]) ? 'checked="checked"' : '').'/></td>
							</tr>';
echo '
						</tbody>
					</table>
					<script>';?>
				
						$(function() {
							$('table.permissions input.all').click(function() {
								if($(this).is(':checked'))
									$(this).parent().parent().find('input.get:not(:checked), input.put:not(:checked), input.post:not(:checked), input.delete:not(:checked), input.head:not(:checked)').click();
								else
									$(this).parent().parent().find('input.get:checked, input.put:checked, input.post:checked, input.delete:checked, input.head:checked').click();
							});
							$('table.permissions .all_get').click(function() {
								if($(this).is(':checked'))
									$(this).parent().parent().parent().find('input.get:not(:checked)').click();
								else
									$(this).parent().parent().parent().find('input.get:checked').click();
							});
							$('table.permissions .all_put').click(function() {
								if($(this).is(':checked'))
									$(this).parent().parent().parent().find('input.put:not(:checked)').click();
								else
									$(this).parent().parent().parent().find('input.put:checked').click();
							});
							$('table.permissions .all_post').click(function() {
								if($(this).is(':checked'))
									$(this).parent().parent().parent().find('input.post:not(:checked)').click();
								else
									$(this).parent().parent().parent().find('input.post:checked').click();
							});
							$('table.permissions .all_delete').click(function() {
								if($(this).is(':checked'))
									$(this).parent().parent().parent().find('input.delete:not(:checked)').click();
								else
									$(this).parent().parent().parent().find('input.delete:checked').click();
							});
							$('table.permissions .all_head').click(function() {
								if($(this).is(':checked'))
									$(this).parent().parent().parent().find('input.head:not(:checked)').click();
								else
									$(this).parent().parent().parent().find('input.head:checked').click();
							});
						});
				<?php echo '
					</script>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}
	
	public static function randname($length = 8) {
		$ret = '';
		for ($i = 0; $i <= $length; $i++)
			$ret .= base_convert(rand(10, 35), 10, 36);
		return $ret;
	}
	
	public static function randdate($time = "" , $time2 = "", $returnTimestamp = false)
	{
			if(!$time) $time = strtotime("10 Janvier 2010");
			if(!$time2) $time2 = strtotime("now");
			$timestamp = date(" D, d M Y", rand((int)$time,(int)$time2)); //Must be called once before becoming random, ???
			$timestamp = date(" D, d M Y", rand($time , $time2))." ";//Now it's random
		 
			$h = rand(1,23);
			if(strlen($h) == 1 ) $h = "0$h";
			$t = $h.":";
		 
			$d = rand(1,29);
			if(strlen($d) == 1 ) $d = "0$d";
			$t .= $d.":";
		 
			$s = rand(0,59);
			if(strlen($s) == 1 ) $s = "0$s";
			$t .= $s;
		 
			$timestamp .= $t." GMT";
			if ($returnTimestamp)
				return $timestamp;
			
			if ($returnTimestamp)
				return date("Y-m-d H:i:s", $timestamp);
	}

	public static function formatFieldsAsXML($fields)
	{
		p($fields);
		libxml_use_internal_errors(true);
		
		$xml = simplexml_load_string("
		<?xml version='1.0' encoding='utf-8'
		<!DOCTYPE prestashop PUBLIC \"-//PRESTASHOP//DTD REST_WEBSERVICE 1.4.0.2//EN\"
		\"http://localhost/ps/v1/trunk/tools/webservice/psws.dtd\">
		<p:prestashop xmlns:p=\"http://prestashop.com/docs/1.4/webservice\" xmlns:xlink=\"http://www.w3.org/1999/xlink\">
		<p:state><associations/></p:state>
		</p:prestashop>
		");
		if ($xml)
		{
			foreach($fields['attributes'] as $key => $value)
				if (is_array($value))
				{
					$xml->attributes->addChild($key);
					foreach ($value as $idLang => $value)
						$xml->attributes->{$key}->addChild('language', $value)->addAttribute('id', $idLang);
				}
				else
				{
					$value = htmlentities($value);
					$xml->attributes->addChild($key,$value);
				}
			if (isset($fields['associations']))
				foreach($fields['associations'] as $type => $associations)
				{
					$xml->associations->addChild($type);
					foreach ($associations as $association)
						foreach ($association as $key => $value)
							$xml->associations->{$type}->addChild($key , htmlentities($value));
				}
		d(htmlentities($xml->asXML()));
		return $xml->asXML();
		}
		else
			return false;
	}
	
	public static function config_case($caseName, $resourceName, $resource) {
		/*
		GET THE LIST
		GET THE LAST ONE
		GET FROM 5 TO 15
		GET WHERE "A" IN THE NAME
		GET WHERE "A" IN THE TRANSLATED NAME
		ADD ONE
		EDIT THE NAME OF ONE
		DELETE THE LAST
		GET A NOT EXISTING
		EDIT THE TRANSLATED NAME OF THE LAST ONE
		EDIT THE ASSOCIATIONS OF THE LAST ONE
		DUPLICATE THE LAST ONE
		*/
		
		switch ($caseName)
		{
			case 'get_all_resources':
				return array(
					'resource' => $resourceName,
					'method' => 'GET',
					'expected_return' => array(
						'code' => '200',
					)
				);
			case 'get_existing_resource_details':
				return array(
					'resource' => $resourceName,
					'method' => 'GET',
					'id' => Db::getInstance()->getValue($resource['existing_id_rq']),
					'expected_return' => array(
						'code' => '200',
					)
				);
			case 'get_not_existing_resource':
				return array(
					'resource' => $resourceName,
					'method' => 'GET',
					'id' => rand(1000000,10000000),
					'expected_return' => array(
						'code' => '404',
					)
				);
			case 'post_resource_with_already_existing_id':
				return array(
					'resource' => $resourceName,
					'method' => 'POST',
					'id' => Db::getInstance()->getValue($resource['existing_id_rq']),
					'post_data' => $resource['fields'],
					'expected_return' => array(
						'code' => '400',
						'error' => true
					)
				);
			case 'post_incomplete_resource':
				array_pop($resource['fields']['attributes']);
				return array(
					'resource' => $resourceName,
					'method' => 'POST',
					'post_data' => $resource['fields'],
					'expected_return' => array(
						'code' => '400',
						'error' => true
					)
				);
			case 'post_complete_resource_with_not_existing_id':
				return array(
					'resource' => $resourceName,
					'method' => 'POST',
					'id' => rand(1000000,10000000),
					'post_data' => $resource['fields'],
					'expected_return' => array(
						'code' => '400',
						'error' => true
					)
				);
			case 'post_complete_resource':
				return array(
					'resource' => $resourceName,
					'method' => 'POST',
					'post_data' => $resource['fields'],
					'expected_return' => array(
						'code' => '201',
					)
				);
			case 'delete_not_existing_resource':
				return array(
					'resource' => $resourceName,
					'id' => 123456789,
					'method' => 'DELETE',
					'expected_return' => array(
						'code' => '204',
					)
				);
			case 'delete_resource_with_last_id':
				return array(
					'resource' => $resourceName,
					'id' => Db::getInstance()->getValue($resource['max_id_rq']),
					'method' => 'DELETE',
					'expected_return' => array(
						'code' => '200',
					)
				);
			case 'put_incomplete_random_informations_for_existing_resource':
				array_pop($resource['fields']['attributes']);
				return array(
					'resource' => $resourceName,
					'method' => 'PUT',
					'id' => Db::getInstance()->getValue($resource['existing_id_rq']),
					'put_data' => AdminWebservice::formatFieldsAsXML($resource['fields']),
					'expected_return' => array(
						'code' => '400',
						'error' => true
					)
				);
			case 'put_random_informations_for_not_existing_resource':
				return array(
					'resource' => $resourceName,
					'method' => 'PUT',
					'id' => rand(1000000,10000000),
					'put_data' => AdminWebservice::formatFieldsAsXML($resource['fields']),
					'expected_return' => array(
						'code' => '404',
					)
				);
			case 'put_random_informations_for_existing_resource':
				return array(
					'resource' => $resourceName,
					'method' => 'PUT',
					'id' => Db::getInstance()->getValue($resource['existing_id_rq']),
					'put_data' => AdminWebservice::formatFieldsAsXML($resource['fields']),
					'expected_return' => array(
						'code' => '200',
					)
				);
		}
	}
	
	public function getCases() {
		$cases = array(
			'post_complete_resource' => $this->l('POST : complete resource'),
			'get_all_resources' => $this->l('GET : list of resources'),
			'get_existing_resource_details' => $this->l('GET : détails of existing resource'),
			'put_random_informations_for_existing_resource' => $this->l('PUT : complete random informations for existing resource'),
			'delete_resource_with_last_id' =>  $this->l('DELETE : existing resource'),
			'all_cases' => $this->l('ALL : execute all cases'),
			'post_resource_with_already_existing_id' => $this->l('POST : with already existing id'),
			'post_incomplete_resource' => $this->l('POST : imcomplete resource'),
			'post_complete_resource_with_not_existing_id' => $this->l('POST : complete resource with not existing id'),
			'get_not_existing_resource' => $this->l('GET : détails of not existing resource'),
			'put_incomplete_random_informations_for_existing_resource' => $this->l('PUT : imcomplete random informations for existing resource'),
			'put_random_informations_for_not_existing_resource' => $this->l('PUT : imcomplete random informations for not existing resource'),
			'delete_not_existing_resource' =>  $this->l('DELETE : not existing resource'),
		);
		return $cases;
	}
	public function getResources() {
		$resources = array(
			'zones' => array(
				'fields' => array(
					'attributes' => array(
						'active' => rand(0,1),
						'name' => AdminWebservice::randname(),
					),
				),
				'max_id_rq' => 'SELECT max(id_zone) FROM `'._DB_PREFIX_.'zone`',
				'existing_id_rq' => 'SELECT min(id_zone) as id FROM `'._DB_PREFIX_.'zone`'
			),
			'tags' => array(
				'fields' => array(
					'attributes' => array(
						'name' => AdminWebservice::randname(),
						'id_lang' => $this->id_lang_default,
					),
				),
				'max_id_rq' => 'SELECT max(id_tag) FROM `'._DB_PREFIX_.'tag`',
				'existing_id_rq' => 'SELECT min(id_tag) as id FROM `'._DB_PREFIX_.'tag`'
			),
			'countries' => array(
				'fields' => array(
					'attributes' => array(
						'name' => array($this->id_lang_default => AdminWebservice::randname()),
						'iso_code' => AdminWebservice::randname(2),
						'id_zone' => rand(1,5),
						'contains_states' => rand(0,1),
						'need_identification_number' => rand(0,1),
					),
				),
				'max_id_rq' => 'SELECT max(id_country) FROM `'._DB_PREFIX_.'country`',
				'existing_id_rq' => 'SELECT min(id_country) as id FROM `'._DB_PREFIX_.'country`'
			),
			'states' => array(
				'fields' => array(
					'attributes' => array(
						'active' => rand(0,1),
						'id_country' => rand(0,5),
						'id_zone' => rand(0,5),
						'iso_code' => strtoupper(AdminWebservice::randname(2)),
						'tax_behavior' => rand(0,1),
						'name' => AdminWebservice::randname(),
					),
				),
				'max_id_rq' => 'SELECT max(id_state) FROM `'._DB_PREFIX_.'state`',
				'existing_id_rq' => 'SELECT min(id_state) as id FROM `'._DB_PREFIX_.'state`'
			),
			'manufacturers' => array(
				'fields' => array(
					'attributes' => array(
						'description' => array($this->id_lang_default => AdminWebservice::randname()),
						'short_description' => array($this->id_lang_default => AdminWebservice::randname()),
						'id_address' => rand(1,5),
						'link_rewrite' => AdminWebservice::randname(),
						'meta_title' => array($this->id_lang_default => AdminWebservice::randname()),
						'meta_keywords' => array($this->id_lang_default => AdminWebservice::randname()),
						'meta_description' => array($this->id_lang_default => AdminWebservice::randname()),
						'name' => AdminWebservice::randname(),
					),
				),
				'max_id_rq' => 'SELECT max(id_manufacturer) FROM `'._DB_PREFIX_.'manufacturer`',
				'existing_id_rq' => 'SELECT min(id_manufacturer) as id FROM `'._DB_PREFIX_.'manufacturer`'
			),
			'suppliers' => array(
				'fields' => array(
					'attributes' => array(
						'description' => array($this->id_lang_default => AdminWebservice::randname()),
						'short_description' => array($this->id_lang_default => AdminWebservice::randname()),
						'id_address' => rand(1,5),
						'link_rewrite' => AdminWebservice::randname(),
						'meta_title' => array($this->id_lang_default => AdminWebservice::randname()),
						'meta_keywords' => array($this->id_lang_default => AdminWebservice::randname()),
						'meta_description' => array($this->id_lang_default => AdminWebservice::randname()),
						'name' => AdminWebservice::randname(),
					),
				),
				'max_id_rq' => 'SELECT max(id_supplier) FROM `'._DB_PREFIX_.'supplier`',
				'existing_id_rq' => 'SELECT min(id_supplier) as id FROM `'._DB_PREFIX_.'supplier`'
			),
			'groups' => array(
				'fields' => array(
					'attributes' => array(
						'name' => array($this->id_lang_default => AdminWebservice::randname()),
						'reduction' => rand(0,100),
						'price_display_method' => rand(0,1),
					),
				),
				'max_id_rq' => 'SELECT max(id_group) FROM `'._DB_PREFIX_.'group`',
				'existing_id_rq' => 'SELECT min(id_group) as id FROM `'._DB_PREFIX_.'group`'
			),
			'customers' => array(
				'fields' => array(
					'attributes' => array(
						'lastname' => AdminWebservice::randname(),
						'email' => AdminWebservice::randname().'@'.AdminWebservice::randname().'.com',
						'passwd' => '123456789',
						'firstname' => AdminWebservice::randname(),
					),
				),
				'max_id_rq' => 'SELECT max(id_customer) FROM `'._DB_PREFIX_.'customer`',
				'existing_id_rq' => 'SELECT min(id_customer) as id FROM `'._DB_PREFIX_.'customer`'
			),
			'addresses' => array(
				'fields' => array(
					'attributes' => array(
						'id_customer' => rand(1,5),
						'id_manufacturer' => rand(1,5),
						'id_supplier' => rand(1,5),
						'id_state' => rand(1,5),
						'country' => strtoupper(AdminWebservice::randname(8)),
						'alias' => strtoupper(AdminWebservice::randname(8)),
						'company' => strtoupper(AdminWebservice::randname(8)),
						'lastname' => strtoupper(AdminWebservice::randname(8)),
						'firstname' => strtoupper(AdminWebservice::randname(8)),
						'address1' => strtoupper(AdminWebservice::randname(8)),
						'address2' => strtoupper(AdminWebservice::randname(8)),
						'postcode' => rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9),
						'city' => strtoupper(AdminWebservice::randname(12)),
						'other' => strtoupper(AdminWebservice::randname(8)),
						'phone' =>'0'.rand(1,5).' '.rand(0,9).rand(0,9).' '.rand(0,9).rand(0,9).' '.rand(0,9).rand(0,9).' '.rand(0,9).rand(0,9).' ',
						'phone_mobile' => '06 '.rand(0,9).rand(0,9).' '.rand(0,9).rand(0,9).' '.rand(0,9).rand(0,9).' '.rand(0,9).rand(0,9).' ',
						'deleted' => 0,
						'id_country' => rand(1,100),
					),
	
				),
				'max_id_rq' => 'SELECT max(id_address) FROM `'._DB_PREFIX_.'address`',
				'existing_id_rq' => 'SELECT min(id_address) as id FROM `'._DB_PREFIX_.'address`'
			),
			'categories' => array(
				'fields' => array(
					'attributes' => array(
						'name' => array($this->id_lang_default => AdminWebservice::randname()),
						'active' => rand(0,1),
						'description' => array($this->id_lang_default => AdminWebservice::randname(50)),
						'level_depth' => rand(1,7),
						'link_rewrite' => array($this->id_lang_default => AdminWebservice::randname()),
						'meta_title' => array($this->id_lang_default => AdminWebservice::randname()),
						'meta_keywords' => array($this->id_lang_default => AdminWebservice::randname()),
						'meta_description' => array($this->id_lang_default => AdminWebservice::randname()),
						'id_parent' => rand(2,3),
					),
	
				),
				'max_id_rq' => 'SELECT max(id_category) FROM `'._DB_PREFIX_.'category`',
				'existing_id_rq' => 'SELECT min(id_category) as id FROM `'._DB_PREFIX_.'category`'
			),
			'product_options' => array(
				'fields' => array(
					'attributes' => array(
						'name' => array($this->id_lang_default => AdminWebservice::randname()),
						'is_color_group' => rand(0,1),
						'public_name' => array($this->id_lang_default => AdminWebservice::randname(50)),
					),
	
				),
				'max_id_rq' => 'SELECT max(id_attribute_group) FROM `'._DB_PREFIX_.'attribute_group`',
				'existing_id_rq' => 'SELECT min(id_attribute_group) as id FROM `'._DB_PREFIX_.'attribute_group`'
			),
			'product_option_values' => array(
				'fields' => array(
					'attributes' => array(
						'id_attribute_group' => rand(1,3),
						'color' => '#'.rand(0,6).rand(0,6).rand(0,6).rand(0,6).rand(0,6).rand(0,6),
						'default' => rand(0,1),
						'name' => array($this->id_lang_default => AdminWebservice::randname(50)),
					),
	
				),
				'max_id_rq' => 'SELECT max(id_attribute) FROM `'._DB_PREFIX_.'attribute`',
				'existing_id_rq' => 'SELECT min(id_attribute) as id FROM `'._DB_PREFIX_.'attribute`'
			),
			'product_features' => array(
				'fields' => array(
					'attributes' => array(
						'name' => array($this->id_lang_default => AdminWebservice::randname()),
					),
	
				),
				'max_id_rq' => 'SELECT max(id_feature) FROM `'._DB_PREFIX_.'feature`',
				'existing_id_rq' => 'SELECT min(id_feature) as id FROM `'._DB_PREFIX_.'feature`'
			),
			'product_feature_values' => array(
				'fields' => array(
					'attributes' => array(
						'id_feature' => rand(1,5),
						'value' => array($this->id_lang_default => AdminWebservice::randname()),
					),
	
				),
				'max_id_rq' => 'SELECT max(id_feature_value) FROM `'._DB_PREFIX_.'feature_value`',
				'existing_id_rq' => 'SELECT min(id_feature_value) as id FROM `'._DB_PREFIX_.'feature_value`'
			),
			'combinations' => array(
				'fields' => array(
					'attributes' => array(
						'reference' => '#'.strtoupper(AdminWebservice::randname(8)),
						'supplier_reference' => '#'.strtoupper(AdminWebservice::randname(2).'_'.AdminWebservice::randname(5)),
						'location' => AdminWebservice::randname(15),
						'ean13' => '1234567890123',
						'upc' => '123456789012',
						'wholesale_price' => rand(1,100),
						'price' => rand(1,1000)/10,
						'ecotax' => rand(1,1000)/10,
						'quantity' => rand(1,100),
						'weight' => rand(100,400)/100,
						'default_on' => 0,
						'id_product' => 1,
					),
					'associations' => array(
						'product_option_values' => array(
							array('id' => 1),
							array('id' => 2),
							array('id' => 3),
						),
					)
				),
				'max_id_rq' => 'SELECT max(id_product_attribute) FROM `'._DB_PREFIX_.'product_attribute`',
				'existing_id_rq' => 'SELECT min(id_product_attribute) as id FROM `'._DB_PREFIX_.'product_attribute`'
			),
			'products' => array(
				'fields' => array(
					'attributes' => array(
						'link_rewrite' => array($this->id_lang_default => AdminWebservice::randname()),
						//'description_short' => array($this->id_lang_default => AdminWebservice::randname(40)),
						'id_tax' => rand(1,5),
						'quantity' => rand(100000,1000000),
						'id_category_default' => 1,
						'price' => rand(1,5000),
						'out_of_stock' => 2,
						'name' => array($this->id_lang_default => AdminWebservice::randname()),
					),
					'associations' => array(
						'categories' => array(
							array('id' => 1),
							array('id' => 2),
							array('id' => 3),
						),
					)
				),
				'max_id_rq' => 'SELECT max(id_product) as id FROM `'._DB_PREFIX_.'product`',
				'existing_id_rq' => 'SELECT min(id_product) as id FROM `'._DB_PREFIX_.'product`'
			),
			'orders' => array(
				'fields' => array(
					'attributes' => array(
						'id_address_invoice' => rand(1,5),
						'id_cart' => rand(1,5),
						'id_lang' => rand(1,3),
						'id_customer' => rand(1,5),
						'id_currency' => rand(1,3),
						'id_carrier' => rand(1,2),
						'payment' => AdminWebservice::randname(10),
						'total_paid' => rand(1,100000)/100,
						'total_paid_real' => rand(1,100000)/100,
						'total_products' => rand(1,100000)/100,
						'total_products_wt' => rand(1,100000)/100,
						'secure_key' => '47ce86627c1f3c792a80773c5d2deaf8',
						'module' => AdminWebservice::randname(10),
						'recyclable' => rand(0,1),
						'gift' => rand(0,1),
						'gift_message' => AdminWebservice::randname(100),
						'shipping_number' => rand(1,100000),
						'total_discounts' => rand(1,100000)/100,
						'total_shipping' => rand(1,10000)/100,
						'total_wrapping' => rand(1,1000)/100,
						'invoice_number' => rand(1,1000000),
						'delivery_number' => rand(1,1000000),
						'invoice_date' => AdminWebservice::randdate(),
						'delivery_date' => AdminWebservice::randdate(),
						'valid' => rand(0,1),
						'id_address_delivery' => rand(1,5),
					),
	
				),
				'max_id_rq' => 'SELECT max(id_order) FROM `'._DB_PREFIX_.'orders`',
				'existing_id_rq' => 'SELECT min(id_order) as id FROM `'._DB_PREFIX_.'orders`'
			),
			'order_states' => array(
				'fields' => array(
					'attributes' => array(
						'template' => AdminWebservice::randname(8),
						'send_email' => rand(0,1),
						'invoice' => rand(0,1),
						'color' => '#'.rand(0,6).rand(0,6).rand(0,6).rand(0,6).rand(0,6).rand(0,6),
						'unremovable' => rand(0,1),
						'logable' => rand(0,1),
						'delivery' => rand(0,1),
						'hidden' => rand(0,1),
						'name' => AdminWebservice::randname(8),
					),
				),
				'max_id_rq' => 'SELECT max(id_order_state) FROM `'._DB_PREFIX_.'order_state`',
				'existing_id_rq' => 'SELECT min(id_order_state) as id FROM `'._DB_PREFIX_.'order_state`'
			),
			'order_histories' => array(
				'fields' => array(
					'attributes' => array(
						'id_order_state' => rand(1,5),
						'id_employee' => rand(1,2),
						'id_order' => rand(1,3),
					),
				),
				'max_id_rq' => 'SELECT max(id_order_history) FROM `'._DB_PREFIX_.'order_history`',
				'existing_id_rq' => 'SELECT min(id_order_history) as id FROM `'._DB_PREFIX_.'order_history`'
			),
			'carriers' => array(
				'fields' => array(
					'attributes' => array(
						'id_tax' => rand(1,3),
						'url' => 'http://'.AdminWebservice::randname(8).'.com',
						'delay' => array($this->id_lang_default => AdminWebservice::randname()),
						'active' => rand(0,1),
						'deleted' => rand(0,1),
						'shipping_handling' => rand(0,1),
						'range_behavior' => rand(0,1),
						'is_module' => rand(0,1),
						'name' => AdminWebservice::randname(8),
					),
				),
				'max_id_rq' => 'SELECT max(id_carrier) FROM `'._DB_PREFIX_.'carrier`',
				'existing_id_rq' => 'SELECT min(id_carrier) as id FROM `'._DB_PREFIX_.'carrier`'
			),
		);
		return $resources;
	}
	
	public function executeCase($resourceName, $resource, $data = false, $auto = false) {
		global $errors;
		$auth_key = Tools::getValue('auth_key');
		if (Tools::getValue('case') != 'all_cases')
		{
			$data = call_user_func(array('AdminWebservice','config_case'), Tools::getValue('case'), $resourceName, $resource);
			$data['method'] = strtoupper($data['method']);
			if (!in_array($data['method'], array('GET', 'POST', 'PUT', 'DELETE', 'HEAD')))
				$errors[] = 'Method '.$data['method'].' is not valid';
		}

		//////////////
		// get content
		//////////////

		$ch = curl_init();
		$url = Tools::getHttpHost(true).__PS_BASE_URI__.'api/'.$data['resource'].(isset($data['id']) ? '/'.$data['id'] : '');
		$curlopts = array(
				CURLOPT_URL => $url,
				CURLOPT_HEADER => 0,
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_TIMEOUT => 4,
				CURLOPT_FORBID_REUSE => 1,
				CURLOPT_CUSTOMREQUEST => $data['method'],
				CURLOPT_FRESH_CONNECT => 1,
				CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
				CURLOPT_USERPWD => $auth_key.':'
			
		);

		if ($data['method'] == 'POST')
		{
			$curlopts[CURLOPT_POSTFIELDS] = http_build_query($data['post_data']);
		}
		elseif ($data['method'] == 'PUT')
		{
			$xml = $data['put_data'];
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		}
		curl_setopt_array($ch, $curlopts);
		if (!$result = curl_exec($ch))
			if ($curl_error = curl_error($ch))
				trigger_error($curl_error);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		/////////////////
		// display result
		/////////////////

		if (!$auto)
		{
			echo '<div style="font-size:7pt;"><h2>'.$resourceName.'</h2>';
			echo '<h3>test case: '.Tools::getValue('case').'</h3>';
			echo '<pre>';
			$formattedData = str_replace('<', "\n<", str_replace('>', ">\n", $data));
			echo htmlentities(print_r($formattedData , true));
			echo '</pre>
			<h3>
			request: '.$data['method'].' '.$url."</h3>\n\n";

			echo '<h3>http code: '.$http_code."</h3>\n";
			if ($result)
			{
				echo '<pre style="background:white;">';
				print_r(htmlentities($result));
				echo '</pre>';
			}
			else
				echo 'http body is empty.';
			echo '</div>';
		}
		else
		{
			$xml_ok = true;
			if ($result != '')
			{
				libxml_use_internal_errors(true);
				$xml = simplexml_load_string($result);
				
				if (libxml_get_errors())
					$xml_ok = false;
				else
				{
					
					$namespaces = $xml->getNameSpaces(true);
					$p = $xml->children($namespaces['p']);
				}
			}
			$code_ok = ($http_code == $data['expected_return']['code']);
			$error_ok = true;
			// if no error atempted but error occure, there is a problem...
			if (!isset($data['expected_return']['error']) || !$data['expected_return']['error'])
			{
					if (isset($p->errors))
						$error_ok = false;
			}
			// if no error atempted but error occure, there is a problem...
			elseif (!isset($p->errors))
					$error_ok = false;
			return ($xml_ok && $error_ok && $code_ok);
		}
	}
	
	public function executeTests($cases, $resources)
	{
		global $currentIndex;
		$cases = array_keys($cases);
		$error = false;
		//select resource
		if (Tools::getValue('resource') !== '')
			if (array_key_exists(Tools::getValue('resource'), $resources))
				$resources = array(Tools::getValue('resource') => $resources[Tools::getValue('resource')]);
			else
			{
				echo $this->l('This resource does not exist.');
				$error = true;
			}
		//select case
		if (!Tools::getValue('case') || !in_array(Tools::getValue('case'), $cases))
		{
			echo $this->l('Please select a case in the list above.');
			$error = true;
		}
		else
			if (Tools::getValue('case') != 'all_cases')
				$cases = array(Tools::getValue('case'));
		
		if (!$error)
		{
			//execute tests
			echo '<ul style="margin:0; padding:0">';
			foreach ($resources as $resourceName => $resource)
			{
				echo '<li style="width:47%; float: left; margin:5px; padding:5px; list-style-type:none;"><h4 style="font-size:10pt;margin:0;text-align:center;text-transform:uppercase;">'.$resourceName.'</h4><ul style="padding:0">';
				foreach ($cases as $case)
				{
					if ($case != 'all_cases')
					{
						$detail = (count($resources + $cases) != 2);
						echo '<li style="list-style-type:none;">';
						$case_result = $this->executeCase($resourceName, $resource, call_user_func(array('AdminWebservice','config_case'), $case, $resourceName, $resource), $detail);
						if ($detail)
							echo
								($case_result ? '<span style="color:green;">ok</span>' : '<strong style="color:red;">FAIL</strong>').
								' <a href="'.$currentIndex.'&token='.$this->token.'&case='.$case.'&resource='.$resourceName.'&auth_key='.Tools::getValue('auth_key').'&submitWebserviceTests=#Tests">'.
								$case.
								'</a>';
						echo '</li>';
					}
				}
				echo '</ul></li>';
			}
			echo '</ul>';
		}
	}
}


