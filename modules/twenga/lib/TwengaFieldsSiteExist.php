<?php
/**
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
 *  @copyright 2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
 *  @version  Release: $Revision: 1.4 $
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 **/

/**
 * @author Nans Pellicari - Prestashop
 * @version 1.0
 */
class TwengaFieldsSiteExist extends TwengaFields
{
    public function __construct()
    {
        if(!is_array($this->fields) AND empty($this->fields))
        {
            $this->fields['key'] = array(32, array('isString', 'isCleanHtml'), true);
//            $this->fields['PARTNER_AUTH_KEY'] = array(56, array('isString', 'isCleanHtml'), true);
        }
        parent::__construct();
    }   
}