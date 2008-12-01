<?php

class BlockTags extends Module
{
	function __construct()
	{
		$this->name = 'blocktags';
		$this->tab = 'Blocks';
		$this->version = 1.0;

		parent::__construct(); /* The parent construct is required for translations */

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Tags block');
		$this->description = $this->l('Adds a block containing a tag cloud');
	}

	function install()
	{
		parent::install();
		$this->registerHook('leftColumn');
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
		$tags = Tag::getMainTags(intval($params['cookie']->id_lang));
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
