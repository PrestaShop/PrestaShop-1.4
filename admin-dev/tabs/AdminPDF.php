<?php
/*
* 2007-2010 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  2007-2010 Prestashop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

include_once(PS_ADMIN_DIR.'/tabs/AdminPreferences.php');

class AdminPDF extends AdminPreferences
{
	public function __construct()
	{
		global $cookie;

		$lang = strtoupper(Language::getIsoById($cookie->id_lang));
		$this->className = 'Configuration';
		$this->table = 'configuration';

		/* Collect all font files and build array for combo box */
		$fontFiles = scandir(_PS_FPDF_PATH_.'font');
		$fontList = array();
		$arr = array();
		
		foreach ($fontFiles AS $file)
			if (substr($file, -4) == '.php' AND $file != 'index.php')
			{
				$arr['mode'] = substr($file, 0, -4);
				$arr['name'] = substr($file, 0, -4);
				array_push($fontList, $arr);
			}

		/* Collect all encoding map files and build array for combo box */
		$encodingFiles = scandir(_PS_FPDF_PATH_.'font/makefont');
		$encodingList = array();
		$arr = array();
		foreach ($encodingFiles AS $file)
			if (substr($file, -4) == '.map')
			{
				$arr['mode'] = substr($file, 0, -4);
				$arr['name'] = substr($file, 0, -4);
				array_push($encodingList, $arr);
			}

 		$this->_fieldsPDF = array(
			'PS_PDF_ENCODING_'.$lang => array(
				'title' => $this->l('Encoding:'),
				'desc' => $this->l('Encoding for PDF invoice'),
				'type' => 'select',
				'cast' => 'strval',
				'identifier' => 'mode', 
				'list' => $encodingList),
			'PS_PDF_FONT_'.$lang => array(
				'title' => $this->l('Font:'),
				'desc' => $this->l('Font for PDF invoice'),
				'type' => 'select',
				'cast' => 'strval',
				'identifier' => 'mode', 
				'list' => $fontList)
		);

		parent::__construct();
	}

	

	public function postProcess()
	{
		if (isset($_POST['submitPDF'.$this->table]))
		{
		 	if ($this->tabAccess['edit'] === '1')
				$this->_postConfig($this->_fieldsPDF);
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
	}	

	public function display()
	{
		global $cookie;

		$language = new Language((int)($cookie->id_lang));
		if (!Validate::isLoadedObject($language))
			die(Tools::displayError());
		$this->_displayForm('PDF', $this->_fieldsPDF, $this->l('PDF settings for the current language:').' '.$language->name, 'width2', 'pdf');
	}
}
