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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7540 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class psaDisaster extends ObjectModel
{
	public $id_order;
	public $id_psa_disaster;
	public $id_product;
	public $status;
	public $reason;
	public $date_add;
		
 	protected $fieldsRequired = array('id_order', 'id_psa_disaster', 'id_product', 'status', 'reason');
 	
 	protected $fieldsValidate = array('id_order' => 'isUnsignedId', 'id_psa_disaster' => 'isUnsignedId',
 	'status' => 'isUnsignedId', 'status' => 'isGenericName', 'reason' => 'isGenericName');
		
	protected $table = 'psa_disaster';
	protected $identifier = 'id_disaster';
	
	public function getFields()
	{
		parent::validateFields();
		
		$fields['id_order'] = (int)$this->id_order;
		$fields['id_psa_disaster'] = (int)$this->id_psa_disaster;
		$fields['id_product'] = (int)$this->id_product;
		$fields['status'] = pSQL($this->status);
		$fields['reason'] = pSQL($this->reason);
		$fields['date_add'] = pSQL($this->date_add);
		
		return $fields;
	}

	public function add($autodate = true, $nullValues = false)
	{
		return parent::add(true, $nullValues);
	}
}