<?php

/**
  * Emails tab for admin panel, AdminEmails.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
include_once(PS_ADMIN_DIR.'/tabs/AdminPreferences.php');

class AdminSearchConf extends AdminPreferences
{
	public function __construct()
	{
		global $cookie;

		$this->className = 'Configuration';
		$this->table = 'configuration';

 		$this->_fieldsSearch = array(
			'PS_SEARCH_AJAX' => array('title' => $this->l('Ajax search'), 'desc' => $this->l('Enable the ajax search for your visitors.'), 'validation' => 'isBool', 'type' => 'bool', 'cast' => 'intval'),
			'PS_SEARCH_MINWORDLEN' => array('title' => $this->l('Minimum word length'), 'desc' => $this->l('Only words longer than this size will be indexed.'), 'size' => 4, 'validation' => 'isUnsignedInt', 'type' => 'text', 'cast' => 'intval'),
			'PS_SEARCH_BLACKLIST' => array('title' => $this->l('Blacklisted words'), 'size' => 40, 'validation' => 'isGenericName', 'desc' => 'Please enter the words separated by a "|".', 'type' => 'textLang'),
			'PS_SEARCH_WEIGHT_PNAME' => array('title' => $this->l('Product name weight'), 'size' => 4, 'validation' => 'isUnsignedInt', 'type' => 'text', 'cast' => 'intval'),
			'PS_SEARCH_WEIGHT_REF' => array('title' => $this->l('Reference weight'), 'size' => 4, 'validation' => 'isUnsignedInt', 'type' => 'text', 'cast' => 'intval'),
			'PS_SEARCH_WEIGHT_SHORTDESC' => array('title' => $this->l('Short description weight'), 'size' => 4, 'validation' => 'isUnsignedInt', 'type' => 'text', 'cast' => 'intval'),
			'PS_SEARCH_WEIGHT_DESC' => array('title' => $this->l('Description weight'), 'size' => 4, 'validation' => 'isUnsignedInt', 'type' => 'text', 'cast' => 'intval'),
			'PS_SEARCH_WEIGHT_CNAME' => array('title' => $this->l('Category weight'), 'size' => 4, 'validation' => 'isUnsignedInt', 'type' => 'text', 'cast' => 'intval'),
			'PS_SEARCH_WEIGHT_MNAME' => array('title' => $this->l('Manufacturer weight'), 'size' => 4, 'validation' => 'isUnsignedInt', 'type' => 'text', 'cast' => 'intval'),
			'PS_SEARCH_WEIGHT_TAG' => array('title' => $this->l('Tags weight'), 'size' => 4, 'validation' => 'isUnsignedInt', 'type' => 'text', 'cast' => 'intval'),
			'PS_SEARCH_WEIGHT_ATTRIBUTE' => array('title' => $this->l('Attributes weight'), 'size' => 4, 'validation' => 'isUnsignedInt', 'type' => 'text', 'cast' => 'intval'),
			'PS_SEARCH_WEIGHT_FEATURE' => array('title' => $this->l('Features weight'), 'size' => 4, 'validation' => 'isUnsignedInt', 'type' => 'text', 'cast' => 'intval')
		);
	
		parent::__construct();
	}
	
	public function postProcess()
	{
		if (isset($_POST['submitSearch'.$this->table]))
		{
		 	if ($this->tabAccess['edit'] === '1')
				$this->_postConfig($this->_fieldsSearch);
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
	}
	
	public function display()
	{
		list($total, $indexed) = Db::getInstance()->getRow('SELECT COUNT(*) as "0", SUM(indexed) as "1" FROM '._DB_PREFIX_.'product');
		echo '
		<fieldset class="width3"><legend>'.$this->l('Indexation').'</legend>
			'.$this->l('Indexed products:').' <b>'.intval($indexed).' / '.intval($total).'</b>.<br /><br />
			-&gt; <a href="searchcron.php" class="bold">'.$this->l('Add missing products to index.').'</a><br />
			-&gt; <a href="searchcron.php?full=1" class="bold">'.$this->l('Re-build entire index.').'</a>
		</fieldset>
		<div class="clear">&nbsp;</div>';
		$this->_displayForm('search', $this->_fieldsSearch, $this->l('Search'), 'width2', 'search');
	}
}

?>