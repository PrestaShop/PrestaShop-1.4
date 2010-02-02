<?php

/**
  * Statistics
  * @category stats
  *
  * @author Damien Metzger / Epitech
  * @copyright Epitech / PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  */
  
class StatsCarrier extends ModuleGraph
{
    private $_html = '';
    private $_query = '';
    private $_query2 = '';
    private $_option = '';

    function __construct()
    {
        $this->name = 'statscarrier';
        $this->tab = 'Stats';
        $this->version = 1.0;
		
		parent::__construct();
		
        $this->displayName = $this->l('Carrier distribution');
        $this->description = $this->l('Display the carriers distribution');
    }
	
	public function install()
	{
		return (parent::install() AND $this->registerHook('AdminStatsModules'));
	}
		
	public function hookAdminStatsModules($params)
	{
		global $cookie;
		
		$result = Db::getInstance()->getRow('
		SELECT COUNT(o.`id_order`) as total
		FROM `'._DB_PREFIX_.'orders` o
		WHERE o.`date_add` BETWEEN '.ModuleGraph::getDateBetween().'
		'.(intval(Tools::getValue('id_order_state')) ? 'AND (SELECT oh.id_order_state FROM `'._DB_PREFIX_.'order_history` oh WHERE o.id_order = oh.id_order ORDER BY oh.date_add DESC, oh.id_order_history DESC LIMIT 1) = '.intval(Tools::getValue('id_order_state')) : ''));
		$states = OrderState::getOrderStates(intval($cookie->id_lang));
	
		$this->_html = '
		<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->displayName.'</legend>
			<form action="'.$_SERVER['REQUEST_URI'].'" method="post" style="float: right;">
				<select name="id_order_state">
					<option value="0"'.((!Tools::getValue('id_order_state')) ? ' selected="selected"' : '').'>'.$this->l('All').'</option>';
		foreach ($states AS $state)
			$this->_html .= '<option value="'.$state['id_order_state'].'"'.(($state['id_order_state'] == Tools::getValue('id_order_state')) ? ' selected="selected"' : '').'>'.$state['name'].'</option>';
		$this->_html .= '</select>
				<input type="submit" name="submitState" value="'.$this->l('Filter').'" class="button" />
			</form>
			<p><img src="../img/admin/down.gif" />'.$this->l('This graph represents the carrier distribution for your orders. You can also limit it to one order state.').'</p>
			'.($result['total'] ? ModuleGraph::engine(array('type' => 'pie', 'option' => Tools::getValue('id_order_state'))) : $this->l('No valid orders for this period.')).'
		</fieldset>';
		return $this->_html;
	}
	
	public function setOption($option, $layers = 1)
	{
		$this->_option = intval($option);
	}
	
	protected function getData($layers)
	{
		$stateQuery = '';
		if (intval($this->_option))
			$stateQuery = 'AND (SELECT oh.id_order_state FROM `'._DB_PREFIX_.'order_history` oh WHERE o.id_order = oh.id_order ORDER BY oh.date_add DESC, oh.id_order_history DESC LIMIT 1) = '.intval($this->_option);
		$this->_titles['main'] = $this->l('Percentage of orders by carrier');
		$result = Db::getInstance()->ExecuteS('
		SELECT c.name, COUNT(DISTINCT o.`id_order`) as total
		FROM `'._DB_PREFIX_.'carrier` c
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON o.id_carrier = c.id_carrier
		WHERE o.`date_add` BETWEEN '.ModuleGraph::getDateBetween().'
		'.$stateQuery.'
		GROUP BY c.`id_carrier`');
		foreach ($result as $row)
		{
		    $this->_values[] = $row['total'];
		    $this->_legend[] = $row['name'];
		}
	}
}

?>
