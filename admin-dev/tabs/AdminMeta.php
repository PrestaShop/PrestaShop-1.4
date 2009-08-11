<?php

/**
  * Suppliers tab for admin panel, AdminSuppliers.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminMeta extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'meta';
	 	$this->className = 'Meta';
		$this->lang = true;
	 	$this->edit = true;
	 	$this->delete = true;

		$this->fieldsDisplay = array(
			'id_meta' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
			'page' => array('title' => $this->l('Page'), 'width' => 120, 'suffix' => '.php'),
			'title' => array('title' => $this->l('Title'), 'width' => 120),
		);
	
		parent::__construct();
	}
	
	public function displayForm()
	{
		global $currentIndex;
		
		$meta = $this->loadObject(true);
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
		$files = Meta::getPages(true, ($meta->page ? $meta->page : false));

		echo '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
		</script>
		<form action="'.$currentIndex.'&token='.$this->token.'&submitAdd'.$this->table.'=1" method="post" class="width3" style="width:650px;">
		'.($meta->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$meta->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/metatags.gif" />'.$this->l('Meta-Tags').'</legend>
				<label>'.$this->l('Page:').' </label>
				<div class="margin-form">';
				if (!sizeof($files))
					echo '<p>'.$this->l('There is no page available!').'</p>';
				else
				{
					echo '
					<select name="page">';
					foreach ($files as $file)
					{
						echo '<option value="'.$file.'"';
						echo $meta->page == $file? ' selected="selected"' : '' ;
						echo'>'.$file.'.php&nbsp;</option>';
					}
					echo '
					</select><sup> *</sup>
					<p style="clear: both;">'.$this->l('Name of the related page').'</p>';
				}
				echo '
				</div>
				<label>'.$this->l('Page\'s title:').' </label>
				<div class="margin-form">';
				foreach ($languages as $language)
					echo '
					<div id="title_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="title_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($meta, 'title', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
						<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
						<p style="clear: both;">'.$this->l('Title of this page').'</p>
					</div>';
				$this->displayFlags($languages, $defaultLanguage, 'title¤description¤keywords', 'title');
		echo '	</div>
				<div style="clear:both;">&nbsp;</div>
				<label>'.$this->l('Meta description:').' </label>
				<div class="margin-form">';
				foreach ($languages as $language)
					echo '
					<div id="description_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="50" type="text" name="description_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($meta, 'description', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
						<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
						<p style="clear: both;">'.$this->l('A short description').'</p>
					</div>';
				$this->displayFlags($languages, $defaultLanguage, 'title¤description¤keywords', 'description');
		echo '	</div>
				<div style="clear:both;">&nbsp;</div>
				<label>'.$this->l('Meta keywords:').' </label>
				<div class="margin-form">';
				foreach ($languages as $language)
					echo '
					<div id="keywords_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="50" type="text" name="keywords_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($meta, 'keywords', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
						<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
						<p style="clear: both;">'.$this->l('List of keywords').'</p>
					</div>';
				$this->displayFlags($languages, $defaultLanguage, 'title¤description¤keywords', 'keywords');
		echo '	</div>
				<div style="clear:both;">&nbsp;</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}
}

?>