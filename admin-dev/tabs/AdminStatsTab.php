<?php

/**
  * Statistics
  * @category stats
  *
  * @author Damien Metzger / Epitech
  * @copyright Epitech / PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.1
  */
  
include_once(PS_ADMIN_DIR.'/tabs/AdminPreferences.php');

abstract class AdminStatsTab extends AdminPreferences
{
	public function __construct()
	{
 		$this->_fieldsSettings = array(
			'PS_STATS_RENDER' => array('title' => $this->l('Graph engine'), 'validation' => 'isGenericName'),
			'PS_STATS_GRID_RENDER' => array('title' => $this->l('Grid engine'), 'validation' => 'isGenericName')
		);
		parent::__construct();
	}
	
	public function postProcess()
	{
		global $cookie;
		
		if (Tools::isSubmit('stats_date'))
		{
			if ($day = Tools::getValue('dateInputDay', -1) AND Validate::isUnsignedInt($day))
				$cookie->stats_day = $day;
			else
				unset($cookie->stats_day);
			if ($month = Tools::getValue('dateInputMonth', -1) AND Validate::isUnsignedInt($month))
				$cookie->stats_month = $month + 1;
			else
				unset($cookie->stats_month);
			if ($year = Tools::getValue('dateInputYear', -1) AND Validate::isUnsignedInt($year))
				$cookie->stats_year = $year;
			else
				unset($cookie->stats_year);
			if ($granularity = Tools::getValue('dateInputGranularity', -1))
				$cookie->stats_granularity = $granularity;
			else
				unset($cookie->stats_granularity);
		}
		
		if (Tools::getValue('submitSettings'))
		{
		 	if ($this->tabAccess['edit'] === '1')
				$this->_postConfig($this->_fieldsSettings);
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit something here.');
		}
	}
	
	protected function displayEngines()
	{
		global $currentIndex, $cookie;
		
		$graphEngine = Configuration::get('PS_STATS_RENDER');
		$gridEngine = Configuration::get('PS_STATS_GRID_RENDER');
		$arrayGraphEngines = ModuleGraphEngine::getGraphEngines();
		$arrayGridEngines = ModuleGridEngine::getGridEngines();
		
		echo '
		<form action="'.$currentIndex.'&token='.$this->token.'&submitSettings=1'.(Tools::getValue('module') ? '&module='.Tools::getValue('module') : '').'" method="post">
			<fieldset><legend><img src="../img/admin/tab-preferences.gif" />'.$this->l('Settings', 'AdminStatsTab').'</legend>';
		echo '<p><strong>'.$this->l('Graph engine', 'AdminStatsTab').' </strong><br />';
		if (sizeof($arrayGraphEngines))
		{
			echo '<select name="PS_STATS_RENDER">';
			foreach ($arrayGraphEngines as $k => $value)
				echo '<option value="'.$k.'"'.($k == $graphEngine ? ' selected="selected"' : '').'>'.$value[0].'</option>';
			echo '</select><p>';
		}
		else
			echo $this->l('No graph engine module installed', 'AdminStatsTab');
		echo '<p><strong>'.$this->l('Grid engine', 'AdminStatsTab').' </strong><br />';
		if (sizeof($arrayGridEngines))
		{
			echo '<select name="PS_STATS_GRID_RENDER">';
			foreach ($arrayGridEngines as $k => $value)
				echo '<option value="'.$k.'"'.($k == $gridEngine ? ' selected="selected"' : '').'>'.$value[0].'</option>';
			echo '</select></p>';
		}
		else
			echo $this->l('No grid engine module installed', 'AdminStatsTab');
		echo '<p><input type="submit" value="'.$this->l('   Save   ', 'AdminStatsTab').'" name="submitSettings" class="button" /></p>
			</fieldset>
		</form><div class="clear space">&nbsp;</div>';
	}
	
	protected function getDate()
	{
		global $cookie;
		$year = isset($cookie->stats_year) ? $cookie->stats_year : date('Y');
		$month = isset($cookie->stats_month) ? sprintf('%02d', $cookie->stats_month) : '%';
		$day = isset($cookie->stats_day) ? sprintf('%02d', $cookie->stats_day) : '%';
		return $year.'-'.$month.'-'.$day;
	}
	
	public function displayCalendar()
	{
		global $cookie;
		$year = isset($cookie->stats_year) ? $cookie->stats_year : date('Y');
		$month = isset($cookie->stats_month) ? $cookie->stats_month - 1 : date('m') - 1;
		$day = isset($cookie->stats_day) ? $cookie->stats_day : date('d');
		$granularity = isset($cookie->stats_granularity) ? $cookie->stats_granularity : 'd';
		
		$result = Db::getInstance()->getRow('SELECT iso_code FROM '._DB_PREFIX_.'lang WHERE `id_lang` = '.intval($cookie->id_lang));
		$iso_code = $result['iso_code'];

		echo '
		<div id="calendar">
		<fieldset style="width: 200px"><legend><img src="../img/admin/date.png" /> '.$this->l('Calendar', 'AdminStatsTab').'</legend>
			<script type="text/javascript" src="'.__PS_BASE_URI__.'tools/datepicker/ui.datepicker.js"></script>
			<script type="text/javascript" src="'.__PS_BASE_URI__.'tools/datepicker/ui.datepicker.granularity.js"></script>';
		
		if (!is_null($iso_code) AND $iso_code != 'en')
			echo '<script type="text/javascript" src="'.__PS_BASE_URI__.'tools/datepicker/lang/ui.datepicker-'.$iso_code.'.js"></script>';
			
		echo	'<script type="text/javascript">
				jQuery(document).ready(function() {
					$(\'#date\').datepicker({
						dateInputDay:\'#dateInputDay\',
						dateInputMonth:\'#dateInputMonth\',
						dateInputYear:\'#dateInputYear\',
						dateInputGranularity:\'#dateInputGranularity\',
						defaultDate:new Date("'.$year.'", "'.$month.'", "'.$day.'"),
						granularity:"'.$granularity.'",';
		echo	'		prevText:"&#x3c;&#x3c;",
						nextText:"&#x3e;&#x3e;",';
		echo	'onSelect: function(date) {$("#dateInput").attr("value", date);}
				    });
				});
			</script>
			<div style="width:150px;">
				<div id="date"></div>
				<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
					<input style="width: 100px" id="dateInput" name="dateInput" type="text" readonly="readonly" value="" />
					<input type="submit" name="stats_date" class="button" value="'.$this->l('OK').'" />
					<input type="hidden" id="dateInputDay" name="dateInputDay" value="'.$day.'" />
					<input type="hidden" id="dateInputMonth" name="dateInputMonth" value="'.$month.'" />
					<input type="hidden" id="dateInputYear" name="dateInputYear" value="'.$year.'" />
					<input type="hidden" id="dateInputGranularity" name="dateInputGranularity" value="'.$granularity.'" />
				</form>
			</div>
		</fieldset>
		<div class="clear space">&nbsp;</div>
		</div>';
	}
	
	public function displaySearch()
	{
		return;
		echo '
		<fieldset style="margin-top:20px; width: 200px"><legend><img src="../img/admin/binoculars.png" /> '.$this->l('Search', 'AdminStatsTab').'</legend>
			<input type="text" /> <input type="button" class="button" value="'.$this->l('Go', 'AdminStatsTab').'" />
		</fieldset>';
	}
	
	private function getModules($limit = false, $auto = true)
	{
		$function = $limit ? 'getRow' : 'ExecuteS';
		return Db::getInstance()->{$function}('
		SELECT h.`name` AS hook, m.`name`
		FROM `'._DB_PREFIX_.'module` m
		LEFT JOIN `'._DB_PREFIX_.'hook_module` hm ON hm.`id_module` = m.`id_module`
		LEFT JOIN `'._DB_PREFIX_.'hook` h ON hm.`id_hook` = h.`id_hook`
		'.($auto ? 'WHERE h.`name` = \''.pSQL(Tools::getValue('tab')).'\'' : 'WHERE h.`name` LIKE \'AdminStats%\'').'
		AND m.`active` = 1
		ORDER BY hm.`position`');
	}
	
	public function displayMenu($auto = true)
	{
		global $currentIndex, $cookie;
		$modules = $auto ? $this->getModules() : $this->getModules(false, false);

		echo '<fieldset style="width: 200px"><legend><img src="../img/admin/navigation.png" /> '.$this->l('Navigation', 'AdminStatsTab').'</legend>';
		if (sizeof($modules))
			foreach ($modules AS $module)
	    	{
				$moduleInstance = Module::getInstanceByName($module['name']);
				if (!$moduleInstance)
					continue;
				echo '
				<h4><img src="../modules/'.$module['name'].'/logo.gif" /> <a href="index.php?tab='.$module['hook'].'&token='.Tools::getAdminToken($module['hook'].intval(Tab::getIdFromClassName($module['hook'])).intval($cookie->id_employee)).'&module='.$module['name'].'">'.$moduleInstance->displayName.'</a></h4>';
		}
		else
			echo $this->l('No module installed', 'AdminStatsTab');
		echo '</fieldset><div class="clear space">&nbsp;</div>';
	}
	
	public function display()
	{
		echo '<div style="float:left">';
		$this->displayCalendar();
		$this->displayEngines();
		$this->displayMenu();
		$this->displaySearch();
		echo '</div>
		<div style="float:left; margin-left:20px;">';
		
		if (!($moduleName = Tools::getValue('module')))
		{
			$module = $this->getModules(true);
			if (isset($module['name']))
				$moduleName = $module['name'];
			else
				echo Tools::displayError('No module available');
		}
		if ($moduleName)
		{
			// Needed for the graphics display when this is the default module
			$_GET['module'] = $moduleName;
			$moduleInstance = Module::getInstanceByName($moduleName);
			if ($moduleInstance)
				echo Module::hookExec(Tools::getValue('tab'), NULL, $moduleInstance->id);
			else
				echo $this->l('Module not found', 'AdminStatsTab');
		}
		echo '</div><div class="clear"></div>';
	}
}

?>
