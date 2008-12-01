<?php

class BlockInfos extends Module
{
		private $_html;
		
    public function __construct()
    {
        $this->name = 'blockinfos';
        $this->tab = 'Blocks';
        $this->version = 1.0;
        parent::__construct();

        /* The parent construct is required for translations */
				$this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Info block');
        $this->description = $this->l('Adds a block with several information links');
     }
    
    public function install()
    {
  		if (!parent::install() OR !$this->registerHook('leftColumn'))
				return false;

			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'block_cms`(`id_block`, `id_cms`) VALUES
																 	('.intval($this->id).', 1),
																 	('.intval($this->id).', 2),
																 	('.intval($this->id).', 3),
																 	('.intval($this->id).', 4)');

		return true;
    }
    
    public function uninstall()
    {
    	Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'block_cms`
    															WHERE `id_block` ='.intval($this->id));
    	parent::uninstall();
    }
    
    public function getContent()
    {
			if(isset($_POST['btnSubmit']))
				$this->_postProcess();
    	$this-> _displayForm();
    	return $this->_html;
    }
    
		private function _displayForm()
		{
			global $cookie;
	
			$this->_html .=
			'<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
				<fieldset>
				<legend>'.$this->l('Selected files displayed').'</legend>
						<span>'.$this->l('Please check files that will be displayed in this module').'.</span><br /><br />
						<table cellspacing="0" cellpadding="0" class="table" style="width: 29.5em;">
								<thead>	
									<tr>
										<th><input type="checkbox" name="checkme" class="noborder" onclick="checkDelBoxes(this.form, \'categoryBox[]\', this.checked)" /></th>
										<th>'.$this->l('ID').'</th>
										<th>'.$this->l('Name').'</th>
									</tr>
								</thead>
								<tbody>';	
	$cms = CMS::listCms($cookie->id_lang);
	foreach($cms AS $row)
		$this->_html.='
									<tr><td><input type="checkbox" class="noborder" value="'.$row['id_cms'].'" name="categoryBox[]" '.((CMS::isInBlock($row['id_cms'], $this->id)) ? 'checked="checked"' : '').'></td><td>'.$row['id_cms'].'</td><td>'.$row['meta_title'].'</td></tr>';
	$this->_html .='
								</tbody>
							</table>
							<br />
							<input type="submit" name="btnSubmit" class="button" value="'.$this->l('Update').'">
				</fieldset>
			</form>';
		}

		private function _postProcess()
		{
			if(isset($_POST['categoryBox']) AND is_array($_POST['categoryBox']) AND count($_POST['categoryBox'] >= 1))
			{
				foreach($_POST['categoryBox'] AS $row)
					$cms[] = intval($row);
				if(CMS::updateCmsToBlock($cms, $this->id))
			 		$this->_html .= '<div class="conf confirm">'.$this->l('Cms Updated').'</div>';
			 }
		}
	
    function hookLeftColumn($params)
    {
			global $smarty, $cookie;
			$cms = CMS::listCms($cookie->id_lang, $this->id);
			$id_cms = array();
			foreach($cms AS $row)
				$id_cms[] = intval($row['id_cms']);
				 
			$smarty->assign('cmslinks', CMS::getLinks($cookie->id_lang, $id_cms));		
			return $this->display(__FILE__, 'blockinfos.tpl');
		}
	
	function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params);
	}
}

?>
