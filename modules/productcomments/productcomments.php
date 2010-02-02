<?php

class ProductComments extends Module
{
	const INSTALL_SQL_FILE = 'install.sql';

    private $_html = '';
    private $_postErrors = array();

    function __construct()
    {
        $this->name = 'productcomments';
        $this->tab = 'Products';
        $this->version = '0.2';

        parent::__construct();

        $this->displayName = $this->l('Product Comments');
        $this->description = $this->l('Allow users to post comment about a product');
    }

    function install()
    {
		if (!file_exists(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			return (false);
		else if (!$sql = file_get_contents(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			return (false);
		$sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
		$sql = preg_split("/;\s*[\r\n]+/",$sql);
		foreach ($sql as $query)
			if (!Db::getInstance()->Execute(trim($query)))
				return (false);
        if (parent::install() == false 
				OR $this->registerHook('productTab') == false
				OR $this->registerHook('productTabContent') == false
				OR $this->registerHook('header') == false
				OR Configuration::updateValue('PRODUCT_COMMENTS_MODERATE', 1) == false)
			return (false);
		return (true);
    }

	public function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';
		$this->_checkModerateComment();
		$this->_checkCriterion();
		$this->_checkCriterionProduct();
		return $this->_html.$this->_displayForm();
	}

	private function _checkModerateComment()
	{
		$action = Tools::getValue('action');
		if (Tools::isSubmit('submitModerate'))
		{
			$moderate = Tools::getValue('moderate');
			if (intval($moderate) != 0)
				$moderate = 1;
			Configuration::updateValue('PRODUCT_COMMENTS_MODERATE', intval($moderate));
			$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}
		else if (empty($action) === false &&
			intval(Configuration::get('PRODUCT_COMMENTS_MODERATE')))
		{
			$id_product_comment = Tools::getValue('id_product_comment');
			require_once(dirname(__FILE__).'/ProductComment.php');
			switch ($action)
			{
				case 'accept':
					$comment = new ProductComment($id_product_comment);
					$comment->validate();
					break;
				case 'delete':
					$comment = new ProductComment($id_product_comment);
					$comment->delete();
					ProductComment::deleteGrades($id_product_comment);
					break;
				default:
					;
			}
		}
	}
	
	private function _checkCriterion()
	{
		$action_criterion = Tools::getValue('criterion_action');
		$name = Tools::getValue('criterion');
		if (Tools::isSubmit('submitCriterion') AND empty($action_criterion) AND !empty($name))
		{
			global $cookie;

			require_once(dirname(__FILE__).'/ProductCommentCriterion.php');
			ProductCommentCriterion::add($cookie->id_lang, $name);
			$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}
		elseif (!empty($action_criterion) AND empty($name))
		{
			$id_product_comment_criterion = Tools::getValue('id_product_comment_criterion');
			require_once(dirname(__FILE__).'/ProductCommentCriterion.php');
			switch ($action_criterion)
			{
				case 'edit':
					ProductCommentCriterion::update($id_product_comment_criterion,
						Tools::getValue('criterion_id_lang'),
						Tools::getValue('criterion_name'));
					break;
				case 'delete':
					ProductCommentCriterion::delete($id_product_comment_criterion);
					break;
				default:
					;
			}
		}
	}
	
	private function _checkCriterionProduct()
	{
		if (Tools::isSubmit('submitCriterionProduct'))
		{
			$id_product_comment_criterions = Tools::getValue('id_product_comment_criterion');
			$id_product = Tools::getValue('id_product');
			require_once(dirname(__FILE__).'/ProductCommentCriterion.php');
			ProductCommentCriterion::deleteByProduct($id_product);
			if (empty($id_product_comment_criterions) === false)
				foreach ($id_product_comment_criterions as $id_product_comment_criterion)
					ProductCommentCriterion::addToProduct($id_product_comment_criterion, $id_product);
			$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}
	}
	
	private function _displayForm()
	{
		$this->_displayFormModerate();
		$this->_displayFormCriterion();
		$this->_displayFormProductCriterion();
		return ($this->_html);
	}

	private function _displayFormModerate()
	{
		$this->_html = '<script type="text/javascript" src="'.$this->_path.'js/moderate.js"></script>
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post" name="comment_form">
			<fieldset><legend><img src="'.$this->_path.'img/comments_delete.png" alt="" title="" />'.$this->l('Moderate Comments').'</legend>
				<label>'.$this->l('Validation required').'</label>
				<div class="margin-form">
					<input type="radio" name="moderate" id="moderate_on" value="1" '.(Configuration::get('PRODUCT_COMMENTS_MODERATE') ? 'checked="checked" ' : '').'/>
					<label class="t" for="moderate_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="moderate" id="moderate_off" value="0" '.(!Configuration::get('PRODUCT_COMMENTS_MODERATE') ? 'checked="checked" ' : '').'/>
					<label class="t" for="moderate_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
				</div>
				<div class="margin-form clear"><input type="submit" name="submitModerate" value="'.$this->l('Save').'" class="button" /></div>';
		if (Configuration::get('PRODUCT_COMMENTS_MODERATE'))
		{
			require_once(dirname(__FILE__).'/ProductComment.php');
			$comments = ProductComment::getByValidate();
			if (sizeof($comments))
			{
				$this->_html .= '<input type="hidden" name="id_product_comment" id="id_product_comment" />
				 <input type="hidden" name="action" id="action" />
				 <br /><table class="table" border="0" cellspacing="0" cellpadding="0">
				 <thead>
				  <tr>
				   <th style="width:30px;">'.$this->l('Actions').'</th>
				   <th style="width:150px;">'.$this->l('Author').'</th>
				   <th style="width:700px;">'.$this->l('Comment').'</th>
				  </tr>
				 </thead>
				 <tbody>';
				foreach ($comments as $comment)
				{
					$this->_html .= '<tr>
					 <td><a href="javascript:;" onclick="acceptComment(\''.intval($comment['id_product_comment']).'\');"><img src="'.$this->_path.'img/accept.png" alt="'.$this->l('Accept').'" title="'.$this->l('Accept').'" /></a>
					     <a href="javascript:;" onclick="deleteComment(\''.intval($comment['id_product_comment']).'\');"><img src="'.$this->_path.'img/delete.png" alt="'.$this->l('Delete').'" title="'.$this->l('Delete').'" /></a></td>
					 <td>'.htmlspecialchars($comment['firstname'], ENT_COMPAT, 'UTF-8').' '.htmlspecialchars(substr($comment['lastname'], 0, 1), ENT_COMPAT, 'UTF-8').'.</td>
					 <td>'.htmlspecialchars($comment['content'], ENT_COMPAT, 'UTF-8').'</td>
					</tr>';
				}
				$this->_html .= '</tbody>
				</table>';
			}
			else
				$this->_html .= $this->l('No comments to validate.');
		}
		$this->_html .= '</fieldset></form><br />';
	}
	
	private function _displayFormCriterion()
	{
		global $cookie;

		$this->_html .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="post" name="criterion_form">
			<fieldset><legend><img src="'.$this->_path.'img/note.png" alt="" title="" />'.$this->l('Comment\'s criterions').'</legend>
				<label for="criterion">'.$this->l('Comment\'s criterion').'</label>
				<div class="margin-form">
					<input type="text" name="criterion" id="criterion" /> <input type="submit" name="submitCriterion" value="'.$this->l('Add').'" class="button" />
					<p>'.$this->l('Create a new grading criterion for your products.').'</p>
				</div>
		<p>'.$this->l('Once created, you must activate it for the desired products with the form below.').'<br />'.$this->l('Be aware that the criterions are independent in each language.').'</p>';
		require_once(dirname(__FILE__).'/ProductCommentCriterion.php');
		$criterions = ProductCommentCriterion::get($cookie->id_lang);
		if (sizeof($criterions))
		{
			$this->_html .= '
				 <input type="hidden" name="id_product_comment_criterion" id="id_product_comment_criterion" />
				 <input type="hidden" name="criterion_name" id="criterion_name" />
				 <input type="hidden" name="criterion_id_lang" id="criterion_id_lang" value="'.intval($cookie->id_lang).'" />
				 <input type="hidden" name="criterion_action" id="criterion_action" />
				 <br /><table class="table">
				 <thead>
				  <tr>
				   <th style="width:30px;">'.$this->l('Actions').'</th>
				   <th style="width:260px;">'.$this->l('Criterion').'</th>
				   <th style="width:30px;">'.$this->l('Actions').'</th>
				   <th style="width:260px;">'.$this->l('Criterion').'</th>
				   <th style="width:30px;">'.$this->l('Actions').'</th>
				   <th style="width:260px;">'.$this->l('Criterion').'</th>
				  </tr>
				 </thead>
				 <tbody><tr>';
				$len = sizeof($criterions);
				for ($i = 0; $i < $len; ++$i)
				{
					$this->_html .= '
					 <td><a href="javascript:;" onclick="editCriterion(\''.intval($criterions[$i]['id_product_comment_criterion']).'\');"><img src="'.$this->_path.'img/accept.png" alt="'.$this->l('Accept').'" /></a>
					     <a href="javascript:;" onclick="deleteCriterion(\''.intval($criterions[$i]['id_product_comment_criterion']).'\');"><img src="'.$this->_path.'img/delete.png" alt="'.$this->l('Delete').'" /></a></td>
					 <td><input type="text" id="criterion_name_'.intval($criterions[$i]['id_product_comment_criterion']).'" value="'.htmlspecialchars($criterions[$i]['name'], ENT_COMPAT, 'UTF-8').'" /></td>';
					if (!(($i + 1) % 3) || ($i + 1) >= $len)
						$this->_html .= '</tr><tr>';
				}
				if (!$len)
					$this->_html = '</tr>';
				$this->_html .= '</tbody>
				</table>';
		}
		$this->_html .= '</fieldset></form><br />';
	}
	
	private function _displayFormProductCriterion()
	{
		global $cookie;

		$products = Product::getSimpleProducts($cookie->id_lang);
		$this->_html .= '<script type="text/javascript" src="'.$this->_path.'js/productCriterion.js"></script>
		   <form action="'.$_SERVER['REQUEST_URI'].'" method="post" name="product_criterion_form">
			<fieldset><legend><img src="'.$this->_path.'img/note_go.png" alt="" title="" />'.$this->l('Product\'s criterions').'</legend>
				<p>'.$this->l('Select the grading criterions corresponding to each product. You can select multiple criterions by pressing the ctrl key.').'</p><br />
				<label for="id_product">'.$this->l('Product').'</label>
				<div class="margin-form">
					<select name="id_product" id="id_product" onchange="getProductCriterion(\''.$this->_path.'\', this.options[this.selectedIndex].value, \''.intval($cookie->id_lang).'\');">';
		foreach ($products as $product)
			$this->_html .= '<option value="'.intval($product['id_product']).'">'.htmlspecialchars($product['name'], ENT_COMPAT, 'UTF-8').'</option>';
		$this->_html .= '</select>
				</div>
				<label for="id_product_comment_criterion">'.$this->l('Grading criterions').'</label>
				<div id="product_criterions" class="margin-form">
				</div>
				<div class="margin-form clear"><input type="submit" name="submitCriterionProduct" value="'.$this->l('Save').'" class="button" /></div>
				<script type="text/javascript">
					getProductCriterion(\''.$this->_path.'\', document.getElementById(\'id_product\').options[0].value, \''.intval($cookie->id_lang).'\');
				</script>
			</fieldset>
			</form>';
	}
	
	public function hookProductTab($params)
    {
		global $smarty;
		global $cookie;
		
		require_once(dirname(__FILE__).'/ProductComment.php');
		require_once(dirname(__FILE__).'/ProductCommentCriterion.php');
		
		$smarty->assign(array(
			'comments' => ProductComment::getByProduct(intval($_GET['id_product'])),
			'criterions' => ProductCommentCriterion::getByProduct(intval($_GET['id_product']), intval($cookie->id_lang)),
			'nbComments' => intval(ProductComment::getCommentNumber(intval($_GET['id_product'])))
		));
		return ($this->display(__FILE__, '/tab.tpl'));
	}

	private function _frontOfficePostProcess()
	{
		global $smarty, $cookie, $errors;

		require_once(dirname(__FILE__).'/ProductComment.php');
		require_once(dirname(__FILE__).'/ProductCommentCriterion.php');
		if (Tools::isSubmit('submitMessage') AND empty($cookie->id_customer) === false)
		{
			if (Tools::getValue('content'))
			{
				$comment = new ProductComment();
				$comment->content = strip_tags(Tools::getValue('content'));
				$comment->id_product = intval($_GET['id_product']);
				$comment->id_customer = intval($cookie->id_customer);
				$comment->grade = 0;
				$comment->validate = 0;
				if (!$comment->content)
					$errors[] = $this->l('Invalid comment text posted.');
				else
				{
					$comment->save();
					for ($i = 1, $grade = 0; isset($_POST[$i.'_grade']) === true; ++$i)
					{
						$cgrade = intval(Tools::getValue($i.'_grade'));
						$grade += $cgrade;
						$cid_product_comment_criterion = Tools::getValue('id_product_comment_criterion_'.$i);
						ProductCommentCriterion::addGrade($comment->id, $cid_product_comment_criterion, $cgrade);
					}
					if (($i - 1) > 0)
						$comment->grade = ($grade / ($i - 1));
					if (!$comment->save())
						$errors[] = $this->l('An error occured while saving your comment.');
					else
						$smarty->assign('confirmation', $this->l('Comment posted successfully.').(intval(Configuration::get('PRODUCT_COMMENTS_MODERATE')) ? $this->l(' Awaiting moderator validation.') : ''));
				}
			}
			else
				$errors[] = $this->l('Comment text is required.');
		}
	}

    public function hookProductTabContent($params)
    {
		global $smarty, $cookie, $nbProducts;

		$commentNumber = intval(ProductComment::getCommentNumber(intval($_GET['id_product'])));
		$averages = ProductComment::getAveragesByProduct(intval($_GET['id_product']), intval($cookie->id_lang));

		$averageTotal = 0;
		foreach ($averages AS $average)
			$averageTotal += floatval($average);
		$averageTotal = count($averages) ? ($averageTotal / count($averages)) : 0;
		$smarty->assign(array(
			'logged' => intval($cookie->id_customer),
			'action_url' => $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'],
			'comments' => ProductComment::getByProduct(intval($_GET['id_product'])),
			'criterions' => ProductCommentCriterion::getByProduct(intval($_GET['id_product']), intval($cookie->id_lang)),
			'averages' => $averages,
			'product_comment_path' => $this->_path,
			'averageTotal' => $averageTotal
		));
		$nbProducts = $commentNumber;
		require_once(dirname(__FILE__).'/../../pagination.php');
		return ($this->display(__FILE__, '/productcomments.tpl'));
	}

	public function hookHeader()
	{
		$this->_frontOfficePostProcess();
	}
}

?>