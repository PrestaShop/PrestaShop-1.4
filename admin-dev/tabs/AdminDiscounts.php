<?php

/**
  * Discounts tab for admin panel, AdminDiscounts.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminDiscounts extends AdminTab
{
	
	public function __construct()
	{
		global $cookie;
	 	
		$this->table = 'discount';
	 	$this->className = 'Discount';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->delete = true;
	 	$this->_select = 'dtl.`name` AS discount_type';
	 	$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'discount_type` dt ON (dt.`id_discount_type` = a.`id_discount_type`)
						LEFT JOIN `'._DB_PREFIX_.'discount_type_lang` dtl ON (dt.`id_discount_type` = dtl.`id_discount_type` AND dtl.`id_lang` = '.intval($cookie->id_lang).')';
		
		$typesArray = array();
		$types = Discount::getDiscountTypes(intval($cookie->id_lang));
		foreach ($types AS $type)
			$typesArray[$type['id_discount_type']] = $type['name'];
			
		$this->fieldsDisplay = array(
		'id_discount' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'name' => array('title' => $this->l('Code'), 'width' => 85, 'prefix' => '<span class="discount_name">', 'suffix' => '</span>', 'filter_key' => 'a!name'),
		'description' => array('title' => $this->l('Description'), 'width' => 100, 'filter_key' => 'b!description'),
		'discount_type' => array('title' => $this->l('Type'), 'type' => 'select', 'select' => $typesArray, 'filter_key' => 'dt!id_discount_type'),
		'value' => array('title' => $this->l('Value'), 'width' => 50, 'align' => 'right'),
		'quantity' => array('title' => $this->l('Qty'), 'width' => 40, 'align' => 'right'),
		'date_to' => array('title' => $this->l('To'), 'width' => 60, 'type' => 'date'),
		'active' => array('title' => $this->l('Status'), 'align' => 'center', 'active' => 'status', 'type' => 'bool', 'orderby' => false));
	
		$this->optionTitle = $this->l('Discounts options');
		$this->_fieldsOptions = array(
		'PS_VOUCHERS' => array('title' => $this->l('Enable vouchers:'), 'desc' => $this->l('Allow the use of vouchers in shop'), 'cast' => 'intval', 'type' => 'bool'),
		);
		parent::__construct();
	}
	
	protected function copyFromPost(&$object, $table)
	{		
		parent::copyFromPost($object, $table);
	
		$object->cumulable = (!isset($_POST['cumulable']) ? false : true);
		$object->cumulable_reduction = (!isset($_POST['cumulable_reduction']) ? false : true);
	}

	public function postProcess()
	{
		global $currentIndex, $cookie;
		$token = Tools::getValue('token') ? Tools::getValue('token') : $this->token;

		if ($discountName = Tools::getValue('name') AND Validate::isDiscountName($discountName) AND Discount::discountExists($discountName, Tools::getValue('id_discount')))
			$this->_errors[] = Tools::displayError('A voucher of this name already exists. Please choose another name.');
	
		if (Tools::getValue('submitAdd'.$this->table))
		{
			/* Checking fields validity */
			$this->validateRules();
			if (!sizeof($this->_errors))
			{
				$id = intval(Tools::getValue($this->identifier));

				/* Object update */
				if (isset($id) AND !empty($id))
				{
					if ($this->tabAccess['edit'] === '1')
					{
						$object = new $this->className($id);
						if (Validate::isLoadedObject($object))
						{
							/* Specific to objects which must not be deleted */
							if ($this->deleted AND $this->beforeDelete($object))
							{
								$object->deleted = 1;
								$object->update();
								$objectNew = new $this->className();
								$this->copyFromPost($objectNew, $this->table);
								$result = $objectNew->add();
								if (Validate::isLoadedObject($objectNew))
									$this->afterDelete($objectNew, $object->id);
							}
							else
							{
								if (($categories = Tools::getValue('categoryBox')) === false OR (!empty($categories) AND !is_array($categories)))
									die(Tools::displayError());
								$this->copyFromPost($object, $this->table);
								$result = $object->update(true, false, $categories);
							}
							if (!$result)
								$this->_errors[] = Tools::displayError('an error occurred while updating object').' <b>'.$this->table.'</b>';
							elseif ($this->postImage($object->id))
							{
								if ($back = Tools::getValue('back'))
									Tools::redirectAdmin(urldecode($back).'&conf=4');
								if (Tools::getValue('stay_here') == 'on' || Tools::getValue('stay_here') == 'true' || Tools::getValue('stay_here') == '1')
									Tools::redirectAdmin($currentIndex.'&'.$this->identifier.'='.$object->id.'&conf=4&updatescene&token='.$token);
								Tools::redirectAdmin($currentIndex.'&'.$this->identifier.'='.$object->id.'&conf=4&token='.$token);
								
							}
						}
						else
							$this->_errors[] = Tools::displayError('an error occurred while updating object').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
					}
					else
						$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
				}

				/* Object creation */
				else
				{
					if ($this->tabAccess['add'] === '1')
					{
						$object = new $this->className();
						$this->copyFromPost($object, $this->table);
						$categories = Tools::getValue('categoryBox', null);
						if (!$object->add(true, false, $categories))
							$this->_errors[] = Tools::displayError('an error occurred while creating object').' <b>'.$this->table.'</b>';
						elseif (($_POST[$this->identifier] = $object->id /* voluntary */) AND $this->postImage($object->id) AND $this->_redirect)
							Tools::redirectAdmin($currentIndex.'&'.$this->identifier.'='.$object->id.'&conf=3&token='.$token);
					}
					else
						$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
				}
			}
			$this->_errors = array_unique($this->_errors);
		}
		else
			return parent::postProcess();
	}

	public function displayForm()
	{
		global $currentIndex, $cookie;
		
		$obj = $this->loadObject(true);
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
		echo '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
		</script>
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" id="discount" name="discount" method="post" enctype="multipart/form-data">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset class="width3"><legend><img src="../img/admin/coupon.gif" />'.$this->l('Vouchers').'</legend>
				<label>'.$this->l('Code:').' </label>
				<div class="margin-form">
					<input type="text" size="30" maxlength="32" name="name" value="'.htmlentities($this->getFieldValue($obj, 'name'), ENT_COMPAT, 'UTF-8').'" style="text-transform: uppercase;" id="code" />
					<sup>*</sup>
					<img src="../img/admin/news-new.gif" onclick="gencode(8);" style="cursor: pointer" />
					<span class="hint" name="help_box">'.$this->l('Invalid characters: numbers and').' !<>,;?=+()@#"�{}_$%:<span class="hint-pointer">&nbsp;</span></span>
					<p style="clear: both;">'.$this->l('The voucher\'s code, at least 3 characters long, which the customer types in during check-out').'</p>
				</div>
				<label>'.$this->l('Type:').' </label>
				<div class="margin-form">
					<select name="id_discount_type" id="id_discount_type" onchange="free_shipping()">';
					
		$discountTypes = Discount::getDiscountTypes(intval($cookie->id_lang));
		foreach ($discountTypes AS $discountType)
			echo '<option value="'.intval($discountType['id_discount_type']).'"'.
			(($this->getFieldValue($obj, 'id_discount_type') == $discountType['id_discount_type']) ? ' selected="selected"' : '').'>'.$discountType['name'].'</option>';
			
		echo '
					</select>
				</div>
				<label>'.$this->l('Categories:').' </label>
					<div class="margin-form">
							<table cellspacing="0" cellpadding="0" class="table" style="width: 29.5em;">
									<tr>
										<th><input type="checkbox" name="checkme" class="noborder" onclick="checkDelBoxes(this.form, \'categoryBox[]\', this.checked)" /></th>
										<th>'.$this->l('ID').'</th>
										<th>'.$this->l('Name').'</th>
									</tr>';
		$done = array();
		$index = array();
		$indexedCategories =  isset($_POST['categoryBox']) ? $_POST['categoryBox'] : ($obj->id ? Discount::getCategories($obj->id) : array());
		$categories = Category::getCategories(intval($cookie->id_lang), false);
		foreach ($indexedCategories AS $k => $row)
			$index[] = $row['id_category'];
		$this->recurseCategoryForInclude($index, $categories, $categories[0][1], 1, $obj->id);
		echo '
							</table>
							<p style="padding:0px; margin:0px 0px 10px 0px;">'.$this->l('Mark all checkbox(es) of categories to which the discount is to be applicated').'<sup> *</sup></p>
						</div>
				<div class="clear" / >
				<label>'.$this->l('Description:').' </label>
				<div class="margin-form">';
				foreach ($languages as $language)
					echo '
					<div id="description_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="description_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'description', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
						<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
						<p style="clear: both;">'.$this->l('Will appear in cart next to voucher code').'</p>
					</div>';							
				$this->displayFlags($languages, $defaultLanguage, 'description', 'description');
		echo '
				</div><br /><br /><br />
				<div class="clear" / >
				<label>'.$this->l('Value:').' </label>
				<div class="margin-form">
					<input type="text" size="15" name="value" id="discount_value" value="'.floatval($this->getFieldValue($obj, 'value')).'" onKeyUp="javascript:this.value = this.value.replace(/,/g, \'.\'); " /> <sup>*</sup>
					<p style="clear: both;">'.$this->l('Either the monetary amount or the %, depending on Type selected above').'</p>
				</div>
				<label>'.$this->l('Total quantity:').' </label>
				<div class="margin-form">
					<input type="text" size="15" name="quantity" value="'.intval($this->getFieldValue($obj, 'quantity')).'" /> <sup>*</sup>
					<p style="clear: both;">'.$this->l('Total quantity available (mainly for vouchers open to everyone)').'</p>
				</div>
				<label>'.$this->l('Qty per each user:').' </label>
				<div class="margin-form">
					<input type="text" size="15" name="quantity_per_user" value="'.intval($this->getFieldValue($obj, 'quantity_per_user')).'" /> <sup>*</sup>
					<p style="clear: both;">'.$this->l('Number of times a single customer can use this voucher').'</p>
				</div>
				<label>'.$this->l('Minimum amount').'</label>
				<div class="margin-form">
					<input type="text" size="15" name="minimal" value="'.($this->getFieldValue($obj, 'minimal') ? floatval($this->getFieldValue($obj, 'minimal')) : '0').'" onkeyup="javascript:this.value = this.value.replace(/,/g, \'.\'); " /> <sup>*</sup>
					<p style="clear: both;">'.$this->l('Leave blank or 0 if not applicable').'</p>
				</div>
				<div class="margin-form">
					<p>
						<input type="checkbox" name="cumulable"'.(($this->getFieldValue($obj, 'cumulable') == 1) ? ' checked="checked"' : '').' id="cumulable_on" value="1" />
						<label class="t" for="cumulable_on"> '.$this->l('Cumulative with other vouchers').'</label>
					</p>
				</div>
				<div class="margin-form">
					<p>
						<input type="checkbox" name="cumulable_reduction"'.(($this->getFieldValue($obj, 'cumulable_reduction') == 1) ? ' checked="checked"' : '').' id="cumulable_reduction_on" value="1" />
						<label class="t" for="cumulable_reduction_on"> '.$this->l('Cumulative with price reductions').'</label>
					</p>
				</div>
				<label>'.$this->l('To be used by:').' </label>
								<div class="margin-form">				
					<select name="id_customer" id="id_customer">
						<option value="0">-- '.$this->l('All customers').' --</option>
					</select><br />'.$this->l('Filter:').' <input type="text" size="25" name="filter" id="filter" onkeyup="fillCustomersAjax();" class="space" value="" />
					<script type="text/javascript">
						var formDiscount = document.layers ? document.forms.discount : document.discount;	
						function fillCustomersAjax()
						{
							var filterValue = \''.(($value = intval($this->getFieldValue($obj, 'id_customer'))) ? $value : '').'\';
							if ($(\'#filter\').val())
								filterValue = $(\'#filter\').val();
							
							$.getJSON("'.dirname($currentIndex).'/ajax.php",{ajaxDiscountCustomers:1,filter:filterValue},
								function(customers) {
									if (customers.length == 0)
									{
										formDiscount.id_customer.length = 2;
										formDiscount.id_customer.options[1].value = -1;
										formDiscount.id_customer.options[1].text = \''.$this->l('No match found').'\';
										formDiscount.id_customer.options.selectedIndex = 1;
									}										
									else
									{
										formDiscount.id_customer.length = customers.length + 1;
										for (i = 0; i < customers.length && i < 50; i++)
										{
											formDiscount.id_customer.options[i+1].value = customers[i]["value"];
											formDiscount.id_customer.options[i+1].text = customers[i]["text"];
										}
										if (customers.length >= 50)
										{
											formDiscount.id_customer.options[50].text = "'.$this->l('Too much results...',__CLASS__ , true, false).'";
											formDiscount.id_customer.options[50].value = "_";	
										}
										
										if ($(\'#filter\').val())
											formDiscount.id_customer.options.selectedIndex = 1;
										else if(filterValue)
											for (i = 0; i < customers.length; i++)
												if (formDiscount.id_customer.options[i+1].value == filterValue)
													formDiscount.id_customer.options.selectedIndex = i + 1;
									}
								}
							);
						}
						fillCustomersAjax(); 
					</script>
				</div><br />';
		includeDatepicker(array('date_from', 'date_to'), true);
		echo '		
				<label>'.$this->l('From:').' </label>
				<div class="margin-form">
					<input type="text" size="20" id="date_from" name="date_from" value="'.($this->getFieldValue($obj, 'date_from') ? htmlentities($this->getFieldValue($obj, 'date_from'), ENT_COMPAT, 'UTF-8') : date('Y-m-d H:i:s')).'" /> <sup>*</sup>
					<p style="clear: both;">'.$this->l('Start date/time from which voucher can be used').'<br />'.$this->l('Format: YYYY-MM-DD HH:MM:SS').'</p>
				</div>
				<label>'.$this->l('To:').' </label>
				<div class="margin-form">
					<input type="text" size="20" id="date_to" name="date_to" value="'.($this->getFieldValue($obj, 'date_to') ? htmlentities($this->getFieldValue($obj, 'date_to'), ENT_COMPAT, 'UTF-8') : (date('Y') + 1).date('-m-d H:i:s')).'" /> <sup>*</sup>
					<p style="clear: both;">'.$this->l('End date/time at which voucher is no longer valid').'<br />'.$this->l('Format: YYYY-MM-DD HH:MM:SS').'</p>
				</div>
				<label>'.$this->l('Status:').' </label>
				<div class="margin-form">
					<input type="radio" name="active" id="active_on" value="1" '.($this->getFieldValue($obj, 'active') ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="active" id="active_off" value="0" '.(!$this->getFieldValue($obj, 'active') ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
					<p>'.$this->l('Enable or disable voucher').'</p>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}
		/**
	 * Build a categories tree
	 *
	 * @param array $indexedCategories Array with categories where product is indexed (in order to check checkbox)
	 * @param array $categories Categories to list
	 * @param array $current Current category
	 * @param integer $id_category Current category id
	 */
	function recurseCategoryForInclude($indexedCategories, $categories, $current, $id_category = 1, $id_category_default = NULL)
	{
		global $done;
		static $irow;
		$id_obj = intval(Tools::getValue($this->identifier));
		if (!isset($done[$current['infos']['id_parent']]))
			$done[$current['infos']['id_parent']] = 0;
		$done[$current['infos']['id_parent']] += 1;

		$todo = sizeof($categories[$current['infos']['id_parent']]);
		$doneC = $done[$current['infos']['id_parent']];

		$level = $current['infos']['level_depth'] + 1;
		$img = $level == 1 ? 'lv1.gif' : 'lv'.$level.'_'.($todo == $doneC ? 'f' : 'b').'.gif';

		echo '
		<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">
			<td>
				<input type="checkbox" name="categoryBox[]" class="categoryBox'.($id_category_default != NULL ? ' id_category_default' : '').'" id="categoryBox_'.$id_category.'" value="'.$id_category.'"'.(((in_array($id_category, $indexedCategories) OR (intval(Tools::getValue('id_category')) == $id_category AND !intval($id_obj))) OR Tools::getIsset('adddiscount')) ? ' checked="checked"' : '').' />
			</td>
			<td>
				'.$id_category.'
			</td>
			<td>
				<img src="../img/admin/'.$img.'" alt="" /> &nbsp;<label for="categoryBox_'.$id_category.'" class="t">'.stripslashes(Category::hideCategoryPosition($current['infos']['name'])).'</label>
			</td>
		</tr>';

		if (isset($categories[$id_category]))
			foreach ($categories[$id_category] AS $key => $row)
				if ($key != 'infos')
					$this->recurseCategoryForInclude($indexedCategories, $categories, $categories[$id_category][$key], $key);
	}
}

?>
