<?php

class Editorial extends Module
{
	/** @var max image size */
	protected $maxImageSize = 307200;

	function __construct()
	{
		$this->name = 'editorial';
		$this->tab = 'Tools';
		$this->version = '1.4';
		
		/* The parent construct is required for translations */
		parent::__construct();
		
		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Home text editor');
		$this->description = $this->l('A text editor module for your homepage');
	}

	function install()
	{
		if (!parent::install())
			return false;
		// Trunk file if already exists with contents
		/*
		if (!$fd = @fopen(dirname(__FILE__).'/editorial.xml', 'w'))
			return false;
		@fclose($fd);
		*/
		return $this->registerHook('home');
	}

	function putContent($xml_data, $key, $field, $forbidden, $section)
	{
		foreach ($forbidden AS $line)
			if ($key == $line)
				return 0;
		if (!eregi('^'.$section.'_', $key))
			return 0;
		$key = eregi_replace('^'.$section.'_', '', $key);
		//$field = pSQL($field);
		$field = htmlspecialchars($field);
		if (!$field)
			return 0;
		return ("\n".'		<'.$key.'>'.$field.'</'.$key.'>');
	}

	function getContent()
	{
		/* display the module name */
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		/* update the editorial xml */
		if (isset($_POST['submitUpdate']))
		{
			// Forbidden key
			$forbidden = array('submitUpdate');
			
			foreach ($_POST AS $key => $value)
				if (!Validate::isCleanHtml($_POST[$key]))
				{
					$this->_html .= $this->displayError($this->l('Invalid html field, javascript is forbidden'));
					$this->_displayForm();
					return $this->_html;
				}

			// Generate new XML data
			$newXml = '<?xml version=\'1.0\' encoding=\'utf-8\' ?>'."\n";
			$newXml .= '<editorial>'."\n";
			$newXml .= '	<header>';
			// Making header data
			foreach ($_POST AS $key => $field)
				if ($line = $this->putContent($newXml, $key, $field, $forbidden, 'header'))
					$newXml .= $line;
			$newXml .= "\n".'	</header>'."\n";
			$newXml .= '	<body>';
			// Making body data
			foreach ($_POST AS $key => $field)
				if ($line = $this->putContent($newXml, $key, $field, $forbidden, 'body'))
					$newXml .= $line;
			$newXml .= "\n".'	</body>'."\n";
			$newXml .= '</editorial>'."\n";

			/* write it into the editorial xml file */
			if ($fd = @fopen(dirname(__FILE__).'/editorial.xml', 'w'))
			{
				if (!@fwrite($fd, $newXml))
					$this->_html .= $this->displayError($this->l('Unable to write to the editor file.'));
				if (!@fclose($fd))
					$this->_html .= $this->displayError($this->l('Can\'t close the editor file.'));
			}
			else
				$this->_html .= $this->displayError($this->l('Unable to update the editor file.<br />Please check the editor file\'s writing permissions.'));

			/* upload the image */
			if (isset($_FILES['body_homepage_logo']) AND isset($_FILES['body_homepage_logo']['tmp_name']) AND !empty($_FILES['body_homepage_logo']['tmp_name']))
			{
				Configuration::set('PS_IMAGE_GENERATION_METHOD', 1);
				if ($error = checkImage($_FILES['body_homepage_logo'], $this->maxImageSize))
					$this->_html .= $error;
				elseif (!imageResize($_FILES['body_homepage_logo'], dirname(__FILE__).'/homepage_logo.jpg'))
					$this->_html .= $this->displayError($this->l('An error occurred during the image upload.'));
			}
		}

		/* display the editorial's form */
		$this->_displayForm();

		return $this->_html;
	}

	private function _displayForm()
	{
		global $cookie;
		
		/* Languages preliminaries */
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
		$iso = Language::getIsoById($defaultLanguage);
		$divLangName = 'title¤subheading¤cpara¤logo_subheading';

		/* xml loading */
		$xml = false;
		if (file_exists(dirname(__FILE__).'/editorial.xml'))
				if (!$xml = @simplexml_load_file(dirname(__FILE__).'/editorial.xml'))
					$this->_html .= $this->displayError($this->l('Your editor file is empty.'));

		$this->_html .= '<br />
			<script type="text/javascript" src="../js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
			<script language="javascript" type="text/javascript">
				tinyMCE.init({
					language : "';
		$iso = Language::getIsoById(intval($cookie->id_lang));
		$this->_html .= ((!file_exists(PS_ADMIN_DIR.'/../js/tinymce/jscripts/tiny_mce/langs/'.$iso.'.js')) ? 'en' : $iso).'",
					mode : "textareas",
					elements : "nourlconvert",
					convert_urls : false,
					theme : "advanced",
					theme_advanced_buttons1 : "bold, italic, underline, fontselect, fontsizeselect",
					theme_advanced_buttons2 : "forecolor, backcolor, separator, justifyleft, justifycenter, justifyright, justifyfull, separator, bullist, numlist, separator, undo, redo, separator, link, unlink, separator, code",
					theme_advanced_buttons3 : "",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					plugins : "contextmenu, directionality, media, paste, preview, safari",
					theme_advanced_buttons3_add : "ltr,rtl,pastetext,pasteword,selectall",
					theme_advanced_buttons1_add : "media,preview",
					paste_create_paragraphs : false,
					paste_create_linebreaks : false,
					paste_use_dialog : true,
					paste_auto_cleanup_on_paste : true,
					paste_convert_middot_lists : false,
					paste_unindented_list_class : "unindentedList",
					paste_convert_headers_to_strong : true,
					paste_insert_word_content_callback : "convertWord",
					plugin_preview_width : "500",
					plugin_preview_height : "600",
					extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"
				});
				function convertWord(type, content)
				{
					switch (type)
					{
						case "before":
							break;
						case "after":
							break;
					}
					return content;
				}
		</script>
		<script language="javascript">id_language = Number('.$defaultLanguage.');</script>
		<form method="post" action="'.$_SERVER['REQUEST_URI'].'" enctype="multipart/form-data">
			<fieldset style="width: 900px;">
				<legend><img src="'.$this->_path.'logo.gif" alt="" title="" /> '.$this->displayName.'</legend>
				<label>'.$this->l('Main title').'</label>
				<div class="margin-form">';
				
				foreach ($languages as $language)
				{
					$this->_html .= '
					<div id="title_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">
						<input type="text" name="body_title_'.$language['id_lang'].'" id="body_title_'.$language['id_lang'].'" size="64" value="'.($xml ? stripslashes(htmlspecialchars($xml->body->{'title_'.$language['id_lang']})) : '').'" />
					</div>';
				 }
				$this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'title', true);
				
				
		$this->_html .= '
					<p class="clear">'.$this->l('Appears along top of homepage').'</p>
				</div>
				<label>'.$this->l('Subheading').'</label>
				<div class="margin-form">';
				
				foreach ($languages as $language)
				{
					$this->_html .= '
					<div id="subheading_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">
						<input type="text" name="body_subheading_'.$language['id_lang'].'" id="body_subheading_'.$language['id_lang'].'" size="64" value="'.($xml ? stripslashes(htmlspecialchars($xml->body->{'subheading_'.$language['id_lang']})) : '').'" />
					</div>';
				 }
				$this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'subheading', true);
				
		$this->_html .= '
					<div class="clear"></div>
				</div>
				<label>'.$this->l('Introductory text').'</label>
				<div class="margin-form">';

				foreach ($languages as $language)
				{
					$this->_html .= '
					<div id="cpara_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">
						<textarea cols="100" rows="30" id="body_paragraph_'.$language['id_lang'].'" name="body_paragraph_'.$language['id_lang'].'">'.($xml ? stripslashes(htmlspecialchars($xml->body->{'paragraph_'.$language['id_lang']})) : '').'</textarea>
					</div>';
				 }
				
				$this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'cpara', true);
				
				$this->_html .= '
					<p class="clear">'.$this->l('Text of your choice; for example, explain your mission, highlight a new product, or describe a recent event').'</p>
				</div>
				<label>'.$this->l('Homepage\'s logo').' </label>
				<div class="margin-form">
					<img src="'.$this->_path.'homepage_logo.jpg" alt="" title="" style="" /><br />
					<input type="file" name="body_homepage_logo" />
					<p style="clear: both">'.$this->l('Will appear next to the Introductory Text above').'</p>
				</div>
				<label>'.$this->l('Homepage logo link').'</label>
				<div class="margin-form">
					<input type="text" name="body_home_logo_link" size="64" value="'.($xml ? stripslashes(htmlspecialchars($xml->body->home_logo_link)) : '').'" />
					<p style="clear: both">'.$this->l('Link used on the 2nd logo').'</p>
				</div>
				<label>'.$this->l('Homepage logo subheading').'</label>
				<div class="margin-form">';
				
				foreach ($languages as $language)
				{
					$this->_html .= '
					<div id="logo_subheading_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">
						<input type="text" name="body_logo_subheading_'.$language['id_lang'].'" id="logo_subheading_'.$language['id_lang'].'" size="64" value="'.($xml ? stripslashes(htmlspecialchars($xml->body->{'logo_subheading_'.$language['id_lang']})) : '').'" />
					</div>';
				 }
				
				$this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'logo_subheading', true);
				
				$this->_html .= '
					<div class="clear"></div>
				</div>
				<div class="clear pspace"></div>
				<div class="margin-form clear"><input type="submit" name="submitUpdate" value="'.$this->l('Update the editor').'" class="button" /></div>
			</fieldset>
		</form>';
	}

	function hookHome($params)
	{
		if (file_exists('modules/editorial/editorial.xml'))
		{
			if ($xml = simplexml_load_file('modules/editorial/editorial.xml'))
			{
				global $cookie, $smarty;
				$smarty->assign(array(
					'xml' => $xml,
					'homepage_logo' => file_exists('modules/editorial/homepage_logo.jpg'),
					'logo_subheading' => 'logo_subheading_'.$cookie->id_lang,
					'title' => 'title_'.$cookie->id_lang,
					'subheading' => 'subheading_'.$cookie->id_lang,
					'paragraph' => 'paragraph_'.$cookie->id_lang,
					'this_path' => $this->_path
				));
				return $this->display(__FILE__, 'editorial.tpl');
			}
		}
		return false;
	}

}
