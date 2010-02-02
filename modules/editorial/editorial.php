<?php

class Editorial extends Module
{
	/** @var max image size */
	protected $maxImageSize = 307200;

	function __construct()
	{
		$this->name = 'editorial';
		$this->tab = 'Tools';
		$this->version = '1.5';
		
		parent::__construct();
		
		$this->displayName = $this->l('Home text editor');
		$this->description = $this->l('A text editor module for your homepage');
	}

	function install()
	{
		if (!parent::install())
			return false;
		return $this->registerHook('home');
	}

	function putContent($xml_data, $key, $field, $forbidden, $section)
	{
		foreach ($forbidden AS $line)
			if ($key == $line)
				return 0;
		if (!preg_match('/^'.$section.'_/i', $key))
			return 0;
		$key = preg_replace('/^'.$section.'_/i', '', $key);
		$field = htmlspecialchars($field);
		if (!$field)
			return 0;
		return ("\n".'		<'.$key.'>'.$field.'</'.$key.'>');
	}

	function getContent()
	{
		/* display the module name */
		$this->_html = '<h2>'.$this->displayName.'</h2>';
		$errors = '';

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
					$errors .= $this->displayError($this->l('Unable to write to the editor file.'));
				if (!@fclose($fd))
					$errors .= $this->displayError($this->l('Can\'t close the editor file.'));
			}
			else
				$errors .= $this->displayError($this->l('Unable to update the editor file.<br />Please check the editor file\'s writing permissions.'));

			/* upload the image */
			if (isset($_FILES['body_homepage_logo']) AND isset($_FILES['body_homepage_logo']['tmp_name']) AND !empty($_FILES['body_homepage_logo']['tmp_name']))
			{
				Configuration::set('PS_IMAGE_GENERATION_METHOD', 1);
				if ($error = checkImage($_FILES['body_homepage_logo'], $this->maxImageSize))
					$errors .= $error;
				elseif (!$tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS') OR !move_uploaded_file($_FILES['body_homepage_logo']['tmp_name'], $tmpName))
					return false;
				elseif (!imageResize($tmpName, dirname(__FILE__).'/homepage_logo.jpg'))
					$errors .= $this->displayError($this->l('An error occurred during the image upload.'));
				unlink($tmpName);
			}
			$this->_html .= $errors == '' ? $this->displayConfirmation('Settings updated successfully') : $errors;
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
				if (!$xml = simplexml_load_file(dirname(__FILE__).'/editorial.xml'))
					$this->_html .= $this->displayError($this->l('Your editor file is empty.'));

		$this->_html .= '
		<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
		<script type="text/javascript">
		function tinyMCEInit(element)
		{
			$().ready(function() {
				$(element).tinymce({
					// Location of TinyMCE script
					script_url : \''.__PS_BASE_URI__.'js/tinymce/jscripts/tiny_mce/tiny_mce.js\',
					// General options
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
					theme_advanced_resizing : true,
					content_css : "'.__PS_BASE_URI__.'themes/'._THEME_NAME_.'/css/global.css",
					// Drop lists for link/image/media/template dialogs
					template_external_list_url : "lists/template_list.js",
					external_link_list_url : "lists/link_list.js",
					external_image_list_url : "lists/image_list.js",
					media_external_list_url : "lists/media_list.js",
					elements : "nourlconvert",
					convert_urls : false,
					language : "'.(file_exists(_PS_ROOT_DIR_.'/js/tinymce/jscripts/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en').'"
				});
			});
		}
		tinyMCEInit(\'textarea.rte\');
		</script>
		<script type="text/javascript">id_language = Number('.$defaultLanguage.');</script>
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
						<textarea class="rte" cols="70" rows="30" id="body_paragraph_'.$language['id_lang'].'" name="body_paragraph_'.$language['id_lang'].'">'.($xml ? stripslashes(htmlspecialchars($xml->body->{'paragraph_'.$language['id_lang']})) : '').'</textarea>
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
