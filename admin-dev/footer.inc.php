<?php

/**
  * Admin panel footer, footer.inc.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

if (!defined('_PS_VERSION_'))
	exit();
ob_flush();
?>

				</div>
			</div>
			<p id="footer">
				Powered by <a href="http://www.prestashop.com/" target="_blank">PrestaShop&trade;</a>&nbsp;[&nbsp;<a href="http://www.prestashop.com/forums/" target="_blank">forum</a>&nbsp;&amp;&nbsp;<a href="http://www.prestashop.com/en/contact_us/" target="_blank">contact</a>&nbsp;]
				- Version <?php echo _PS_VERSION_; ?>
				- <?php echo number_format(microtime(true) - $timerStart, 3, '.', ''); ?>s
			</p>
		</div>
	</body>
</html>
<?php
	//ob_end_flush();
?>