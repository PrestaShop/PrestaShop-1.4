<?php

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminCMS extends AdminTab
{
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
			'meta_title' => array('title' => $this->l('Title'), 'width' => 300)
		);
		
		parent::__construct();
	}
	
	public function displayForm()
	{
		global $currentIndex, $cookie;
		
		$obj = $this->loadObject(true);
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$iso = Language::getIsoById(intval($cookie->id_lang));
		$languages = Language::getLanguages();
		$divLangName = 'meta_title¤meta_description¤meta_keywords¤ccontent¤link_rewrite';

		echo '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
		</script>
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post">
			'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/cms.gif" />'.$this->l('CMS').'</legend>';
			
		// META TITLE
		echo '	<label>'.$this->l('Meta title').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '	<div id="meta_title_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="40" type="text" name="meta_title_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'meta_title', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
					</div>';
		$this->displayFlags($languages, $defaultLanguage, $divLangName, 'meta_title');
		echo '	</div><div class="clear space">&nbsp;</div>';
		
		// META DESCRIPTION
		echo '	<label>'.$this->l('Meta description').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '	<div id="meta_description_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="50" type="text" name="meta_description_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'meta_description', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
					</div>';
		$this->displayFlags($languages, $defaultLanguage, $divLangName, 'meta_description');
		echo '	</div><div class="clear space">&nbsp;</div>';
		
		// META KEYWORDS
		echo '	<label>'.$this->l('Meta keywords').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '	<div id="meta_keywords_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="50" type="text" name="meta_keywords_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'meta_keywords', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
					</div>';
		$this->displayFlags($languages, $defaultLanguage, $divLangName, 'meta_keywords');
		echo '	</div><div class="clear space">&nbsp;</div>';
		
		// LINK REWRITE
		echo '	<label>'.$this->l('Friendly URL').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '	<div id="link_rewrite_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="30" type="text" name="link_rewrite_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'link_rewrite', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
					</div>';
		$this->displayFlags($languages, $defaultLanguage, $divLangName, 'link_rewrite');
		echo '	</div><div class="clear space">&nbsp;</div>';
		
		// CONTENT
		echo '	<label>'.$this->l('Page content').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '	<div id="ccontent_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">
						<textarea cols="80" rows="30" id="content_'.$language['id_lang'].'" name="content_'.$language['id_lang'].'">'.htmlentities(stripslashes($this->getFieldValue($obj, 'content', $language['id_lang'])), ENT_COMPAT, 'UTF-8').'</textarea>
					</div>';
		$this->displayFlags($languages, $defaultLanguage, $divLangName, 'ccontent');
		echo '	</div><div class="clear space">&nbsp;</div>';
		
		// SUBMIT
		echo '	<div class="margin-form space">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
		
		// TinyMCE
		echo '
			<script type="text/javascript" src="../js/tinymce/jscripts/tiny_mce/tiny_mce_gzip.js"></script>
			<script type="text/javascript">
				tinyMCE_GZ.init({
					plugins : "contextmenu, directionality, media, paste, preview, safari",
					themes : "advanced",
					languages : "'.((!file_exists(PS_ADMIN_DIR.'/../js/tinymce/jscripts/tiny_mce/langs/'.$iso.'.js')) ? 'en' : $iso).'",
					disk_cache : false,
					debug : false
				});
			</script>
			<script type="text/javascript">
				tinyMCE.init({
					mode : "textareas",
					plugins : "contextmenu, directionality, media, paste, preview, safari",
					theme : "advanced",
					language : "'.((!file_exists(PS_ADMIN_DIR.'/../js/tinymce/jscripts/tiny_mce/langs/'.$iso.'.js')) ? 'en' : $iso).'",
					elements : "nourlconvert",
					convert_urls : false,
					theme_advanced_buttons1 : "bold, italic, underline, fontselect, fontsizeselect",
					theme_advanced_buttons2 : "forecolor, backcolor, separator, justifyleft, justifycenter, justifyright, justifyfull, separator, bullist, numlist, separator, undo, redo, separator, link, unlink, separator, code",
					theme_advanced_buttons3 : "",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					theme_advanced_buttons3_add : "ltr,rtl,pastetext,pasteword,selectall",
					theme_advanced_buttons1_add : "media,preview",
					paste_create_paragraphs : false,
					paste_create_linebreaks : false,
					paste_use_dialog : true,
					paste_auto_cleanup_on_paste : true,
					paste_convert_middot_lists : false,
					paste_unindented_list_class : "unindentedList",
					paste_convert_headers_to_strong : true,
					plugin_preview_width : "500",
					plugin_preview_height : "600",
					extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"
				});';
		echo '</script>';
	}
	
	function postProcess()
	{
		global $cookie, $link;
		if (Tools::isSubmit('viewcms') AND ($id_cms = intval(Tools::getValue('id_cms'))) AND $cms = new CMS($id_cms, intval($cookie->id_lang)) AND Validate::isLoadedObject($cms))
			Tools::redirectLink($link->getCMSLink($cms));
		return parent::postProcess();
	}	
}

?>
