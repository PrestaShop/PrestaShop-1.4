<?php

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class GridHtml extends ModuleGridEngine
{
	private $_values;
	private static $_columns;

	function __construct($type = null)
	{
		if ($type != null)
			parent::__construct($type);
		else
		{
			$this->name = 'gridhtml';
			$this->tab = 'administration';
			$this->version = 1.0;
			
			Module::__construct();
			
			$this->displayName = $this->l('Simple HTML table display');
			$this->description = '';
		}
	}
	
	function install()
	{
		return (parent::install() AND $this->registerHook('GridEngine'));
	}
	
	public static function hookGridEngine($params, $grider)
	{
		self::$_columns = $params['columns'];
		if (!isset($params['emptyMsg']))
			$params['emptyMsg'] = 'Empty';

		$html = '<div style="width:'.$params['width'].'px;height:'.$params['height'].'px;overflow:scroll">
			<table class="table" cellpadding="0" cellspacing="0" id="grid_1"><thead><tr>';
		foreach ($params['columns'] as $column)
			$html .= '<th style="width:'.$column['width'].'px;cursor:pointer">
						'.$column['header'].'<br />
						<a href="javascript:getGridData(\''.$grider.'&sort='.$column['dataIndex'].'&dir=ASC\');">
							<img src="../img/admin/up.gif" />
						</a>
						<a href="javascript:getGridData(\''.$grider.'&sort='.$column['dataIndex'].'&dir=DESC\');">
							<img src="../img/admin/down.gif" />
						</a>
					</th>';
		$html .= '</tr></thead>
				<tbody></tbody>
			</table>
		</div>
		<script type="text/javascript">
			function getGridData(url)
			{
				$("#grid_1 tbody").html("<tr><td style=\"text-align:center\" colspan=\"" + '.count($params['columns']).' + "\"><img src=\"../img/loadingAnimation.gif\" /></td></tr>");
				$.get(url, "", function(json) {
					$("#grid_1 tbody").html("");
					var array = $.parseJSON(json);
					if (array.length > 0)
						$.each(array, function(index, row){
							var newLine = "<tr>";';
			foreach ($params['columns'] as $column)
				$html .= '	newLine += "<td'.(isset($column['align']) ? ' style=\"text-align:'.$column['align'].'\"' : '').'>" + row["'.$column['dataIndex'].'"] + "</td>";';
			$html .= '		$("#grid_1 tbody").append(newLine);
						});
					else
						$("#grid_1 tbody").append("<tr><td style=\"text-align:center\" colspan=\"" + '.count($params['columns']).' + "\">'.$params['emptyMsg'].'</td></tr>");
				});
			}
			$(document).ready(function(){getGridData("'.$grider.'");});
		</script>';
		return $html;
	}
	
	public function setValues($values)
	{
		$this->_values = $values;
	}
	
	public function setTitle($title)
	{
		$this->_title = $title;
	}
	
	public function setSize($width, $height)
	{
		$this->_width = $width;
		$this->_nlines = $height;
	}
	
	public function setColumnsInfos(&$infos)
	{
	}
	
	public function setTotalCount($totalCount)
	{
		$this->_totalCount = $totalCount;
	}
	
	public function render()
	{
		echo json_encode($this->_values);
	}	
}
?>
