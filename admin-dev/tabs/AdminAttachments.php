<?php

/**
  * Attachments tab for admin panel, AdminAttachments.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminAttachments extends AdminTab
{
	protected $maxFileSize  = 2000000;
	
	public function __construct()
	{
		global $cookie;
		
	 	$this->table = 'attachment';
	 	$this->className = 'Attachment';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->delete = true;
		
		$this->fieldsDisplay = array(
		'id_attachment' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'name' => array('title' => $this->l('Name')),
		'file' => array('title' => $this->l('File')));
	
		parent::__construct();
	}

	public function postProcess()
	{
		if (Tools::isSubmit('submitAdd'.$this->table))
		{
			if ($id = intval(Tools::getValue('id_attachment')) AND $a = new Attachment($id))
			{
				$_POST['file'] = $a->file;
				$_POST['mime'] = $a->mime;
			}
			if (!sizeof($this->_errors))
				if (isset($_FILES['file']) AND is_uploaded_file($_FILES['file']['tmp_name']))
				{
					if ($_FILES['file']['size'] > $this->maxFileSize)
						$this->_errors[] = $this->l('File too large, maximum size allowed:').' '.($this->maxFileSize/1000).' '.$this->l('kb');
					else
					{
						$uploadDir = dirname(__FILE__).'/../../download/';
						do $uniqid = sha1(microtime());	while (file_exists($uploadDir.$uniqid));
						if (!copy($_FILES['file']['tmp_name'], $uploadDir.$uniqid))
							$this->_errors[] = $this->l('File copy failed');
						@unlink($_FILES['file']['tmp_name']);
						$_POST['file'] = $uniqid;
						$_POST['mime'] = $_FILES['file']['type'];
					}
				}
			$this->validateRules();
		}
		return parent::postProcess();
	}
	
	public function displayForm()
	{
		global $currentIndex, $cookie;
		
		$obj = $this->loadObject(true);
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
		
		echo '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
		</script>
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" enctype="multipart/form-data" class="width2">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/t/AdminAttachments.gif" />'.$this->l('Attachment').'</legend>
				<label>'.$this->l('Name:').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '	<div id="cname_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="name_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'name', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
					</div>';							
		$this->displayFlags($languages, $defaultLanguage, 'cname¤cdescription', 'cname');
		echo '	</div>
				<div class="clear">&nbsp;</div>
				<label>'.$this->l('Description:').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '	<div id="cdescription_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<textarea name="description_'.$language['id_lang'].'">'.htmlentities($this->getFieldValue($obj, 'description', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'</textarea>
					</div>';							
		$this->displayFlags($languages, $defaultLanguage, 'cname¤cdescription', 'cdescription');
		echo '	</div>
				<div class="clear">&nbsp;</div>
				<label>'.$this->l('File:').'</label>
				<div class="margin-form">
					<p><input type="file" name="file" /></p>
					<p>'.$this->l('Upload file from your computer').'</p>
				</div>
				<div class="clear">&nbsp;</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}
}

?>
