<?php
class WebserviceExceptionCore extends Exception
{
	protected $status;
	protected $wrong_value;
	protected $available_values;
	protected $type;
	
	const SIMPLE = 0;
	const DID_YOU_MEAN = 1;
	
	public function __construct($message, $code)
	{
		$exception_code = $code;
		if (is_array($code))
		{
			$exception_code = $code[0];
			$this->setStatus($code[1]);
		}
		parent::__construct($message, $exception_code);
		$this->type = self::SIMPLE;
	}
	public function getType()
	{
		return $this->type;	
	}
	public function setType($type)
	{
		$this->type = $type;
		return $this;	
	}
	public function setStatus($status)
	{
		if (Validate::isInt($status))
			$this->status = $status;
		return $this;
	}
	public function getStatus()
	{
		return $this->status;
	}
	public function getWrongValue()
	{
		return $this->wrong_value;
	}
	public function setDidYouMean($wrong_value, $available_values)
	{
		$this->type = self::DID_YOU_MEAN;
		$this->wrong_value = $wrong_value;
		$this->available_values = $available_values;
		return $this;
	}
	public function getAvailableValues()
	{
		return $this->available_values;
	}
}