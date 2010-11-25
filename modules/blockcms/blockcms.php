<?php

class BlockCms extends Module
{
	private $_html;

	public function __construct()
	{
		$this->name = 'blockcms';
		$this->tab = 'front_office_features';
		$this->version = 1.0;

		parent::__construct();

		$this->displayName = $this->l('CMS Block');
		$this->description = $this->l('Adds a block with several CMS links');
		$this->secure_key = Tools::encrypt($this->name);
	}

	public function install()
	{
		$languages = Language::getLanguages(false);
		$query_lang = 'INSERT INTO `'._DB_PREFIX_.'cms_block_lang` (`id_block_cms`, `id_lang`) VALUES';
		foreach ($languages as $language)
			$query_lang .= '(1, '.(int)($language['id_lang']).'),';
	
		if (!parent::install() OR
		!$this->registerHook('leftColumn') OR
		!$this->registerHook('rightColumn') OR
		!$this->registerHook('footer') OR
		!$this->registerHook('header') OR
		!Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'cms_block`(
		`id_block_cms` int(10) unsigned NOT NULL auto_increment,
		`id_cms_category` int(10) unsigned NOT NULL,
		`location` tinyint(1) unsigned NOT NULL,
		`position` int(10) unsigned NOT NULL default \'0\',
		PRIMARY KEY (`id_block_cms`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8') OR
		!Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'cms_block` (`id_cms_category`, `location`, `position`) VALUES(1, 0, 0)') OR
		!Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'cms_block_lang`(
		`id_block_cms` int(10) unsigned NOT NULL,
		`id_lang` int(10) unsigned NOT NULL,
		`name` varchar(40) NOT NULL default \'\',
		PRIMARY KEY (`id_block_cms`, `id_lang`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8') OR
		!Db::getInstance()->Execute(rtrim($query_lang, ',')) OR
		!Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'cms_block_page`(
		`id_block_cms_page` int(10) unsigned NOT NULL auto_increment,
		`id_block_cms` int(10) unsigned NOT NULL,
		`id_cms` int(10) unsigned NOT NULL,
		`is_category` tinyint(1) unsigned NOT NULL,
		PRIMARY KEY (`id_block_cms_page`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8') OR
		!Configuration::updateValue('FOOTER_CMS', '') OR
		!Configuration::updateValue('FOOTER_BLOCK_ACTIVATION', 1))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall() OR
		!Configuration::deleteByName('FOOTER_CMS') OR
		!Configuration::deleteByName('FOOTER_BLOCK_ACTIVATION') OR
		!Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'cms_block` , `'._DB_PREFIX_.'cms_block_page`, `'._DB_PREFIX_.'cms_block_lang`'))
			return false;
		return true;
	}

	public function getBlockCMS($id_block_cms)
	{
		$cms_block = Db::getInstance()->ExecuteS('
		SELECT `id_cms_category`, `location`, `display_store` FROM `'._DB_PREFIX_.'cms_block`
		WHERE `id_block_cms` = '.(int)($id_block_cms));
		$cms_block_lang = Db::getInstance()->ExecuteS('
		SELECT `id_lang`, `name` FROM `'._DB_PREFIX_.'cms_block_lang`
		WHERE `id_block_cms` = '.(int)($id_block_cms));
		
		foreach ($cms_block_lang as $lang)
			$cms_block['name'][$lang['id_lang']] = $lang['name'];
		
		return $cms_block;
	}
	
	private function getBlocksCMS($location)
	{
		global $cookie;
		
		$cms_block = Db::getInstance()->ExecuteS('
		SELECT bc.`id_block_cms`, bcl.`name` AS block_name, ccl.`name` AS category_name, bc.`position`, bc.`id_cms_category`, bc.`display_store`
		FROM `'._DB_PREFIX_.'cms_block` bc
		INNER JOIN `'._DB_PREFIX_.'cms_category_lang` ccl ON (bc.`id_cms_category` = ccl.`id_cms_category`)
		INNER JOIN `'._DB_PREFIX_.'cms_block_lang` bcl ON (bc.`id_block_cms` = bcl.`id_block_cms`)
		WHERE ccl.`id_lang` = '.(int)($cookie->id_lang).'
		AND bc.`location` = '.(int)($location).'
		AND bcl.`id_lang` = '.(int)($cookie->id_lang).'
		ORDER BY bc.`position`');
		
		return $cms_block;
	}
	
	public function getAllBlocksCMS()
	{
		$block = array();
		$block = array_merge($this->getBlocksCMS(0), $this->getBlocksCMS(1));
		return $block;
	}

	static public function getCMStitlesFooter()
	{
		global $cookie;
		
		$footer_cms = Configuration::get('FOOTER_CMS');
		if (empty($footer_cms))
			return array();
		$cms_categories = explode('|', $footer_cms);
		$display_footer = array();
		$link = new Link();
		foreach ($cms_categories as $cms_category)
		{
			$ids = explode('_', $cms_category);
			if ($ids[0] == 1)
			{
				$req = Db::getInstance()->getRow('
				SELECT `name`, `link_rewrite` FROM `'._DB_PREFIX_.'cms_category_lang`
				WHERE `id_cms_category` = '.(int)($ids[1]).'
				AND `id_lang` = '.(int)($cookie->id_lang));
				$display_footer[$cms_category]['link'] = $link->getCMSCategoryLink((int)($ids[1]), $req['link_rewrite']);
				$display_footer[$cms_category]['meta_title'] = $req['name'];
			}
			elseif ($ids[0] == 0)
			{
				$req = Db::getInstance()->getRow('
				SELECT `meta_title`, `link_rewrite` FROM `'._DB_PREFIX_.'cms_lang`
				WHERE `id_cms` = '.(int)($ids[1]).'
				AND `id_lang` = '.(int)($cookie->id_lang));
				$display_footer[$cms_category]['link'] = $link->getCMSLink((int)($ids[1]), $req['link_rewrite']);
				$display_footer[$cms_category]['meta_title'] = $req['meta_title'];
			}
		}
		return $display_footer;
	}

	static public function getCMStitles($location)
	{
		global $cookie;
		$cms_categories = Db::getInstance()->ExecuteS('
		SELECT bc.`id_block_cms`, bc.`id_cms_category`, bc.`display_store`, ccl.`link_rewrite`, ccl.`name` AS category_name, bcl.`name` AS block_name FROM `'._DB_PREFIX_.'cms_block` bc
		INNER JOIN `'._DB_PREFIX_.'cms_category_lang` ccl ON (bc.`id_cms_category` = ccl.`id_cms_category`)
		INNER JOIN `'._DB_PREFIX_.'cms_block_lang` bcl ON (bc.`id_block_cms` = bcl.`id_block_cms`)
		WHERE bc.`location` = '.(int)($location).'
		AND ccl.`id_lang` = '.(int)($cookie->id_lang).'
		AND bcl.`id_lang` = '.(int)($cookie->id_lang).'
		ORDER BY `position`');
		$display_cms = array();
		$link = new Link();
		foreach ($cms_categories as $cms_category)
		{
			$key = $cms_category['id_block_cms'];
			$display_cms[$key]['display_store'] = $cms_category['display_store'];
			$display_cms[$key]['cms'] = Db::getInstance()->ExecuteS('
			SELECT cl.`id_cms`, cl.`meta_title`, cl.`link_rewrite`
			FROM `'._DB_PREFIX_.'cms_block_page` bcp 
			INNER JOIN `'._DB_PREFIX_.'cms_lang` cl ON (bcp.`id_cms` = cl.`id_cms`)
			INNER JOIN `'._DB_PREFIX_.'cms` c ON (bcp.`id_cms` = c.`id_cms`)
			WHERE bcp.`id_block_cms` = '.(int)($cms_category['id_block_cms']).'
			AND cl.`id_lang` = '.(int)($cookie->id_lang).'
			AND bcp.`is_category` = 0
			AND c.`active` = 1');
			$links = array();
			if (sizeof($display_cms[$key]['cms']))
				foreach ($display_cms[$key]['cms'] as $row)
				{
					$row['link'] = $link->getCMSLink((int)($row['id_cms']), $row['link_rewrite']);
					$links[] = $row;
				}
			$display_cms[$key]['cms'] = $links;
			$display_cms[$key]['categories'] = Db::getInstance()->ExecuteS('
			SELECT bcp.`id_cms`, cl.`name`, cl.`link_rewrite` FROM `'._DB_PREFIX_.'cms_block_page` bcp 
			INNER JOIN `'._DB_PREFIX_.'cms_category_lang` cl ON (bcp.`id_cms` = cl.`id_cms_category`)
			WHERE bcp.`id_block_cms` = '.(int)($cms_category['id_block_cms']).'
			AND cl.`id_lang` = '.(int)($cookie->id_lang).'
			AND bcp.`is_category` = 1');
			$links = array();
			if (sizeof($display_cms[$key]['categories']))
				foreach ($display_cms[$key]['categories'] as $row)
				{
					$row['link'] = $link->getCMSCategoryLink((int)($row['id_cms']), $row['link_rewrite']);
					$links[] = $row;
				}
			$display_cms[$key]['categories'] = $links;
			$display_cms[$key]['name'] = $cms_category['block_name'];
			$display_cms[$key]['category_link'] = $link->getCMSCategoryLink((int)($cms_category['id_cms_category']), $cms_category['link_rewrite']);
			$display_cms[$key]['category_name'] = $cms_category['category_name'];
		}
		return $display_cms;
	}
	
	public function getAllCMSTitles()
	{
		$titles = array();
		foreach(self::getCMStitles(0) as $key => $title)
		{
			unset($title['categories']);
			unset($title['name']);
			unset($title['category_link']);
			unset($title['category_name']);
			$titles[$key] = $title;
		}
		foreach(self::getCMStitles(1) as $key => $title)
		{
			unset($title['categories']);
			unset($title['name']);
			unset($title['category_link']);
			unset($title['category_name']);
			$titles[$key] = $title;
		}		
		return $titles;
	}

	private function displayRecurseCheckboxes($categories, $selected, $has_suite = array())
	{
		static $irow = 0;
		
		$img = $categories['level_depth'] == 0 ? 'lv1.gif' : 'lv'.($categories['level_depth'] + 1).'_'.((sizeof($categories['cms']) OR isset($categories['children'])) ? 'b' : 'f').'.gif';
		
		$this->_html .= '
			<tr '.($irow++ % 2 ? 'class="alt_row"' : '').'>
				<td width="3%"><input type="checkbox" name="footerBox[]" class="cmsBox" id="1_'.$categories['id_cms_category'].'" value="1_'.$categories['id_cms_category'].'" '.
				(in_array('1_'.$categories['id_cms_category'], $selected) ? ' checked="checked"' : '').' /></td>
				<td width="3%">'.$categories['id_cms_category'].'</td>
				<td width="94%">';
		for ($i = 1; $i < $categories['level_depth']; $i++)
			$this->_html .=	'<img style="vertical-align:middle;" src="../img/admin/lvl_'.$has_suite[$i - 1].'.gif" alt="" />';
		$this->_html .= '<img style="vertical-align:middle;" src="../img/admin/'.($categories['level_depth'] == 0 ? 'lv1' : 'lv2_'.(($has_suite[$categories['level_depth'] - 1]) ? 'b' : 'f')).'.gif" alt="" /> &nbsp;
				<label for="1_'.$categories['id_cms_category'].'" class="t"><b>'.$categories['name'].'</b></label></td>
			</tr>';
		if (isset($categories['children']))
			foreach ($categories['children'] as $key => $category)
			{
				$has_suite[$categories['level_depth']] = 1;
				if (sizeof($categories['children']) == $key + 1 AND !sizeof($categories['cms']))
					$has_suite[$categories['level_depth']] = 0;
				$this->displayRecurseCheckboxes($category, $selected, $has_suite, 0);
			}
		
		$cpt = 0;
		foreach ($categories['cms'] as $cms)
		{
			$this->_html .= '
				<tr '.($irow++ % 2 ? 'class="alt_row"' : '').'>
					<td width="3%"><input type="checkbox" name="footerBox[]" class="cmsBox" id="0_'.$cms['id_cms'].'" value="0_'.$cms['id_cms'].'" '.
					(in_array('0_'.$cms['id_cms'], $selected) ? ' checked="checked"' : '').' /></td>
					<td width="3%">'.$cms['id_cms'].'</td>
					<td width="94%">';
			for ($i = 0; $i < $categories['level_depth']; $i++)
				$this->_html .=	'<img style="vertical-align:middle;" src="../img/admin/lvl_'.$has_suite[$i].'.gif" alt="" />';
			$this->_html .= '<img style="vertical-align:middle;" src="../img/admin/lv2_'.(++$cpt == sizeof($categories['cms']) ? 'f' : 'b').'.gif" alt="" /> &nbsp;
			<label for="0_'.$cms['id_cms'].'" class="t" style="margin-top:6px;">'.$cms['meta_title'].'</label></td>
				</tr>';
		}
	}

	private function _displayForm()
	{
		global $currentIndex, $cookie;
		
		$cms_blocks_left = $this->getBlocksCMS(0);
		$cms_blocks_right = $this->getBlocksCMS(1);

		$this->_html .= '
		<script type="text/javascript" src="../js/jquery/jquery.tablednd_0_5.js"></script>
		<script type="text/javascript" src="../modules/blockcms/blockcms.js"></script>
		<script type="text/javascript">CMSBlocksDnD(\''.$this->secure_key.'\');</script>
		<fieldset>
			<legend><img src="'._PS_BASE_URL_.__PS_BASE_URI__.'modules/'.$this->name.'/logo.gif" alt="" /> '.$this->l('CMS blocks configuration').'</legend>

			<p><a href="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&addBlockCMS"><img src="'._PS_ADMIN_IMG_.'add.gif" alt="" /> '.$this->l('Add a new block cms').'</a></p>';
			
		$this->_html .= '<div style="width:440px; float:left; margin-right:10px;" ><h3>'.$this->l('List of Left CMS blocks').'</h3>';
		if (sizeof($cms_blocks_left))
		{
			$this->_html .= '<table width="100%" class="table" cellspacing="0" cellpadding="0" id="table_left" class="tableDnD">
			<thead>
			<tr class="nodrag nodrop">
				<th width="10%"><b>'.$this->l('ID').'</b></th>
				<th width="30%" class="center"><b>'.$this->l('Block\'s Name').'</b></th>
				<th width="30%" class="center"><b>'.$this->l('Category Name').'</b></th>
				<th width="10%" class="center"><b>'.$this->l('Position').'</b></th>
				<th width="10%" class="center"><b>'.$this->l('Actions').'</b></th>
			</tr>
			</thead>
			<tbody>
			';
			$irow = 0;
			foreach ($cms_blocks_left as $cms_block)
			{
				$this->_html .= '
					<tr id="tr_0_'.$cms_block['id_block_cms'].'_'.$cms_block['position'].'" '.($irow++ % 2 ? 'class="alt_row"' : '').'>
						<td width="10%">'.$cms_block['id_block_cms'].'</td>
						<td width="30%" class="center">'.(empty($cms_block['block_name']) ? $cms_block['category_name'] : $cms_block['block_name']).'</td>
						<td width="30%" class="center">'.$cms_block['category_name'].'</td>
						<td class="center pointer dragHandle">
							<a'.(($cms_block['position'] == (sizeof($cms_blocks_left) - 1) OR sizeof($cms_blocks_left) == 1) ? ' style="display: none;"' : '').' href="'.$currentIndex.'&configure=blockcms&id_block_cms='.$cms_block['id_block_cms'].'&way=1&position='.(int)($cms_block['position'] + 1).'&location=0&token='.Tools::getAdminTokenLite('AdminModules').'">
							<img src="../img/admin/down.gif" alt="'.$this->l('Down').'" title="'.$this->l('Down').'" /></a>
							<a'.($cms_block['position'] == 0 ? ' style="display: none;"' : '').' href="'.$currentIndex.'&configure=blockcms&id_block_cms='.$cms_block['id_block_cms'].'&way=0&position='.(int)($cms_block['position'] - 1).'&location=0&token='.Tools::getAdminTokenLite('AdminModules').'">
							<img src="../img/admin/up.gif" alt="'.$this->l('Up').'" title="'.$this->l('Up').'" /></a>
						</td>
						<td width="10%" class="center">
							<a href="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&editBlockCMS&id_block_cms='.(int)($cms_block['id_block_cms']).'" title="'.$this->l('Edit').'"><img src="'._PS_ADMIN_IMG_.'edit.gif" alt="" /></a> 
							<a href="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&deleteBlockCMS&id_block_cms='.(int)($cms_block['id_block_cms']).'" title="'.$this->l('Delete').'"><img src="'._PS_ADMIN_IMG_.'delete.gif" alt="" /></a>
						</td>
					</tr>';
			}
		$this->_html .= '
			</tbody>
			</table>';
		}
		else
			$this->_html .= '<p style="margin-left:40px;">'.$this->l('There is no CMS block set').'</p>';
		$this->_html .= '</div>';
		
		$this->_html .= '<div style="width:440px; float:left;" ><h3>'.$this->l('List of Right CMS blocks').'</h3>';
		if (sizeof($cms_blocks_right))
		{
			$this->_html .= '<table width="100%" class="table" cellspacing="0" cellpadding="0" id="table_right" class="tableDnD">
			<thead>
			<tr class="nodrag nodrop">
				<th width="10%"><b>'.$this->l('ID').'</b></th>
				<th width="30%" class="center"><b>'.$this->l('Block\'s Name').'</b></th>
				<th width="30%" class="center"><b>'.$this->l('Category Name').'</b></th>
				<th width="10%" class="center"><b>'.$this->l('Position').'</b></th>
				<th width="10%" class="center"><b>'.$this->l('Actions').'</b></th>
			</tr>
			</thead>
			<tbody>
			';
			$irow = 0;
			foreach ($cms_blocks_right as $cms_block)
			{
				$this->_html .= '
					<tr id="tr_1_'.$cms_block['id_block_cms'].'_'.$cms_block['position'].'" '.($irow++ % 2 ? 'class="alt_row"' : '').'>
						<td width="10%">'.$cms_block['id_block_cms'].'</td>
						<td width="30%" class="center">'.(empty($cms_block['block_name']) ? $cms_block['category_name'] : $cms_block['block_name']).'</td>
						<td width="30%" class="center">'.$cms_block['category_name'].'</td>
						<td class="center pointer dragHandle">
							<a'.(($cms_block['position'] == (sizeof($cms_blocks_right) - 1) OR sizeof($cms_blocks_right) == 1) ? ' style="display: none;"' : '').' href="'.$currentIndex.'&configure=blockcms&id_block_cms='.$cms_block['id_block_cms'].'&way=1&position='.(int)($cms_block['position'] + 1).'&location=1&token='.Tools::getAdminTokenLite('AdminModules').'">
							<img src="../img/admin/down.gif" alt="'.$this->l('Down').'" title="'.$this->l('Down').'" /></a>
							<a'.($cms_block['position'] == 0 ? ' style="display: none;"' : '').' href="'.$currentIndex.'&configure=blockcms&id_block_cms='.$cms_block['id_block_cms'].'&way=0&position='.(int)($cms_block['position'] - 1).'&location=1&token='.Tools::getAdminTokenLite('AdminModules').'">
							<img src="../img/admin/up.gif" alt="'.$this->l('Up').'" title="'.$this->l('Up').'" /></a>
						</td>
						<td width="10%" class="center">
							<a href="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&editBlockCMS&id_block_cms='.(int)($cms_block['id_block_cms']).'" title="'.$this->l('Edit').'"><img src="'._PS_ADMIN_IMG_.'edit.gif" alt="" /></a> 
							<a href="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&deleteBlockCMS&id_block_cms='.(int)($cms_block['id_block_cms']).'" title="'.$this->l('Delete').'"><img src="'._PS_ADMIN_IMG_.'delete.gif" alt="" /></a>
						</td>
					</tr>';
			}
		$this->_html .= '
			</tbody>
			</table>';
		}
		else
			$this->_html .= '<p style="margin-left:40px;">'.$this->l('There is no CMS block set').'</p>';
		$this->_html .= '</div>
			<div class="clear"></div>
		</fieldset><br />
		<form method="POST" action="'.$_SERVER['REQUEST_URI'].'">
		<fieldset>
			<legend><img src="'._PS_BASE_URL_.__PS_BASE_URI__.'modules/'.$this->name.'/logo.gif" alt="" /> '.$this->l('Footer\'s various links Configuration').'</legend>
			<input type="checkbox" name="footer_active" id="footer_active" '.(Configuration::get('FOOTER_BLOCK_ACTIVATION') ? 'checked="checked"' : '').'> <label for="footer_active" style="float:none;">'.$this->l('Display the Footer\'s various links').'</label><br /><br />
			<table cellspacing="0" cellpadding="0" class="table" width="100%">
				<tr>
					<th width="3%"><input type="checkbox" name="checkme" class="noborder" onclick="checkallCMSBoxes($(this).attr(\'checked\'))" /></th>
					<th width="3%">'.$this->l('ID').'</th>
					<th width="94%">'.$this->l('Name').'</th>
				</tr>';
			self::displayRecurseCheckboxes(CMSCategory::getRecurseCategory($cookie->id_lang), explode('|', Configuration::get('FOOTER_CMS')));
		$this->_html .= '
			</table>
			<p class="center"><input type="submit" class="button" name="submitFooterCMS" value="'.$this->l('Save').'" /></p>
		</fieldset>
		</form>';
	}

	private function _displayAddForm()
	{
		global $currentIndex, $cookie;

		$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages(false);
		$divLangName = 'name';

		$block_cms = NULL;
		if (Tools::isSubmit('editBlockCMS') AND (int)(Tools::getValue('id_block_cms')))
			$block_cms = $this->getBlockCMS((int)(Tools::getValue('id_block_cms')));

		$this->_html .= '
		<script type="text/javascript" src="'._PS_BASE_URL_.__PS_BASE_URI__.'modules/'.$this->name.'/'.$this->name.'.js"></script>
		<script type="text/javascript">id_language = Number('.$defaultLanguage.');</script>
		<form method="POST" action="'.$_SERVER['REQUEST_URI'].'">
		';
		if (Tools::getValue('id_block_cms'))
			$this->_html .= '<input type="hidden" name="id_block_cms" value="'.(int)(Tools::getValue('id_block_cms')).'" id="id_block_cms" />';
		$this->_html .= '
		<fieldset>';
		
		if (Tools::isSubmit('addBlockCMS'))
			$this->_html .= '<legend><img src="'._PS_ADMIN_IMG_.'add.gif" alt="" /> '.$this->l('New CMS block').'</legend>';
		elseif (Tools::isSubmit('editBlockCMS'))
			$this->_html .= '<legend><img src="'._PS_BASE_URL_.__PS_BASE_URI__.'modules/'.$this->name.'/logo.gif" alt="" /> '.$this->l('Edit CMS block').'</legend>';
		
		$this->_html .= '
			<label>'.$this->l('Block\'s name:').'</label>
			<div class="margin-form">';
				
				foreach ($languages as $language)
					$this->_html .= '
					<div id="name_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">
						<input type="text" name="block_name_'.$language['id_lang'].'" id="block_name_'.$language['id_lang'].'" size="30" value="'.(Tools::getValue('block_name_'.$language['id_lang']) ? Tools::getValue('block_name_'.$language['id_lang']) : (isset($block_cms['name'][$language['id_lang']]) ? $block_cms['name'][$language['id_lang']] : '')).'" />
					</div>';
				$this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'name', true);
		$this->_html .= '<p class="clear">'.$this->l('If your left this field empty, the block\'s name will be the category\'s name').'</p>
			</div><br />
			<label for="id_category">'.$this->l('Choose a CMS category:').'</label>
			<div class="margin-form">
				<select name="id_category" id="id_category" onchange="CMSCategory_js($(this).val(), \''.$this->secure_key.'\')">';
		$categories = CMSCategory::getCategories((int)($cookie->id_lang), false);
		$this->_html .= CMSCategory::recurseCMSCategory($categories, $categories[0][1], 1, ($block_cms != NULL ? $block_cms[0]['id_cms_category'] : 1), 1);
		$this->_html .= '
				</select>
			</div><br />
			
			<label>'.$this->l('Location:').'</label>
			<div class="margin-form">
				<select name="block_location" id="block_location">
					<option value="0" '.(($block_cms AND $block_cms[0]['location'] == 0) ? 'selected="selected"' : '').'>'.$this->l('Left').'</option>
					<option value="1" '.(($block_cms AND $block_cms[0]['location'] == 1) ? 'selected="selected"' : '').'>'.$this->l('Right').'</option>
				</select>
			</div>';
		$this->_html .=	'
			<label for="PS_STORES_DISPLAY_CMS_on">'.$this->l('Display Stores:').'</label>
			<div class="margin-form">
				<img src="../img/admin/enabled.gif" alt="Yes" title="Yes" />
		        <input type="radio" name="PS_STORES_DISPLAY_CMS" id="PS_STORES_DISPLAY_CMS_on" '.(($block_cms AND ( isset($block_cms[0]['display_store']) && $block_cms[0]['display_store'] == 0)) ? '' : 'checked="checked" ').'value="1" />
			    <label class="t" for="PS_STORES_DISPLAY_CMS_on">'.$this->l('Yes').'</label>
			    <img src="../img/admin/disabled.gif" alt="No" title="No" style="margin-left: 10px;" />
			    <input type="radio" name="PS_STORES_DISPLAY_CMS" id="PS_STORES_DISPLAY_CMS_off" '.(($block_cms AND ( isset($block_cms[0]['display_store']) && $block_cms[0]['display_store'] == 0)) ? 'checked="checked" ' : '').'value="0" />
			    <label  class="t" for="PS_STORES_DISPLAY_CMS_off">'.$this->l('No').'</label><br />'
				.$this->l('Display "our stores" at the end of the block')
			.'</div>';
		$this->_html .=	'<div id="cms_subcategories"></div>
			<p class="center">
				<input type="submit" class="button" name="submitBlockCMS" value="'.$this->l('Save').'" />
				<a class="button" style="position:relative; padding:3px 3px 4px 3px; top:1px" href="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Cancel').'</a>
			</p>
		';
		
		$this->_html .= '
		</fieldset>
		</form>
		<script type="text/javascript">CMSCategory_js($(\'#id_category\').val(), \''.$this->secure_key.'\')</script>
		';
	}

	private function _postValidation()
	{
		$errors = array();
		if (Tools::isSubmit('submitBlockCMS'))
		{
			$languages = Language::getLanguages(false);
			$cmsBoxes = Tools::getValue('cmsBox');
			if (!Validate::isInt(Tools::getValue('PS_STORES_DISPLAY_CMS')) OR (Tools::getValue('PS_STORES_DISPLAY_CMS') != 0 AND Tools::getValue('PS_STORES_DISPLAY_CMS') != 1))
			    $errors[] = $this->l('Invalid store displaying');
			if (!Validate::isInt(Tools::getValue('block_location')) OR (Tools::getValue('block_location') != 0 AND Tools::getValue('block_location') != 1))
				$errors[] = $this->l('Invalid block location');
			if (!is_array($cmsBoxes))
				$errors[] = $this->l('You must choose at least one page or subcategory to create a CMS block');
			else
				foreach ($cmsBoxes as $cmsBox)
					if (!preg_match("#^[01]_[0-9]+$#", $cmsBox))
						$errors[] = $this->l('Invalid CMS page or category');
			foreach ($languages as $language)
				if (strlen(Tools::getValue('block_name_'.$language['id_lang'])) > 40)
					$errors[] = $this->l('Block name is too long');
		}
		elseif (Tools::isSubmit('deleteBlockCMS') AND !Validate::isInt(Tools::getValue('id_block_cms')))
			$errors[] = $this->l('Invalid id_block_cms');
		elseif (Tools::isSubmit('submitFooterCMS'))
		{
			if (Tools::getValue('footerBox'))
				foreach (Tools::getValue('footerBox') as $cmsBox)
					if (!preg_match("#^[01]_[0-9]+$#", $cmsBox))
						$errors[] = $this->l('Invalid CMS page or category');
			if (Tools::getValue('footer_active') != 0 AND Tools::getValue('footer_active') != 1)
				$errors[] = $this->l('Invalid activation footer');
		}
		if (sizeof($errors))
		{
			$this->_html .= $this->displayError(implode('<br />', $errors));
			return false;
		}
		return true;
	}
	
	private function changePosition()
	{
		$this->_html .= 'pos change!';
		if (Tools::getValue('way') == 0)
		{
			if (Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'cms_block`
			SET `position` = '.((int)(Tools::getValue('position')) + 1).'
			WHERE `position` = '.((int)(Tools::getValue('position'))).'
			AND `location` = '.(int)(Tools::getValue('location'))))
				Db::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.'cms_block`
				SET `position` = '.((int)(Tools::getValue('position'))).'
				WHERE `id_block_cms` = '.(int)(Tools::getValue('id_block_cms')));
		}
		elseif (Tools::getValue('way') == 1)
		{
			if(Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'cms_block`
			SET `position` = '.((int)(Tools::getValue('position')) - 1).'
			WHERE `position` = '.((int)(Tools::getValue('position'))).'
			AND `location` = '.(int)(Tools::getValue('location'))))
				Db::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.'cms_block`
				SET `position` = '.((int)(Tools::getValue('position'))).'
				WHERE `id_block_cms` = '.(int)(Tools::getValue('id_block_cms')));
		}
		Tools::redirectAdmin($currentIndex.'index.php?tab=AdminModules&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
	}
	
	private function _postProcess()
	{
		global $currentIndex;
	
		if (Tools::isSubmit('submitBlockCMS'))
		{
			$position = Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'cms_block` WHERE location = '.(int)(Tools::getValue('block_location')));
			$languages = Language::getLanguages(false);
			if (Tools::isSubmit('addBlockCMS'))
			{
				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'cms_block` (`id_cms_category`, `location`, `position`, `display_store`) VALUES('.(int)(Tools::getValue('id_category')).', '.(int)(Tools::getValue('block_location')).', '.(int)($position).', '.(int)(Tools::getValue('PS_STORES_DISPLAY_CMS')).')');
				$id_block_cms = Db::getInstance()->Insert_ID();
				foreach ($languages as $language)
					Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'cms_block_lang` (`id_block_cms`, `id_lang`, `name`) VALUES('.(int)($id_block_cms).', '.(int)($language['id_lang']).', "'.pSQL(Tools::getValue('block_name_'.$language['id_lang'])).'")');
			}
			elseif (Tools::isSubmit('editBlockCMS'))
			{
				$id_block_cms = Tools::getvalue('id_block_cms');
				$old_block = Db::getInstance()->ExecuteS('SELECT `location`, `position` FROM `'._DB_PREFIX_.'cms_block` WHERE `id_block_cms` = '.(int)($id_block_cms));
				$location_change = ($old_block[0]['location'] != (int)(Tools::getvalue('block_location')));
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'cms_block_page` WHERE `id_block_cms` = '.(int)($id_block_cms));
				if ($location_change == true)
					Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'cms_block` SET `position` = (`position` - 1) WHERE `position` > '.(int)($old_block[0]['position']).' AND `location` = '.(int)($old_block[0]['location']));
				Db::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.'cms_block`
				SET `location` = '.(int)(Tools::getvalue('block_location')).',
				`id_cms_category` = '.(int)(Tools::getvalue('id_category')).'
				'.($location_change == true ? ',
				`position` = '.(int)($position) : '').',
				`display_store` = '.(int)(Tools::getValue('PS_STORES_DISPLAY_CMS')).'
				WHERE `id_block_cms` = '.(int)($id_block_cms));
				
				foreach ($languages as $language)
					Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'cms_block_lang` SET `name` = "'.pSQL(Tools::getValue('block_name_'.$language['id_lang'])).'" WHERE `id_block_cms` = '.(int)($id_block_cms).' AND `id_lang`= '.(int)($language['id_lang']));
			}
			$cmsBoxes = Tools::getValue('cmsBox');
			if (sizeof($cmsBoxes))
				foreach ($cmsBoxes as $cmsBox)
				{
					$cms_properties = explode('_', $cmsBox);
					Db::getInstance()->Execute('
					INSERT INTO `'._DB_PREFIX_.'cms_block_page` (`id_block_cms`, `id_cms`, `is_category`) 
					VALUES('.(int)($id_block_cms).', '.(int)($cms_properties[1]).', '.(int)($cms_properties[0]).')');
				}
			if (Tools::isSubmit('addBlockCMS'))
				Tools::redirectAdmin($currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&addBlockCMSConfirmation');
			elseif (Tools::isSubmit('editBlockCMS'))
				Tools::redirectAdmin($currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&editBlockCMSConfirmation');
		}
		elseif (Tools::isSubmit('deleteBlockCMS') AND Tools::getValue('id_block_cms'))
		{
			$old_block = Db::getInstance()->ExecuteS('SELECT `location`, `position` FROM `'._DB_PREFIX_.'cms_block` WHERE `id_block_cms` = '.Tools::getvalue('id_block_cms'));
			if (sizeof($old_block))
			{
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'cms_block` SET `position` = (`position` - 1) WHERE `position` > '.$old_block[0]['position'].' AND `location` = '.$old_block[0]['location']);
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'cms_block` WHERE `id_block_cms` = '.(int)(Tools::getValue('id_block_cms')));
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'cms_block_page` WHERE `id_block_cms` = '.(int)(Tools::getValue('id_block_cms')));
				Tools::redirectAdmin($currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&deleteBlockCMSConfirmation');
			}
			else
				$this->_html .= $this->displayError($this->l('Trying to delete a non-existent block cms'));
		}
		elseif (Tools::isSubmit('submitFooterCMS'))
		{
			$footer = '';
			if (Tools::getValue('footerBox'))
				foreach (Tools::getValue('footerBox') as $box)
					$footer .= $box.'|';
			Configuration::updateValue('FOOTER_CMS', rtrim($footer, '|'));
			Configuration::updateValue('FOOTER_BLOCK_ACTIVATION', Tools::getValue('footer_active'));
			$this->_html = $this->displayConfirmation($this->l('Footer\'s CMS succesfully updated'));
		}
		elseif (Tools::isSubmit('addBlockCMSConfirmation'))
			$this->_html = $this->displayConfirmation($this->l('Block CMS succesfully added'));
		elseif (Tools::isSubmit('editBlockCMSConfirmation'))
			$this->_html = $this->displayConfirmation($this->l('Block CMS succesfully edited'));
		elseif (Tools::isSubmit('deleteBlockCMSConfirmation'))
			$this->_html .= $this->displayConfirmation($this->l('Deletion succesfully done'));
		elseif (Tools::isSubmit('id_block_cms') AND Tools::isSubmit('way') AND Tools::isSubmit('position') AND Tools::isSubmit('location'))
			$this->changePosition();
	}

	public function getContent()
	{
		$this->_html = '';
		if ($this->_postValidation())
			$this->_postProcess();
		$this->_html .= '<h2>'.$this->l('CMS Block configuration').'</h2>';
		if (Tools::isSubmit('addBlockCMS') OR Tools::isSubmit('editBlockCMS'))
			$this->_displayAddForm();
		else
			$this->_displayForm();
		return $this->_html;
	}
	
	public function hookLeftColumn()
	{
		global $smarty;
	
		$cms_titles = self::getCMStitles(0);
		$smarty->assign(array(
			'block' => 1,
			'cms_titles' => $cms_titles,
			'theme_dir' => _PS_THEME_DIR_
		));
		return $this->display(__FILE__, 'blockcms.tpl');
	}
	
	public function hookRightColumn()
	{
		global $smarty;

		$cms_titles = self::getCMStitles(1);
		$smarty->assign(array(
			'block' => 1,
			'cms_titles' => $cms_titles,
			'theme_dir' => _PS_THEME_DIR_
		));
		return $this->display(__FILE__, 'blockcms.tpl');
	}
	
	public function hookFooter()
	{
		global $smarty;
		
		if (Configuration::get('FOOTER_BLOCK_ACTIVATION'))
		{
			$cms_titles = self::getCMStitlesFooter();
			$smarty->assign(array(
				'block' => 0,
				'cmslinks' => $cms_titles,
				'theme_dir' => _PS_THEME_DIR_,
				'display_stores_footer' => Configuration::get('PS_STORES_DISPLAY_FOOTER')
			));
			return $this->display(__FILE__, 'blockcms.tpl');
		}
		return '';
	}
	
	public function hookHeader($params)
	{
		Tools::addCSS(($this->_path).'blockcms.css', 'all');
	}
	
	public function getL($key)
	{
		$trad = array(
			'ID' => $this->l('ID'),
			'Name' => $this->l('Name'),
			'There is nothing to display in this CMS category' => $this->l('There is nothing to display in this CMS category')
		);
		return $trad[$key];
	}
}
