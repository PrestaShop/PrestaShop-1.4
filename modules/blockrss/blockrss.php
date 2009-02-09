<?php

include_once(_PS_CLASS_DIR_.'PEAR.php');
include_once(_PS_PEAR_XML_PARSER_PATH_.'Parser.php');

class Blockrss extends Module
{
 	function __construct()
 	{
 	 	$this->name = 'blockrss';
 	 	$this->tab = 'Blocks';

		parent::__construct();

		$this->displayName = $this->l('RSS feed block');
		$this->description = $this->l('Adds a block displaying an RSS feed');

		$this->version = '1.0';
		$this->error = false;
		$this->valid = false;
 	}
 	
 	function install()
 	{
		Configuration::updateValue('RSS_FEED_TITLE', $this->l('RSS feed'));
		Configuration::updateValue('RSS_FEED_NBR', 5);
 	 	if (parent::install() == false OR $this->registerHook('leftColumn') == false)
 	 		return false;
  	}
	
	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitBlockRss'))
		{
			$urlfeed = strval(Tools::getValue('urlfeed'));
			$title = strval(Tools::getValue('title'));
			$nbr = intval(Tools::getValue('nbr'));
			if ($urlfeed AND !Validate::isUrl($urlfeed))
				$errors[] = $this->l('Invalid feed URL');
			elseif (!$title OR empty($title) OR !Validate::isGenericName($title))
				$errors[] = $this->l('Invalid title');
			elseif (!$nbr OR $nbr <= 0 OR !Validate::isInt($nbr))
				$errors[] = $this->l('Invalid number of feeds');
			else
			{
				Configuration::updateValue('RSS_FEED_URL', $urlfeed);
				Configuration::updateValue('RSS_FEED_TITLE', $title);
				Configuration::updateValue('RSS_FEED_NBR', $nbr);
			}
			if (isset($errors) AND sizeof($errors))
				$output .= $this->displayError(implode('<br />', $errors));
			else
				$output .= $this->displayConfirmation($this->l('Settings updated'));
		}

		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		$output = '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Block title').'</label>
				<div class="margin-form">
					<input type="text" name="title" value="'.Tools::getValue('title', Configuration::get('RSS_FEED_TITLE')).'" />
					<p class="clear">'.$this->l('Create a title for the block (default: \'RSS feed\')').'</p>
					
				</div>
				<label>'.$this->l('Add a feed URL').'</label>
				<div class="margin-form">
					<input type="text" size="85" name="urlfeed" value="'.Tools::getValue('urlfeed', Configuration::get('RSS_FEED_URL')).'" />
					<p class="clear">'.$this->l('Add the url of the feed you wan\'t to use').'</p>
					
				</div>
				<label>'.$this->l('Number of threads displayed').'</label>
				<div class="margin-form">
					<input type="text" size="5" name="nbr" value="'.Tools::getValue('nbr', Configuration::get('RSS_FEED_NBR')).'" />
					<p class="clear">'.$this->l('The number of threads displayed by the block (default value: 5)').'</p>
					
				</div>
				<center><input type="submit" name="submitBlockRss" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		</form>';
		return $output;
	}
 	
	function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params);
	}
 	
 	function hookLeftColumn($params)
 	{
		global $smarty;

		// Conf
		$title = strval(Configuration::get('RSS_FEED_TITLE'));
		$url = strval(Configuration::get('RSS_FEED_URL'));
		$nb = intval(Configuration::get('RSS_FEED_NBR'));
		
		// Getting data
		if ($contents = @file_get_contents($url))
		{
			if ($url) @$src = new XML_Feed_Parser($contents);
			$content = '';
			for ($i = 0; isset($src) AND $i < ($nb ? $nb : 5); ++$i)
			{
				@$item = $src->getEntryByOffset($i);
				$content .= '<li><a href="'.(@$item->link).'">'.Tools::htmlentitiesUTF8(@$item->title).'</a></li>';
			}
		}
		
		// Display smarty
		$smarty->assign('title', ($title ? $title : $this->l('RSS feed')));
		$smarty->assign('content', (isset($content) ? $content : ''));
 	 	return $this->display(__FILE__, 'blockrss.tpl');
 	}
}

?>
