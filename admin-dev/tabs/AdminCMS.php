<?php

/**
  * CMS tab for admin panel, AdminCMS.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminCMS extends AdminTab
{	
	private $_category;

	public function __construct()
	{
	 	$this->table = 'cms';
	 	$this->className = 'CMS';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->view = true;
	 	$this->delete = true;
		
		$this->fieldsDisplay = array(
			'id_cms' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
			'link_rewrite' => array('title' => $this->l('URL'), 'width' => 200),
			'meta_title' => array('title' => $this->l('Title'), 'width' => 300),
			'position' => array('title' => $this->l('Position'), 'width' => 40,'filter_key' => 'position', 'align' => 'center', 'position' => 'position')
			);
			
		$this->_category = AdminCMSContent::getCurrentCMSCategory();
		$this->_join = '
		LEFT JOIN `'._DB_PREFIX_.'cms_category` c ON (c.`id_cms_category` = a.`id_cms_category`)';
		$this->_select = 'position ';
		$this->_filter = 'AND c.id_cms_category = '.intval($this->_category->id);
		
		parent::__construct();
	}
	
	public function displayForm($isMainTab = true)
	{
		global $currentIndex, $cookie;
		parent::displayForm();
		
		$obj = $this->loadObject(true);
		$iso = Language::getIsoById(intval($cookie->id_lang));
		$divLangName = 'meta_title¤meta_description¤meta_keywords¤ccontent¤link_rewrite';

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.Tools::getAdminTokenLite('AdminCMSContent').'" method="post">
			'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/cms.gif" />'.$this->l('CMS page').'</legend>';
			
		// META TITLE
		echo '<label>'.$this->l('CMS Category:').' </label>
				<div class="margin-form">
					<select name="id_cms_category">';
		$categories = CMSCategory::getCategories(intval($cookie->id_lang), false);
		CMSCategory::recurseCMSCategory($categories, $categories[0][1], 1, $this->getFieldValue($obj, 'id_cms_category'));
		echo '
					</select>
				</div>
				<label>'.$this->l('Meta title').' </label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '	<div id="meta_title_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input size="40" type="text" name="meta_title_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'meta_title', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
					</div>';
		
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, $divLangName, 'meta_title');
		echo '	</div><div class="clear space">&nbsp;</div>';
		
		// META DESCRIPTION
		echo '	<label>'.$this->l('Meta description').' </label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '	<div id="meta_description_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input size="50" type="text" name="meta_description_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'meta_description', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
					</div>';
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, $divLangName, 'meta_description');
		echo '	</div><div class="clear space">&nbsp;</div>';
		
		// META KEYWORDS
		echo '	<label>'.$this->l('Meta keywords').' </label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '	<div id="meta_keywords_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input size="50" type="text" name="meta_keywords_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'meta_keywords', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
					</div>';
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, $divLangName, 'meta_keywords');
		echo '	</div><div class="clear space">&nbsp;</div>';
		
		// LINK REWRITE
		echo '	<label>'.$this->l('Friendly URL').' </label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '	<div id="link_rewrite_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input size="30" type="text" name="link_rewrite_'.$language['id_lang'].'" onkeyup="this.value = str2url(this.value); updateFriendlyURL();" value="'.htmlentities($this->getFieldValue($obj, 'link_rewrite', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
					</div>';
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, $divLangName, 'link_rewrite');
		echo '	</div><div class="clear space">&nbsp;</div>';
		
		// CONTENT
		echo '	<label>'.$this->l('Page content').' </label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '	<div id="ccontent_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').';float: left;">
						<textarea class="rte" cols="80" rows="30" id="content_'.$language['id_lang'].'" name="content_'.$language['id_lang'].'">'.htmlentities(stripslashes($this->getFieldValue($obj, 'content', $language['id_lang'])), ENT_COMPAT, 'UTF-8').'</textarea>
					</div>';
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, $divLangName, 'ccontent');
		echo '	</div><div class="clear space">&nbsp;</div>';
		
		// SUBMIT
		echo '	<div class="margin-form space">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
		
		// TinyMCE
		echo ' <script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
				<script type="text/javascript">
					tinyMCE.init({
						mode : "textareas",
						theme : "advanced",
						plugins : "safari,pagebreak,style,layer,table,advimage,advlink,inlinepopups,media,searchreplace,contextmenu,paste,directionality,fullscreen",
						// Theme options
						theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
						theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,,|,forecolor,backcolor",
						theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|,fullscreen",
						theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,pagebreak",
						theme_advanced_toolbar_location : "top",
						theme_advanced_toolbar_align : "left",
						theme_advanced_statusbar_location : "bottom",
						theme_advanced_resizing : false,
						content_css : "'.__PS_BASE_URI__.'themes/'._THEME_NAME_.'/css/global.css",
						document_base_url : "'.__PS_BASE_URI__.'",
						width: "600",
						height: "auto",
						font_size_style_values : "8pt, 10pt, 12pt, 14pt, 18pt, 24pt, 36pt",
						// Drop lists for link/image/media/template dialogs
						template_external_list_url : "lists/template_list.js",
						external_link_list_url : "lists/link_list.js",
						external_image_list_url : "lists/image_list.js",
						media_external_list_url : "lists/media_list.js",
						elements : "nourlconvert,ajaxfilemanager",
						file_browser_callback : "ajaxfilemanager",
						convert_urls : false,
						language : "'.(file_exists(_PS_ROOT_DIR_.'/js/tinymce/jscripts/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en').'"
						
					});
					function ajaxfilemanager(field_name, url, type, win) {
						var ajaxfilemanagerurl = "'.__PS_BASE_URI__.'admin-dev/ajaxfilemanager/ajaxfilemanager.php";
						switch (type) {
							case "image":
								break;
							case "media":
								break;
							case "flash": 
								break;
							case "file":
								break;
							default:
								return false;
					}
		            tinyMCE.activeEditor.windowManager.open({
		                url: "'.__PS_BASE_URI__.'admin-dev/ajaxfilemanager/ajaxfilemanager.php",
		                width: 782,
		                height: 440,
		                inline : "yes",
		                close_previous : "no"
		            },{
		                window : win,
		                input : field_name
		            });
            
		}
	</script>';
	}
	
	public function display($token = NULL)
	{
		global $currentIndex, $cookie;
		
		if (($id_cms_category = (int)Tools::getValue('id_cms_category')))
			$currentIndex .= '&id_cms_category='.$id_cms_category;
		$this->getList(intval($cookie->id_lang), !$cookie->__get($this->table.'Orderby') ? 'position' : NULL, !$cookie->__get($this->table.'Orderway') ? 'ASC' : NULL);
		//$this->getList(intval($cookie->id_lang));
		if (!$id_cms_category)
			$id_cms_category = 1;
		echo '<h3>'.(!$this->_listTotal ? ($this->l('No pages found')) : ($this->_listTotal.' '.($this->_listTotal > 1 ? $this->l('pages') : $this->l('page')))).' '.
		$this->l('in category').' "'.stripslashes(CMSCategory::hideCMSCategoryPosition($this->_category->getName())).'"</h3>';
		echo '<a href="'.$currentIndex.'&id_cms_category='.$id_cms_category.'&add'.$this->table.'&token='.Tools::getAdminTokenLite('AdminCMSContent').'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add a new page').'</a>
		<div style="margin:10px;">';
		$this->displayList($token);
		echo '</div>';
	}
	public function displayList($token = NULL)
	{
		global $currentIndex;
		
		/* Display list header (filtering, pagination and column names) */
		$this->displayListHeader($token);
		if (!sizeof($this->_list))
			echo '<tr><td class="center" colspan="'.(sizeof($this->fieldsDisplay) + 2).'">'.$this->l('No items found').'</td></tr>';

		/* Show the content of the table */
		$this->displayListContent($token);

		/* Close list table and submit button */
		$this->displayListFooter($token);
	}


	function postProcess()
	{
		global $cookie, $link, $currentIndex;
		if (Tools::isSubmit('viewcms') AND ($id_cms = intval(Tools::getValue('id_cms'))) AND $cms = new CMS($id_cms, intval($cookie->id_lang)) AND Validate::isLoadedObject($cms))
				Tools::redirectLink($link->getCMSLink($cms));
		elseif (Tools::isSubmit('deletecms'))
		{
			if (Tools::getValue('id_cms') == Configuration::get('PS_CONDITIONS_CMS_ID'))
			{
				Configuration::updateValue('PS_CONDITIONS', 0);
				Configuration::updateValue('PS_CONDITIONS_CMS_ID', 0);
			}
			$cms = new CMS(intval(Tools::getValue('id_cms')));
			$cms->cleanPositions($cms->id_cms_category);
		}
		return parent::postProcess();
	}
	
	public function displayListHeader($token = NULL)
	{
		global $currentIndex, $cookie;

		if (!isset($token) OR empty($token))
			$token = Tools::getAdminTokenLite('AdminCMSContent');

		/* Determine total page number */
		$totalPages = ceil($this->_listTotal / Tools::getValue('pagination', (isset($cookie->{$this->table.'_pagination'}) ? $cookie->{$this->table.'_pagination'} : $this->_pagination[0])));
		if (!$totalPages) $totalPages = 1;
		
		echo '<a name="'.$this->table.'">&nbsp;</a>';
		echo '<form method="post" action="'.$currentIndex;
		if (Tools::getIsset($this->table.'Orderby'))
			echo '&'.$this->table.'Orderby='.urlencode($this->_orderBy).'&'.$this->table.'Orderway='.urlencode(strtolower($this->_orderWay));
		echo '#'.$this->table.'" class="form">
		<input type="hidden" id="submitFilter'.$this->table.'" name="submitFilter'.$this->table.'" value="0">
		<table>
			<tr>
				<td style="vertical-align: bottom;">
					<span style="float: left;">';

		/* Determine current page number */
		$page = intval(Tools::getValue('submitFilter'.$this->table));
		if (!$page) $page = 1;
		if ($page > 1)
			echo '
						<input type="image" src="../img/admin/list-prev2.gif" onclick="getE(\'submitFilter'.$this->table.'\').value=1"/>
						&nbsp; <input type="image" src="../img/admin/list-prev.gif" onclick="getE(\'submitFilter'.$this->table.'\').value='.($page - 1).'"/> ';
		echo $this->l('Page').' <b>'.$page.'</b> / '.$totalPages;
		if ($page < $totalPages)
			echo '
						<input type="image" src="../img/admin/list-next.gif" onclick="getE(\'submitFilter'.$this->table.'\').value='.($page + 1).'"/>
						 &nbsp;<input type="image" src="../img/admin/list-next2.gif" onclick="getE(\'submitFilter'.$this->table.'\').value='.$totalPages.'"/>';
		echo '			| '.$this->l('Display').'
						<select name="pagination">';
		/* Choose number of results per page */
		$selectedPagination = Tools::getValue('pagination', (isset($cookie->{$this->table.'_pagination'}) ? $cookie->{$this->table.'_pagination'} : NULL));
		foreach ($this->_pagination AS $value)
			echo '<option value="'.intval($value).'"'.($selectedPagination == $value ? ' selected="selected"' : (($selectedPagination == NULL && $value == $this->_pagination[1]) ? ' selected="selected2"' : '')).'>'.intval($value).'</option>';
		echo '
						</select>
						/ '.intval($this->_listTotal).' '.$this->l('result(s)').'
					</span>
					<span style="float: right;">
						<input type="submit" name="submitReset'.$this->table.'" value="'.$this->l('Reset').'" class="button" />
						<input type="submit" id="submitFilterButton_'.$this->table.'" name="submitFilter" value="'.$this->l('Filter').'" class="button" />
					</span>
					<span class="clear"></span>
				</td>
			</tr>
			<tr>
				<td>';

		/* Display column names and arrows for ordering (ASC, DESC) */
		if ($this->identifier == 'id_cms' AND $this->_orderBy == 'position')
		{
			echo '
			<script type="text/javascript" src="../js/jquery/jquery.tablednd_0_5.js"></script>
			<script type="text/javascript">
				var token = \''.Tools::getAdminTokenLite('AdminCMSContent').'\';
				var come_from = \''.$this->table.'\';
				var alternate = \''.($this->_orderWay == 'DESC' ? '1' : '0' ).'\';
			</script>
			<script type="text/javascript" src="../js/admin-dnd.js"></script>
			';
		}
		echo '<table'.($this->identifier == 'id_cms' ? ' id="'.(($id_cms_category = intval(Tools::getValue('id_cms_category', '1'))) ? $id_cms_category : '').'"' : '' ).' class="table'.($this->identifier == 'id_cms' ? ' tableDnD' : '' ).'" cellpadding="0" cellspacing="0">
			<thead>
				<tr class="nodrag nodrop">
					<th>';
		if ($this->delete)
			echo '		<input type="checkbox" name="checkme" class="noborder" onclick="checkDelBoxes(this.form, \''.$this->table.'Box[]\', this.checked)" />';
		echo '		</th>';
		foreach ($this->fieldsDisplay AS $key => $params)
		{
			echo '	<th '.(isset($params['widthColumn']) ? 'style="width: '.$params['widthColumn'].'px"' : '').'>'.$params['title'];
			if (!isset($params['orderby']) OR $params['orderby'])
			{
				// Cleaning links
				if (Tools::getValue($this->table.'Orderby') && Tools::getValue($this->table.'Orderway')) 
					$currentIndex = preg_replace('/&'.$this->table.'Orderby=([a-z _]*)&'.$this->table.'Orderway=([a-z]*)/i', '', $currentIndex);
				echo '	<br />
						<a href="'.$currentIndex.'&'.$this->table.'Orderby='.urlencode($key).'&'.$this->table.'Orderway=desc&token='.$token.'"><img border="0" src="../img/admin/down'.((isset($this->_orderBy) AND ($key == $this->_orderBy) AND ($this->_orderWay == 'DESC')) ? '_d' : '').'.gif" /></a>
						<a href="'.$currentIndex.'&'.$this->table.'Orderby='.urlencode($key).'&'.$this->table.'Orderway=asc&token='.$token.'"><img border="0" src="../img/admin/up'.((isset($this->_orderBy) AND ($key == $this->_orderBy) AND ($this->_orderWay == 'ASC')) ? '_d' : '').'.gif" /></a>';
			}
			echo '	</th>';
		}

		/* Check if object can be modified, deleted or detailed */
		if ($this->edit OR $this->delete OR ($this->view AND $this->view !== 'noActionColumn'))
			echo '	<th style="width: 52px">'.$this->l('Actions').'</th>';
		echo '	</tr>
				<tr class="nodrag nodrop" style="height: 35px;">
					<td class="center">';
		if ($this->delete)
			echo '		--';
		echo '		</td>';

		/* Javascript hack in order to catch ENTER keypress event */
		$keyPress = 'onkeypress="formSubmit(event, \'submitFilterButton_'.$this->table.'\');"';

		/* Filters (input, select, date or bool) */
		foreach ($this->fieldsDisplay AS $key => $params)
		{
			$width = (isset($params['width']) ? ' style="width: '.intval($params['width']).'px;"' : '');
			echo '<td'.(isset($params['align']) ? ' class="'.$params['align'].'"' : '').'>';
			if (!isset($params['type']))
				$params['type'] = 'text';

			$value = Tools::getValue('conf') ? NULL : Tools::getValue($this->table.'Filter_'.(array_key_exists('filter_key', $params) ? $params['filter_key'] : $key));
			if (isset($params['search']) AND !$params['search'])
			{
				echo '--</td>';
				continue;
			}
			switch ($params['type'])
			{
				case 'bool':
					echo '
					<select name="'.$this->table.'Filter_'.$key.'">
						<option value="">--</option>
						<option value="1"'.($value == 1 ? ' selected="selected"' : '').'>'.$this->l('Yes').'</option>
						<option value="0"'.(($value == 0 AND $value != '') ? ' selected="selected"' : '').'>'.$this->l('No').'</option>
					</select>';
					break;

				case 'date':
				case 'datetime':
					if (is_string($value))
						$value = unserialize($value);
					$name = $this->table.'Filter_'.(isset($params['filter_key']) ? $params['filter_key'] : $key);
					$nameId = str_replace('!', '__', $name);
					includeDatepicker(array($nameId.'_0', $nameId.'_1'));
					echo $this->l('From').' <input type="text" id="'.$nameId.'_0" name="'.$name.'[0]" value="'.(isset($value[0]) ? $value[0] : '').'"'.$width.' '.$keyPress.' /><br />
					'.$this->l('To').' <input type="text" id="'.$nameId.'_1" name="'.$name.'[1]" value="'.(isset($value[1]) ? $value[1] : '').'"'.$width.' '.$keyPress.' />';
					break;

				case 'select':

					if (isset($params['filter_key']))
					{
						echo '<select onchange="$(\'#submitFilter'.$this->table.'\').focus();$(\'#submitFilter'.$this->table.'\').click();" name="'.$this->table.'Filter_'.$params['filter_key'].'" '.(isset($params['width']) ? 'style="width: '.$params['width'].'px"' : '').'>
								<option value=""'.(($value == 0 AND $value != '') ? ' selected="selected"' : '').'>--</option>';
						if (isset($params['select']) AND is_array($params['select']))
							foreach ($params['select'] AS $optionValue => $optionDisplay)
							{
								echo '<option value="'.$optionValue.'"'.((isset($_POST[$this->table.'Filter_'.$params['filter_key']]) AND Tools::getValue($this->table.'Filter_'.$params['filter_key']) == $optionValue AND Tools::getValue($this->table.'Filter_'.$params['filter_key']) != '') ? ' selected="selected"' : '').'>'.$optionDisplay.'</option>';
								}
						echo '</select>';
						break;
					}

				case 'text':
				default:
					echo '<input type="text" name="'.$this->table.'Filter_'.(isset($params['filter_key']) ? $params['filter_key'] : $key).'" value="'.htmlentities($value, ENT_COMPAT, 'UTF-8').'"'.$width.' '.$keyPress.' />';
			}
			echo '</td>';
		}

		if ($this->edit OR $this->delete OR ($this->view AND $this->view !== 'noActionColumn'))
			echo '<td class="center">--</td>';

		echo '</tr>
			</thead>';
	}

	public function displayListContent($token = NULL)
	{
		/* Display results in a table
		 *
		 * align  : determine value alignment
		 * prefix : displayed before value
		 * suffix : displayed after value
		 * image  : object image
		 * icon   : icon determined by values
		 * active : allow to toggle status
		 */
		global $currentIndex, $cookie;
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

		$_cacheLang['View'] = $this->l('View');
		$_cacheLang['Edit'] = $this->l('Edit');
		$_cacheLang['Delete'] = $this->l('Delete', __CLASS__, TRUE, FALSE);
		$_cacheLang['DeleteItem'] = $this->l('Delete item #', __CLASS__, TRUE, FALSE);		
		$_cacheLang['Duplicate'] = $this->l('Duplicate');
		$_cacheLang['Copy images too?'] = $this->l('Copy images too?', __CLASS__, TRUE, FALSE);
		
		$irow = 0;
		if ($this->_list AND isset($this->fieldsDisplay['position']))
		{
			$positions = array_map(create_function('$elem', 'return intval($elem[\'position\']);'), $this->_list);
			sort($positions);
		}
		if ($this->_list)
		{
			foreach ($this->_list AS $i => $tr)
			{
				$id = $tr[$this->identifier];
				echo '<tr'.($this->identifier == 'id_cms' ? ' id="tr_'.(($id_cms_category = intval(Tools::getValue('id_cms_category', 1))) ? $id_cms_category : '').'_'.$id.'_'.$tr['position'].'"' : '').($irow++ % 2 ? ' class="alt_row"' : '').' '.((isset($tr['color']) AND $this->colorOnBackground) ? 'style="background-color: '.$tr['color'].'"' : '').'>
							<td class="center">';
				if ($this->delete AND (!isset($this->_listSkipDelete) OR !in_array($id, $this->_listSkipDelete)))
					echo '<input type="checkbox" name="'.$this->table.'Box[]" value="'.$id.'" class="noborder" />';
				echo '</td>';
				foreach ($this->fieldsDisplay AS $key => $params)
				{
					$tmp = explode('!', $key);
					$key = isset($tmp[1]) ? $tmp[1] : $tmp[0];
					echo '
					<td '.(isset($params['position']) ? ' id="td_'.(isset($id_cms_category) AND $id_cms_category ? $id_cms_category : 0).'_'.$id.'"' : '').' class="pointer'.((isset($params['position']) AND $this->_orderBy == 'position')? ' dragHandle' : ''). (isset($params['align']) ? ' '.$params['align'] : '').'" ';
					if (!isset($params['position']))
						echo ' onclick="document.location = \''.$currentIndex.'&'.$this->identifier.'='.$id.($this->view? '&view' : '&update').$this->table.'&token='.Tools::getAdminTokenLite('AdminCMSContent').'\'">'.(isset($params['prefix']) ? $params['prefix'] : '');
					else
						echo '>';
					if (isset($params['active']) AND isset($tr[$key]))
						echo '<a href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&'.$params['active'].
						((($id_cms_category = intval(Tools::getValue('id_cms_category')))) ? '&id_cms_category='.$id_cms_category : '').'&token='.Tools::getAdminTokenLite('AdminCMSContent').'">
						<img src="../img/admin/'.($tr[$key] ? 'enabled.gif' : 'disabled.gif').'"
						alt="'.($tr[$key] ? $this->l('Enabled') : $this->l('Disabled')).'" title="'.($tr[$key] ? $this->l('Enabled') : $this->l('Disabled')).'" /></a>';
					elseif (isset($params['activeVisu']) AND isset($tr[$key]))
						echo '<img src="../img/admin/'.($tr[$key] ? 'enabled.gif' : 'disabled.gif').'"
						alt="'.($tr[$key] ? $this->l('Enabled') : $this->l('Disabled')).'" title="'.($tr[$key] ? $this->l('Enabled') : $this->l('Disabled')).'" />';
					elseif (isset($params['position']))
					{
						if ($this->_orderBy == 'position')
						{
							echo '<a'.(!($tr[$key] != $positions[sizeof($positions) - 1]) ? ' style="display: none;"' : '').' href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&way=1&position='.intval($tr['position'] + 1).
									'&id_cms_category='.intval(Tools::getValue('id_cms_category', 1)).'&token='.Tools::getAdminTokenLite('AdminCMSContent').'">
									<img src="../img/admin/'.($this->_orderWay == 'ASC' ? 'down' : 'up').'.gif"
									alt="'.$this->l('Down').'" title="'.$this->l('Down').'" /></a>';
							echo '<a'.(!($tr[$key] != $positions[0]) ? ' style="display: none;"' : '').' href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&way=0&position='.intval($tr['position'] - 1).
									'&id_cms_category='.intval(Tools::getValue('id_cms_category', 1)).'&token='.Tools::getAdminTokenLite('AdminCMSContent').'">
									<img src="../img/admin/'.($this->_orderWay == 'ASC' ? 'up' : 'down').'.gif"
									alt="'.$this->l('Up').'" title="'.$this->l('Up').'" /></a>';
									
						}
						else
							echo intval($tr[$key] + 1);
					}
					elseif (isset($params['image']))
					{
						$image_id = isset($params['image_id']) ? $tr[$params['image_id']] : $id;
						echo cacheImage(_PS_IMG_DIR_.$params['image'].'/'.$image_id.(isset($tr['id_image']) ? '-'.intval($tr['id_image']) : '').'.'.$this->imageType, $this->table.'_mini_'.$image_id.'.'.$this->imageType, 45, $this->imageType);
					}
					elseif (isset($params['icon']) AND (isset($params['icon'][$tr[$key]]) OR isset($params['icon']['default'])))
						echo '<img src="../img/admin/'.(isset($params['icon'][$tr[$key]]) ? $params['icon'][$tr[$key]] : $params['icon']['default'].'" alt="'.$tr[$key]).'" title="'.$tr[$key].'" />';
                    elseif (isset($params['price']))
						echo Tools::displayPrice($tr[$key], (isset($params['currency']) ? Currency::getCurrencyInstance(intval($tr['id_currency'])) : $currency), false, false);
					elseif (isset($params['float']))
						echo rtrim(rtrim($tr[$key], '0'), '.');
					elseif (isset($params['type']) AND $params['type'] == 'date')
						echo Tools::displayDate($tr[$key], $cookie->id_lang);
					elseif (isset($params['type']) AND $params['type'] == 'datetime')
						echo Tools::displayDate($tr[$key], $cookie->id_lang, true);
					elseif (isset($tr[$key]))
					{
						$echo = ($key == 'price' ? round($tr[$key], 2) : isset($params['maxlength']) ? Tools::substr($tr[$key], 0, $params['maxlength']).'...' : $tr[$key]);
						echo isset($params['callback']) ? call_user_func_array(array($this->className, $params['callback']), array($echo, $tr)) : $echo;
					}
					else
						echo '--';

					echo (isset($params['suffix']) ? $params['suffix'] : '').
					'</td>';
				}

				if ($this->edit OR $this->delete OR ($this->view AND $this->view !== 'noActionColumn'))
				{
					echo '<td class="center" style="white-space: nowrap;">';
					if ($this->view)
						echo '
						<a href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&view'.$this->table.'&token='.Tools::getAdminTokenLite('AdminCMSContent').'">
						<img src="../img/admin/details.gif" alt="'.$_cacheLang['View'].'" title="'.$_cacheLang['View'].'" /></a>';
					if ($this->edit)
						echo '
						<a href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&update'.$this->table.'&token='.Tools::getAdminTokenLite('AdminCMSContent').'">
						<img src="../img/admin/edit.gif" alt="" title="'.$_cacheLang['Edit'].'" /></a>';
					if ($this->delete AND (!isset($this->_listSkipDelete) OR !in_array($id, $this->_listSkipDelete)))
						echo '
						<a href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&delete'.$this->table.'&token='.Tools::getAdminTokenLite('AdminCMSContent').'" onclick="return confirm(\''.$_cacheLang['DeleteItem'].$id.' ?\');">
						<img src="../img/admin/delete.gif" alt="'.$_cacheLang['Delete'].'" title="'.$_cacheLang['Delete'].'" /></a>';
					$duplicate = $currentIndex.'&'.$this->identifier.'='.$id.'&duplicate'.$this->table;
					if ($this->duplicate)
						echo '
						<a class="pointer" onclick="if (confirm(\''.$_cacheLang['Copy images too?'].'\')) document.location = \''.$duplicate.'&token='.Tools::getAdminTokenLite('AdminCMSContent').'\'; else document.location = \''.$duplicate.'&noimage=1&token='.($token ? $token : Tools::getAdminTokenLite('AdminCMSContent')).'\';">
						<img src="../img/admin/add.gif" alt="'.$_cacheLang['Duplicate'].'" title="'.$_cacheLang['Duplicate'].'" /></a>';
					echo '</td>';
				}
				echo '</tr>';
			}			
		}
	}
	
	
}

?>
