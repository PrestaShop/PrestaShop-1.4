<?php

class BlockMyAccount extends Module
{
	public function __construct()
	{
		$this->name = 'blockmyaccount';
		$this->tab = 'Blocks';
		$this->version = '1.2';

		parent::__construct();

		$this->displayName = $this->l('My Account block');
		$this->description = $this->l('Displays a block with links relative to user account');
	}

	public function install()
	{
		if (!$this->addMyAccountBlockHook() OR !parent::install() OR !$this->registerHook('leftColumn'))
			return false;
		return true;
	}

	public function uninstall()
	{
		return parent::uninstall() AND $this->removeMyAccountBlockHook();
	}

	public function hookLeftColumn($params)
	{
		global $smarty;
		
		if (!$params['cookie']->isLogged())
			return false;
		$smarty->assign(array(
			'voucherAllowed' => intval(Configuration::get('PS_VOUCHERS')),
			'returnAllowed' => intval(Configuration::get('PS_ORDER_RETURN')),
			'HOOK_BLOCK_MY_ACCOUNT' => Module::hookExec('myAccountBlock')
		));
		return $this->display(__FILE__, $this->name.'.tpl');
	}

	public function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params);
	}

	private function addMyAccountBlockHook()
	{
		return Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'hook` (`name`, `title`, `description`, `position`) VALUES (\'myAccountBlock\', \'My account block\', \'Display extra informations inside the "my account" block\', 1)');
	}

	private function removeMyAccountBlockHook()
	{
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'hook` WHERE `name` = \'myAccountBlock\'');
	}
}

?>
