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
*  @copyright  2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

echo '			</div>
			</div>
			'.Module::hookExec('backOfficeFooter').'
			<div id="footer">
				<div style="float:left;margin-left:10px;padding-top:6px">
					<a href="http://www.prestashop.com/" target="_blank" style="font-weight:700;color:#666666">PrestaShop&trade; '._PS_VERSION_.'</a><br />
					<span style="font-size:10px">'.translate('Load time:').' '.number_format(microtime(true) - $timerStart, 3, '.', '').'s</span>
				</div>
				<div style="float:right;height:40px;margin-right:10px;line-height:38px;vertical-align:middle">
					<a href="http://www.prestashop.com/bug_tracker/" target="_blank" class="footer_link">Bug Tracker</a>
					| <a href="http://www.prestashop.com/forums/" target="_blank" class="footer_link">Forum</a>
					| <a href="http://www.prestashop.com/en/contact_us/" target="_blank" class="footer_link">Contact</a>
				</div>
			</div>
		</div>
	</div>
	</body>
</html>';

