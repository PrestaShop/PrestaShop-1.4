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
  
class GridExtJS extends ModuleGridEngine
{
	private $_values;
	private $_totalCount;
	private $_title;
	private $_width;
	private $_height;

	function __construct($type = null)
	{
		if ($type != null)
		{
			parent::__construct($type);
		}
		else
		{
			$this->name = 'gridextjs';
			$this->tab = 'Stats Engines';
			$this->version = 1.0;
			
			Module::__construct();
			
			$this->displayName = $this->l('ExtJS');
			$this->description = $this->l('ExtJS is a library which enables buiding rich internet application using Javascript.');
		}
	}
	
	function install()
	{
		return (parent::install() AND $this->registerHook('GridEngine'));
	}
	
	public static function hookGridEngine($params, $grider)
	{
		if (!isset($params['emptyMsg']))
			$params['emptyMsg'] = 'Empty';

		$html = '
<script type="text/javascript" src="../modules/gridextjs/extjs/ext-grid-only.js"></script>
<script type="text/javascript">
//<![CDATA[
Ext.onReady(function(){
   var store = new Ext.data.Store({ 
        proxy: new Ext.data.HttpProxy({
            url:"'.$grider.'"
        }),
        reader: new Ext.data.JsonReader({
            root:"extjsgrid",
            totalProperty:"totalCount",
			id:"'.$params['id'].'",
			fields:[';
		foreach ($params['columns'] as $index => $column)
		{
			if ($index > 0)
				$html .= ',';
			$html .= '"'.$column['dataIndex'].'"';
		}
		$html .= ']
        }),
        remoteSort: true
    });
    store.setDefaultSort("'.$params['defaultSortColumn'].'", "DESC");
	
var cm = new Ext.grid.ColumnModel([';
	
		foreach ($params['columns'] as $index => $column)
		{
			if ($index > 0)
				$html .= ',';
			$html .= '{';
			$i = 0;
			foreach ($column as $key => $value)
			{
				if ($i++ > 0)
					$html .= ',';
				$html .= $key.':'.(is_int($value) ? $value : '"'.$value.'"');
			}
			$html .= '}';
		}
		$html .= ']);

    cm.defaultSortable = true;

    var grid = new Ext.grid.GridPanel({
        el:"extjsgrid",
        width:'.$params['width'].',
        height:'.$params['height'].',
        title:"'.$params['title'].'",
        store: store,
        cm: cm,
        trackMouseOver:false,
        sm: new Ext.grid.RowSelectionModel({selectRow:Ext.emptyFn}),
        loadMask: true,
        viewConfig: {
            forceFit:true,
            enableRowBody:true
        },		
		bbar: new Ext.PagingToolbar({
            pageSize: '.$params['limit'].',
            store: store,
            displayInfo: true,
            displayMsg: "'.$params['pagingMessage'].'",
            emptyMsg: "'.$params['emptyMsg'].'"
        })
    });

    grid.render();
    store.load({params:{start:'.$params['start'].', limit:'.$params['limit'].'}});
});';
		$html .= '
//]]>
</script>
<div id="extjsgrid"></div>';
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
		$json = array
		(
			'totalCount' => $this->_totalCount,
			'extjsgrid' => $this->_values
		);
	
		echo json_encode($json);
	}	
}
?>
