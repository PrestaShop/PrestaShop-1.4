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
  
class DateRange extends ObjectModel
{	
	public $time_start;
	public $time_end;

	protected	$fieldsRequired = array ('time_start', 'time_end');	
	protected	$fieldsValidate = array ('time_start' => 'isDate', 'time_end' => 'isDate');

	protected 	$table = 'date_range';
	protected 	$identifier = 'id_date_range';
	
	public function getFields()
	{
		parent::validateFields();
		$fields['time_start'] = pSQL($this->time_start);
		$fields['time_end'] = pSQL($this->time_end);
		return $fields;
	}
	
	public static function getCurrentRange()
	{
		$result = Db::getInstance()->getRow('
		SELECT `id_date_range`, `time_end`
		FROM `'._DB_PREFIX_.'date_range`
		WHERE `time_end` = (SELECT MAX(`time_end`) FROM `'._DB_PREFIX_.'date_range`)');
		if (!$result['id_date_range'] OR strtotime($result['time_end']) < strtotime(date('Y-m-d H:i:s')))
		{
			// The default range is set to 1 day less 1 second (in seconds)
			$rangeSize = 86399;
			$dateRange = new DateRange();
			$dateRange->time_start = date('Y-m-d');
			$dateRange->time_end = strftime('%Y-%m-%d %H:%M:%S', strtotime($dateRange->time_start) + $rangeSize);
			$dateRange->add();
			return $dateRange->id;
		}
		return $result['id_date_range'];
	}
	
}

?>
