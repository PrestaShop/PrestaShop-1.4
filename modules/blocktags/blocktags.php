<?php

class BlockTags extends Module
{
	function __construct()
	{
		$this->name = 'blocktags';
		$this->tab = 'Blocks';
		$this->version = 1.0;

		parent::__construct();
		
		$this->displayName = $this->l('Tags block');
		$this->description = $this->l('Adds a block containing a tag cloud');
	}

	function install()
	{
		if (parent::install() == false 
				OR $this->registerHook('leftColumn') == false
				OR Configuration::updateValue('BLOCKTAGS_NBR', 10) == false)
			return false;
		return true;
	}

	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitBlockTags'))
		{
			if (!$tagsNbr = Tools::getValue('tagsNbr') OR empty($tagsNbr))
				$output .= '<div class="alert error">'.$this->l('You should fill the "tags displayed" field').'</div>';
			elseif (intval($tagsNbr) == 0)
				$output .= '<div class="alert error">'.$this->l('Invalid number.').'</div>';
			else
			{
				Configuration::updateValue('BLOCKTAGS_NBR', intval($tagsNbr));
				$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
			}
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		$output = '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Tags displayed').'</label>
				<div class="margin-form">
					<input type="text" name="tagsNbr" value="'.intval(Configuration::get('BLOCKTAGS_NBR')).'" />
					<p class="clear">'.$this->l('Set the number of tags to be displayed in this block').'</p>
				</div>
				<center><input type="submit" name="submitBlockTags" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		</form>';
		return $output;
	}

	/**
	* Returns module content for left column
	*
	* @param array $params Parameters
	* @return string Content
	*
	* @todo Links on tags (dedicated page or search ?)
	*/
	function hookLeftColumn($params)
	{
		global $smarty;

		$numberTags = intval(Configuration::get('BLOCKTAGS_NBR'));
		$tags = Tag::getMainTags(intval($params['cookie']->id_lang), $numberTags);
		if (!sizeof($tags))
			return '';
		$maxFontSize = 18;
		$minFontSize = 10;
		$maxNumber = intval($tags[0]['times']);
		$classPrefix = 'tag_level';
		for ($i = 0; $i < sizeof($tags); ++$i)
		{
			$tags[$i]['fontSize'] = floor(($maxFontSize * $tags[$i]['times']) / $maxNumber);
			if ($tags[$i]['fontSize'] < $minFontSize)
				$tags[$i]['fontSize'] = $minFontSize;
			// 2nd version: use CSS class
			$tags[$i]['class'] = $classPrefix.$tags[$i]['times'];
			if ($tags[$i]['times'] > 3)
				$tags[$i]['class'] = $classPrefix;
		}
		$smarty->assign('tags', $tags);
		return $this->display(__FILE__, 'blocktags.tpl');
	}

	function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params);
	}

}
