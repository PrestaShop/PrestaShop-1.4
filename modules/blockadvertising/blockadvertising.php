<?php

class BlockAdvertising extends Module
{
	public $adv_link;
	public $adv_img;
	public $adv_imgname;

	function __construct()
	{
		$this->name = 'blockadvertising';
		$this->tab = 'Blocks';
		$this->version = 0.1;

		parent::__construct();

		$this->displayName = $this->l('Block advertising');
		$this->description = $this->l('Adds a block to display an advertising');

		$this->adv_imgname = 'advertising_custom.jpg';

		if (!file_exists(dirname(__FILE__).'/'.$this->adv_imgname))
			$this->adv_img = Tools::getMediaServer($this->name)._MODULE_DIR_.$this->name.'/advertising.jpg';
		else
			$this->adv_img = Tools::getMediaServer($this->name)._MODULE_DIR_.$this->name.'/'.$this->adv_imgname;
		$this->adv_link = htmlentities(Configuration::get('BLOCKADVERT_LINK'), ENT_QUOTES, 'UTF-8');
	}


	function install()
	{
		Configuration::updateValue('BLOCKADVERT_LINK', 'http://www.prestashop.com');
		if (!parent::install())
			return false;
		if (!$this->registerHook('leftColumn') OR !$this->registerHook('header'))
			return false;
		return true;
	}

	public function postProcess()
	{
		global $currentIndex;

		$errors = false;
		if (Tools::isSubmit('submitAdvConf'))
		{
			$file = false;
			if (isset($_FILES['adv_img']) AND isset($_FILES['adv_img']['tmp_name']) AND !empty($_FILES['adv_img']['tmp_name']))
			{
				if ($error = checkImage($_FILES['adv_img'], 4000000))
					$errors .= $error;
				elseif (!move_uploaded_file($_FILES['adv_img']['tmp_name'], dirname(__FILE__).'/'.$this->adv_imgname))
					$errors .= $this->l('Error move uploaded file');

				$this->adv_img = _MODULE_DIR_.$this->name.'/'.$this->adv_imgname;
			}
			if ($link = Tools::getValue('adv_link'))
			{
				Configuration::updateValue('BLOCKADVERT_LINK', $link);
				$this->adv_link = htmlentities($link, ENT_QUOTES, 'UTF-8');
			}
		}
		if ($errors)
			echo $this->displayError($errors);
	}

	public function getContent()
	{
		$this->postProcess();
		echo '
<form action="'.$_SERVER['REQUEST_URI'].'" method="post" enctype="multipart/form-data">
<fieldset><legend>'.$this->l('Advertising block configuration').'</legend>
<a href="'.$this->adv_link.'" target="_blank" title="'.$this->l('Advertising').'">';
		if ($this->adv_img)
			echo '<img src="'.$this->adv_img.'" alt="'.$this->l('Advertising image').'" style="margin-left: 100px;"/>';
		else
			echo $this->l('no image');
		echo '
</a>
<br/>
<br/>
<label for="adv_img">'.$this->l('Change image').'&nbsp;&nbsp;</label><input id="adv_img" type="file" name="adv_img" />
<br/>
<br class="clear"/>
<label for="adv_link">'.$this->l('Image link').'&nbsp;&nbsp;</label><input id="adv_link" type="text" name="adv_link" value="'.$this->adv_link.'" />
<br class="clear"/>
<br/>
<input class="button" type="submit" name="submitAdvConf" value="'.$this->l('validate').'" style="margin-left: 200px;"/>
</fieldset>
</form>
';
	}

	/**
	* Returns module content
	*
	* @param array $params Parameters
	* @return string Content
	*/
	function hookRightColumn($params)
	{
		global $smarty, $protocol_content, $server_host;

		$smarty->assign('image', $protocol_content.$this->adv_img);
		$smarty->assign('adv_link', $this->adv_link);

		return $this->display(__FILE__, 'blockadvertising.tpl');
	}

	function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}
	
	function hookHeader($params)
	{
		Tools::addCSS(_THEME_CSS_DIR_.'modules/'.$this->name.'/blockadvertising.css', 'all');
	}

}

?>
