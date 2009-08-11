<?php

class	AddConfToFile
{
	public $fd;
	public $file;
	public $mode;
	public $error = false;
	
	public function __construct($file, $mode = 'r+')
	{
		$this->file = $file;
		$this->mode = $mode;
		$this->checkFile($file);
		if ($mode == 'w' AND !$this->error)
			if (!$res = @fwrite($this->fd, '<?php'."\n"))
				$this->error = 6;
	}
	
	public function __destruct()
	{
		if (!$this->error)
			@fclose($this->fd);
	}
	
	private function checkFile($file)
	{
		if (!$fd = @fopen($this->file, $this->mode))
			$this->error = 5;
		elseif (!is_writable($this->file))
			$this->error = 6;
		$this->fd = $fd;
	}
	
	public function writeInFile($name, $data)
	{
		if (!$res = @fwrite($this->fd,
			'define(\''.$name.'\', \''.$this->checkString($data).'\');'."\n"))
		{
			$this->error = 6;
			return false;
		}
		return true;
	}
	
	public function writeEndTagPhp()
	{
		if (!$res = @fwrite($this->fd, '?>'."\n")) {
			$this->error = 6;
			return false;
		}
		return true;
	}
	
	public function checkString($string)
	{
		if (get_magic_quotes_gpc())
			$string = stripslashes($string);
		if (!is_numeric($string))
		{
			$string = addslashes($string);
			$string = strip_tags(nl2br($string));
		}
		return $string;
	}
}
?>