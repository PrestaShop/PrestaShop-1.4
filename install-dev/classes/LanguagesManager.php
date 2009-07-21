<?php

include_once("../classes/Validate.php");

class LanguageManager {
	
	private $url_xml;
	private $lang;
	private $xml_file;
	
	function __construct ($url_xml){
		$this->loadXML($url_xml);
		$this->setLanguage();
		$this->getIncludeTradFilename();
		
	}
	
	
	private function loadXML($url_xml){
		global $errors;
		if(!$this->xml_file = simplexml_load_file($url_xml))
			$errors = "Error when loading XML language file : $url_xml";
	}
	
	public function getIdSelectedLang(){
		return $this->lang['id'];
	}
	
	public function getIsoCodeSelectedLang(){
		return $this->lang->idLangPS;
	}
	
	public function countLangs(){
		return sizeof($this->xml_file);
	}
	
	public function getAvailableLangs(){
		return $this->xml_file;
	}
	
	public function getSelectedLang(){
		return $this->lang;
	}
	
	private function getIdByHAL(){
		
		$iso = false;
		
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			
			$FirstHAL = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			$iso = $FirstHAL[0];
			
			if ( $iso != "en-us" ) {
				foreach ($this->xml_file as $lang){
					foreach ($lang->isos->iso as $anIso){
						if ($anIso == $iso) return $lang['id'];
					}
				}
			}
			
		} else return 0;
		
	}
	
	private function setLanguage(){
		if( isset($_GET['language']) AND Validate::isInt($_GET['language'])){
			$id_lang = intval($_GET['language']);
		}
		if (!isset($id_lang)) {
			$id_lang = ($this->getIdByHAL());
		}
		
		$this->lang = $this->xml_file->lang[intval($id_lang)];
		
		
	}
	
	public function getIncludeTradFilename(){
		return ($this->lang == NULL) ? false : dirname(__FILE__).$this->lang['trad_file'];
	}
	
} 

?>



