<?php
interface WebserviceSpecificManagementInterface
{
	public function setObjectOutput(WebserviceOutputBuilderCore $obj);
	public function getObjectOutput();
	public function setWsObject(WebserviceRequestCore $obj);
	public function getWsObject();
	
	public function manage();
	
	/**
	 * This must be return an array with specific values as WebserviceRequest expects.
	 * 
	 * @return array
	 */
	public function getContent();
}