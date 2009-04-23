<?php

/**
  * Backup management tab for admin panel, AdminBackup.php
  * @category admin
  *
  * @author Andrew Brampton
  * @copyright Andrew Brampton
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 0.1
  *
  * @todo When exporting data, ExecuteS is used which returns a large array. I would prefer Db supported a way to retreive one row at a time
  * @todo Abstract some of the methods into the Db class, for example, listing table names, exporting schema
  * @todo Add a import backup option
  * @todo Make the filters work
  */
  
include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminBackup extends AdminTab
{

	/** @var string The field we are sorting on */
	protected $_sortBy = 'date';

	public function __construct()
	{
		$this->table = 'backup';
		$this->className = 'Backup';
		parent::__construct();

	 	$this->edit = false;
	 	$this->delete = true;
	 	$this->view = true;

	 	$this->deleted = false;

		$this->fieldsDisplay = array (
			'date'     => array('title' => $this->l('Date'), 'type' => 'datetime', 'width' => 120),
			'age'      => array('title' => $this->l('Age')),
			'filename' => array('title' => $this->l('Filename'), 'width' => 200),
			'filesize' => array('title' => $this->l('File size')),
		);

		$this->identifier = 'filename';
	}

	/**
	 * Load class object using identifier in $_GET (if possible)
	 * otherwise return an empty object
	 * This method overrides the one in AdminTab because AdminTab assumes the id is a UnsignedInt
	 *
	 * @param boolean $opt Return an empty object if load fail
	 * @return object
	 */
	protected function loadObject($opt = false)
	{
		if ($id = Tools::getValue($this->identifier))
			return new $this->className($id);
		return new $this->className();
	}

	/**
	 * Creates a new backup, and then displays the normal menu
	 */
	public function displayForm()
	{
		$object = $this->loadObject();
		if ($object->add())
		{
			echo '<div class="conf confirm"><img src="../img/admin/ok.gif" />&nbsp;'.$this->l('Back-up Creation successful').' !</div>';
			if ($this->tabAccess['view'] === '1')
				echo '<br />'.$this->l('You can now').' <b><a href="'.$object->getBackupURL().'">'.$this->l('download the back-up file').'</a></b>.';
			echo '<br />';
		}
		elseif ($object->error)
			$this->_errors[] = $object->error;
		$this->displayErrors();
	}

	/**
	 * Displays the page which allows the backup to be downloaded
	*/
	public function viewbackup()
	{
		global $currentIndex;

		$object = $this->loadObject();
		if ($object->id)
		{
			$url = $object->getBackupURL();
			echo '<div class="conf confirm"><img src="../img/admin/ok.gif" />&nbsp;'.$this->l('Beginning download ...').'</div>';
			echo '<br />'.$this->l('Back-up file should automatically download.');
			echo '<br /><br />'.$this->l('If not,').' <b><a href="'.$url.'">'.$this->l('please click here!').'</a></b>';
			echo '<iframe width="0" height="0" scrolling="no" frameborder="0" src="'.$url.'"></iframe>';
			echo '<br /><br /><br /><a href="'.$currentIndex.'&token='.$this->token.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to list').'</a><br />';
		}
		elseif ($object->error)
			$this->_errors[] = $object->error;
		$this->displayErrors();
	}

	public function displayList()
	{
		global $currentIndex;

		$this->displayErrors();
		echo '<br /><a href="'.$currentIndex.'&add'.$this->table.'&token='.$this->token.'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Create new back-up').'</a><br /><br />';
		parent::displayList();
	}

	public function getList($id_lang, $orderBy = NULL, $orderWay = NULL, $start = 0, $limit = NULL)
	{
		global $cookie;
		
		if (!Validate::isTableOrIdentifier($this->table))
			die('filter is corrupted');
		if (empty($orderBy))
			$orderBy = Tools::getValue($this->table.'Orderby', $this->_defaultOrderBy);
		if (empty($orderWay))
			$orderWay = Tools::getValue($this->table.'Orderway', 'ASC');

		// Try and obtain getList arguments from $_GET
		$orderBy = Tools::getValue($this->table.'Orderby');
		$orderWay = Tools::getValue($this->table.'Orderway');

		// Validate the orderBy and orderWay fields
		switch ($orderBy)
		{
			case 'filename':
			case 'filesize':
			case 'date':
			case 'age':
				break;
			default:
				$orderBy = 'date';
		}
		switch ($orderWay)
		{
			case 'asc':
			case 'desc':
				break;
			default:
				$orderWay = 'desc';
		}
		if (empty($limit))
			$limit = ((!isset($cookie->{$this->table.'_pagination'})) ? $this->_pagination[0] : $limit = $cookie->{$this->table.'_pagination'});
		$limit = intval(Tools::getValue('pagination', $limit));
		$cookie->{$this->table.'_pagination'} = $limit;

		/* Determine offset from current page */
		if (!empty($_POST['submitFilter'.$this->table]) AND	is_numeric($_POST['submitFilter'.$this->table]))
			$start = intval($_POST['submitFilter'.$this->table] - 1) * $limit;
		$this->_lang = intval($id_lang);
		$this->_orderBy = $orderBy;	
		$this->_orderWay = strtoupper($orderWay);
		$this->_list = array();

		// Find all the backups
		$dh = @opendir(PS_ADMIN_DIR.'/backups/');
		if ($dh === false)
		{
			$this->_errors[] = Tools::displayError('Unable to open backup directory "').addslashes(PS_ADMIN_DIR.'/backups/').'"';
			return;
		}
		while (($file = readdir($dh)) !== false)
		{
			if (preg_match('/^([\d]+-[a-z\d]+)\.sql(\.gz|\.bz2)?$/', $file, $matches) == 0)
				continue;
			$timestamp = intval($matches[1]);
			$date = date('Y-m-d h:i:s', $timestamp);
			$age = time() - $timestamp;
			if ($age < 3600)
				$age = '< 1 '.$this->l('hour');
			else if ($age < 86400)
			{
				$age = floor($age / 3600);
				$age = $age.' '.(($age == 1) ? $this->l('hour') : $this->l('hours'));
			}
			else
			{
				$age = floor($age / 86400);
				$age = $age.' '.(($age == 1) ? $this->l('day') : $this->l('days'));
			}
			$size = filesize(PS_ADMIN_DIR.'/backups/'.$file);
			$this->_list[] = array(
				'filename' => $file, 
				'age' => $age,
				'date' => $date,
				'filesize' => number_format($size / 1000, 2).' Kb',
				'timestamp' => $timestamp,
				'filesize_sort' => $size,
			);
		}
		closedir($dh);
		$this->_listTotal = count($this->_list);

		// Sort the _list based on the order requirements
		switch ($this->_orderBy)
		{
			case 'filename':
				$this->_sortBy = 'filename';
				$sorter = 'str_sort';
				break;
			case 'filesize':
				$this->_sortBy = 'filesize_sort';
				$sorter = 'int_sort';
				break;
			case 'age':
			case 'date':
				$this->_sortBy = 'timestamp';
				$sorter = 'int_sort';
				break;
		}
		usort($this->_list, array($this, $sorter));
		$this->_list = array_slice($this->_list, $start, $limit);
	}
	

	public function displayErrors()
	{
		static $seenErrors = false;
		if (!$seenErrors)
		{
			$seenErrors = true;
			parent::displayErrors();
		}
	}
	
	public function int_sort($a, $b)
	{
		if ($this->_orderWay == 'ASC')
			return $a[$this->_sortBy] - $b[$this->_sortBy];
		else
			return $b[$this->_sortBy] - $a[$this->_sortBy];
	}
	
	public function str_sort($a, $b)
	{
		if ($this->_orderWay == 'ASC')
			return strcmp ($a[ $this->_sortBy], $b[$this->_sortBy]);
		else
			return strcmp ($b[ $this->_sortBy], $a[$this->_sortBy]);
	}

}

?>