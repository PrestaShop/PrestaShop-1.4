<?php

/**
  * Admin panel footer, footer.inc.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.4
  *
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

?>