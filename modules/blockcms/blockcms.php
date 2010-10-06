<?php

class BlockCms extends Module
{
	private $_html;

	public function __construct()
	{
		$this->name = 'blockcms';
		$this->tab = 'Blocks';
		$this->version = 1.0;

		parent::__construct();

		$this->displayName = $this->l('CMS Block');
		$this->description = $this->l('Adds a block with several CMS links');
		$this->secure_key = Tools::encrypt($this->name);
	}

	public function install()
	{
		if (!parent::install() OR
		!$this->registerHook('leftColumn') OR
		!$this->registerHook('rightColumn') OR
		!Db::getInstance()->Execute('
		CREATE TABLE `'._DB_PREFIX_.'cms_block`(
		`id_block_cms` int(10) unsigned NOT NULL auto_increment,
		`id_cms_category` int(10) unsigned NOT NULL,
		`name` varchar(40) NOT NULL, 
		`location` tinyint(1) unsigned NOT NULL,
		`position` int(10) unsigned NOT NULL default \'0\',
		PRIMARY KEY (`id_block_cms`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8') OR
		!Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'cms_block` (`id_cms_category`, `location`, `position`) VALUES(1, 0, 0)') OR
		!Db::getInstance()->Execute('
		CREATE TABLE `'._DB_PREFIX_.'cms_block_page`(
		`id_block_cms_page` int(10) unsigned NOT NULL auto_increment,
		`id_block_cms` int(10) unsigned NOT NULL,
		`id_cms` int(10) unsigned NOT NULL,
		`is_category` tinyint(1) unsigned NOT NULL,
		PRIMARY KEY (`id_block_cms_page`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8'))
			return false;
		$default_cms = Db::getInstance()->ExecuteS('SELECT `id_cms` FROM `'._DB_PREFIX_.'cms` WHERE `id_cms_category` = 1');
		$cms_query = 'INSERT INTO `'._DB_PREFIX_.'cms_block_page` (`id_block_cms`, `id_cms`, `is_category`) VALUES';
		foreach ($default_cms as $cms)
			$cms_query .= '(1, '.intval($cms['id_cms']).', 0),';
		$cms_query = rtrim($cms_query, ',');
		if (!Db::getInstance()->Execute($cms_query))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall())
			return false;
		Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'cms_block`');
		Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'cms_block_page`');
		return true;
	}

	private function getBlockCMS($id_block_cms)
	{
		$cms_block = Db::getInstance()->ExecuteS('
		SELECT `id_cms_category`, `location`, `name` FROM `'._DB_PREFIX_.'cms_block`
		WHERE `id_block_cms` = '.intval($id_block_cms));
		
		return $cms_block;
	}
	
	private function getBlocksCMS($location)
	{
		global $cookie;
		
		$cms_block = Db::getInstance()->ExecuteS('
		SELECT bc.`id_block_cms`, bc.`name` AS block_name, ccl.`name` AS category_name, bc.`position`, bc.`id_cms_category` FROM `'._DB_PREFIX_.'cms_block` bc
		INNER JOIN `'._DB_PREFIX_.'cms_category_lang` ccl ON (bc.`id_cms_category` = ccl.`id_cms_category`)
		WHERE ccl.`id_lang` = '.intval($cookie->id_lang).'
		AND bc.`location` = '.intval($location).'
		ORDER BY bc.`position`');
		
		return $cms_block;
	}

	static public function getCMStitles($location)
	{
		global $cookie;
		$cms_categories = Db::getInstance()->ExecuteS('
		SELECT bc.`id_block_cms`, bc.`id_cms_category`, ccl.`link_rewrite`, ccl.`name` AS category_name, bc.`name` AS block_name FROM `'._DB_PREFIX_.'cms_block` bc
		INNER JOIN `'._DB_PREFIX_.'cms_category_lang` ccl ON (bc.`id_cms_category` = ccl.`id_cms_category`)
		WHERE bc.`location` = '.intval($location).'
		AND ccl.`id_lang` = '.intval($cookie->id_lang).'
		ORDER BY `position`');
		$display_cms = array();
		$link = new Link();
		foreach ($cms_categories as $cms_category)
		{
			$key = $cms_category['id_block_cms'];
			$display_cms[$key]['cms'] = Db::getInstance()->ExecuteS('
			SELECT cl.`id_cms`, cl.`meta_title`, cl.`link_rewrite` FROM `'._DB_PREFIX_.'cms_block_page` bcp 
			INNER JOIN `'._DB_PREFIX_.'cms_lang` cl ON (bcp.`id_cms` = cl.`id_cms`)
			WHERE bcp.`id_block_cms` = '.intval($cms_category['id_block_cms']).'
			AND cl.`id_lang` = '.intval($cookie->id_lang).'
			AND bcp.`is_category` = 0');
			$links = array();
			if (sizeof($display_cms[$key]['cms']))
				foreach ($display_cms[$key]['cms'] as $row)
				{
					$row['link'] = $link->getCMSLink(intval($row['id_cms']), $row['link_rewrite']);
					$links[] = $row;
				}
			$display_cms[$key]['cms'] = $links;
			$display_cms[$key]['categories'] = Db::getInstance()->ExecuteS('
			SELECT bcp.`id_cms`, cl.`name`, cl.`link_rewrite` FROM `'._DB_PREFIX_.'cms_block_page` bcp 
			INNER JOIN `'._DB_PREFIX_.'cms_category_lang` cl ON (bcp.`id_cms` = cl.`id_cms_category`)
			WHERE bcp.`id_block_cms` = '.intval($cms_category['id_block_cms']).'
			AND cl.`id_lang` = '.intval($cookie->id_lang).'
			AND bcp.`is_category` = 1');
			$links = array();
			if (sizeof($display_cms[$key]['categories']))
				foreach ($display_cms[$key]['categories'] as $row)
				{
					$row['link'] = $link->getCMSCategoryLink(intval($row['id_cms']), $row['link_rewrite']);
					$links[] = $row;
				}
			$display_cms[$key]['categories'] = $links;
			$display_cms[$key]['name'] = $cms_category['block_name'];
			$display_cms[$key]['category_link'] = $link->getCMSCategoryLink(intval($cms_category['id_cms_category']), $cms_category['link_rewrite']);
			$display_cms[$key]['category_name'] = $cms_category['category_name'];
		}
		return $display_cms;
	}

	private function _displayForm()
	{
		global $currentIndex;
		
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
							<a'.(($cms_block['position'] == (sizeof($cms_blocks_left) - 1) OR sizeof($cms_blocks_left) == 1) ? ' style="display: none;"' : '').' href="'.$currentIndex.'&configure=blockcms&id_block_cms='.$cms_block['id_block_cms'].'&way=1&position='.intval($cms_block['position'] + 1).'&location=0&token='.Tools::getAdminTokenLite('AdminModules').'">
							<img src="../img/admin/down.gif" alt="'.$this->l('Down').'" title="'.$this->l('Down').'" /></a>
							<a'.($cms_block['position'] == 0 ? ' style="display: none;"' : '').' href="'.$currentIndex.'&configure=blockcms&id_block_cms='.$cms_block['id_block_cms'].'&way=0&position='.intval($cms_block['position'] - 1).'&location=0&token='.Tools::getAdminTokenLite('AdminModules').'">
							<img src="../img/admin/up.gif" alt="'.$this->l('Up').'" title="'.$this->l('Up').'" /></a>
						</td>
						<td width="10%" class="center">
							<a href="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&editBlockCMS&id_block_cms='.intval($cms_block['id_block_cms']).'" title="'.$this->l('Edit').'"><img src="'._PS_ADMIN_IMG_.'edit.gif" alt="" /></a> 
							<a href="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&deleteBlockCMS&id_block_cms='.intval($cms_block['id_block_cms']).'" title="'.$this->l('Delete').'"><img src="'._PS_ADMIN_IMG_.'delete.gif" alt="" /></a>
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
							<a'.(($cms_block['position'] == (sizeof($cms_blocks_right) - 1) OR sizeof($cms_blocks_right) == 1) ? ' style="display: none;"' : '').' href="'.$currentIndex.'&configure=blockcms&id_block_cms='.$cms_block['id_block_cms'].'&way=1&position='.intval($cms_block['position'] + 1).'&location=1&token='.Tools::getAdminTokenLite('AdminModules').'">
							<img src="../img/admin/down.gif" alt="'.$this->l('Down').'" title="'.$this->l('Down').'" /></a>
							<a'.($cms_block['position'] == 0 ? ' style="display: none;"' : '').' href="'.$currentIndex.'&configure=blockcms&id_block_cms='.$cms_block['id_block_cms'].'&way=0&position='.intval($cms_block['position'] - 1).'&location=1&token='.Tools::getAdminTokenLite('AdminModules').'">
							<img src="../img/admin/up.gif" alt="'.$this->l('Up').'" title="'.$this->l('Up').'" /></a>
						</td>
						<td width="10%" class="center">
							<a href="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&editBlockCMS&id_block_cms='.intval($cms_block['id_block_cms']).'" title="'.$this->l('Edit').'"><img src="'._PS_ADMIN_IMG_.'edit.gif" alt="" /></a> 
							<a href="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&deleteBlockCMS&id_block_cms='.intval($cms_block['id_block_cms']).'" title="'.$this->l('Delete').'"><img src="'._PS_ADMIN_IMG_.'delete.gif" alt="" /></a>
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
		</fieldset>';
	}

	private function _displayAddForm()
	{
		global $currentIndex, $cookie;

		$block_cms = NULL;
		if (Tools::isSubmit('editBlockCMS') AND intval(Tools::getValue('id_block_cms')))
			$block_cms = $this->getBlockCMS(intval(Tools::getValue('id_block_cms')));

		$this->_html .= '
		<script type="text/javascript" src="'._PS_BASE_URL_.__PS_BASE_URI__.'modules/'.$this->name.'/'.$this->name.'.js"></script>
		<form method="POST" action="'.$_SERVER['REQUEST_URI'].'">
		';
		if (Tools::getValue('id_block_cms'))
			$this->_html .= '<input type="hidden" name="id_block_cms" value="'.intval(Tools::getValue('id_block_cms')).'" id="id_block_cms" />';
		$this->_html .= '
		<fieldset>';
		
		if (Tools::isSubmit('addBlockCMS'))
			$this->_html .= '<legend><img src="'._PS_ADMIN_IMG_.'add.gif" alt="" /> '.$this->l('New CMS block').'</legend>';
		elseif (Tools::isSubmit('editBlockCMS'))
			$this->_html .= '<legend><img src="'._PS_BASE_URL_.__PS_BASE_URI__.'modules/'.$this->name.'/logo.gif" alt="" /> '.$this->l('Edit CMS block').'</legend>';
		
		$this->_html .= '
			<label for="block_name">'.$this->l('Block\'s name:').'</label>
			<div class="margin-form">
				<input id="block_name" name="block_name" type="text" '.(($block_cms AND $block_cms[0]['name']) ? 'value="'.$block_cms[0]['name'].'"' : '').'/>
			</div><br />
			<label for="id_category">'.$this->l('Choose a CMS category:').'</label>
			<div class="margin-form">
				<select name="id_category" id="id_category" onchange="CMSCategory_js($(this).val(), \''.$this->secure_key.'\')">';
		$categories = CMSCategory::getCategories(intval($cookie->id_lang), false);
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
			</div>
			<div id="cms_subcategories"></div>
			<p class="center"><input type="submit" class="button" name="submitBlockCMS" value="'.$this->l('Save').'" /></p>
			<p class="center"><a href="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Cancel').'</a></p>
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
			$cmsBoxes = Tools::getValue('cmsBox');
			if (!Validate::isInt(Tools::getValue('block_location')) OR (Tools::getValue('block_location') != 0 AND Tools::getValue('block_location') != 1))
				$errors[] = $this->l('Invalid block location');
			if (!is_array($cmsBoxes))
				$errors[] = $this->l('You must choose at least one page or subcategory to create a CMS block');
			else
				foreach ($cmsBoxes as $cmsBox)
					if (!preg_match("#^[01]_[0-9]+$#", $cmsBox))
						$errors[] = $this->l('Invalid CMS page or category');
			if (strlen(Tools::getValue('block_name')) > 40)
				$errors[] = $this->l('Block name is too long');
		}
		elseif (Tools::isSubmit('deleteBlockCMS') AND !Validate::isInt(Tools::getValue('id_block_cms')))
			$errors[] = $this->l('Invalid id_block_cms');
		
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
			SET `position` = '.(intval(Tools::getValue('position')) + 1).'
			WHERE `position` = '.(intval(Tools::getValue('position'))).'
			AND `location` = '.intval(Tools::getValue('location'))))
				Db::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.'cms_block`
				SET `position` = '.(intval(Tools::getValue('position'))).'
				WHERE `id_block_cms` = '.intval(Tools::getValue('id_block_cms')));
		}
		elseif (Tools::getValue('way') == 1)
		{
			if(Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'cms_block`
			SET `position` = '.(intval(Tools::getValue('position')) - 1).'
			WHERE `position` = '.(intval(Tools::getValue('position'))).'
			AND `location` = '.intval(Tools::getValue('location'))))
				Db::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.'cms_block`
				SET `position` = '.(intval(Tools::getValue('position'))).'
				WHERE `id_block_cms` = '.intval(Tools::getValue('id_block_cms')));
		}
		Tools::redirectAdmin($currentIndex.'index.php?tab=AdminModules&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
	}
	
	private function _postProcess()
	{
		global $currentIndex;
	
		if (Tools::isSubmit('submitBlockCMS'))
		{
			$position = Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'cms_block` WHERE location = '.intval(Tools::getValue('block_location')));
			if (Tools::isSubmit('addBlockCMS'))
			{
				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'cms_block` (`id_cms_category`, `location`, `name`, `position`) VALUES('.intval(Tools::getValue('id_category')).', '.intval(Tools::getValue('block_location')).', "'.pSQL(Tools::getValue('block_name')).'", '.$position.')');
				$id_block_cms = Db::getInstance()->Insert_ID();
			}
			elseif (Tools::isSubmit('editBlockCMS'))
			{
				$id_block_cms = Tools::getvalue('id_block_cms');
				$old_block = Db::getInstance()->ExecuteS('SELECT `location`, `position` FROM `'._DB_PREFIX_.'cms_block` WHERE `id_block_cms` = '.intval($id_block_cms));
				$location_change = ($old_block[0]['location'] != intval(Tools::getvalue('block_location')));
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'cms_block_page` WHERE `id_block_cms` = '.intval($id_block_cms));
				if ($location_change == true)
					Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'cms_block` SET `position` = (`position` - 1) WHERE `position` > '.$old_block[0]['position'].' AND `location` = '.$old_block[0]['location']);
				Db::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.'cms_block`
				SET `location` = '.intval(Tools::getvalue('block_location')).',
				`id_cms_category` = '.intval(Tools::getvalue('id_category')).',
				`name` = "'.pSQL(Tools::getValue('block_name')).'"
				'.($location_change == true ? ', `position` = '.$position : '').'
				WHERE `id_block_cms` = '.intval($id_block_cms));
			}
			$cmsBoxes = Tools::getValue('cmsBox');
			if (sizeof($cmsBoxes))
				foreach ($cmsBoxes as $cmsBox)
				{
					$cms_properties = explode('_', $cmsBox);
					Db::getInstance()->Execute('
					INSERT INTO `'._DB_PREFIX_.'cms_block_page` (`id_block_cms`, `id_cms`, `is_category`) 
					VALUES('.intval($id_block_cms).', '.intval($cms_properties[1]).', '.intval($cms_properties[0]).')');
				}
			if (Tools::isSubmit('addBlockCMS'))
				Tools::redirectAdmin($currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&addBlockCMSConfirmation');
			elseif (Tools::isSubmit('editBlockCMS'))
				Tools::redirectAdmin($currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&editBlockCMSConfirmation');
		}
		elseif (Tools::isSubmit('deleteBlockCMS') AND Tools::getValue('id_block_cms'))
		{
			$old_block = Db::getInstance()->ExecuteS('SELECT `location`, `position` FROM `'._DB_PREFIX_.'cms_block` WHERE `id_block_cms` = '.Tools::getvalue('id_block_cms'));
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'cms_block` SET `position` = (`position` - 1) WHERE `position` > '.$old_block[0]['position'].' AND `location` = '.$old_block[0]['location']);
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'cms_block` WHERE `id_block_cms` = '.intval(Tools::getValue('id_block_cms')));
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'cms_block_page` WHERE `id_block_cms` = '.intval(Tools::getValue('id_block_cms')));
			$this->_html .= $this->displayConfirmation($this->l('Deletion succesfully done'));
		}
		elseif (Tools::isSubmit('addBlockCMSConfirmation'))
			$this->_html = $this->displayConfirmation($this->l('Block CMS succesfully added'));
		elseif (Tools::isSubmit('editBlockCMSConfirmation'))
			$this->_html = $this->displayConfirmation($this->l('Block CMS succesfully edited'));
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
			'cms_titles' => $cms_titles,
			'theme_dir' => _PS_THEME_DIR_
		));
		return $this->display(__FILE__, 'blockcms.tpl');
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
