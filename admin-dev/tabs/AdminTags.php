<?php

class AdminTags extends AdminTab
{
	public function  __construct()
	{
		global $cookie;
		
		$this->table = 'tag';
		$this->className = 'Tag';
		$this->edit = true;
		$this->delete = true;

		$this->fieldsDisplay = array(
		'id_tag' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25, 'filter_key' => 'a!id_seller_message'),
		'lang' => array('title' => $this->l('Language'), 'filter_key' => 'l!name'),
		'name' => array('title' => $this->l('Name'), 'width' => 200),
		'products' => array('title' => $this->l('Products'), 'align' => 'right'));

		$this->_select = 'l.name as lang, COUNT(pt.id_product) as products';
		$this->_join = '
		LEFT JOIN `'._DB_PREFIX_.'product_tag` pt ON (a.`id_tag` = pt.`id_tag`)
		LEFT JOIN `'._DB_PREFIX_.'lang` l ON l.`id_lang` = a.`id_lang`';
		$this->_where = 'GROUP BY a.name, a.id_lang';

		parent::__construct();
	}
	
	public function postProcess()
	{
		global $currentIndex;
		
		return parent::postProcess();
	}
	
	public function displayForm()
	{
		global $currentIndex, $cookie;
		$obj = $this->loadObject(true);
		$languages = Language::getLanguages();
		
		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" class="width3">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/t/AdminTags.gif" />'.$this->l('Tag').'</legend>
				<label>'.$this->l('Name').' </label>
				<div class="margin-form">
					<input type="text" size="33" name="name" value="'.htmlentities($this->getFieldValue($obj, 'name'), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
				</div>
				<label>'.$this->l('Language').' </label>
				<div class="margin-form">
					<select name="id_lang">
						<option value="">-</option>';
						foreach ($languages as $language)
							echo '<option value="'.$language['id_lang'].'" '.($language['id_lang'] == $this->getFieldValue($obj, 'id_lang') ? 'selected="selected"' : '').'>'.$language['name'].'</option>';
					echo '
					</select>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}

}

?>
