<?php

class AdminAddonsMyAccount extends AdminTab
{
	public function display()
	{
		$parentDomain = Tools::getHttpHost(true).substr($_SERVER['REQUEST_URI'], 0, -1 * strlen(basename($_SERVER['REQUEST_URI'])));
		echo '<iframe frameborder="no" style="margin:0px;padding:0px;width:100%;height:920px" src="http://preprod.prestastore.com/iframe/myaccount.php?parentUrl='.$parentDomain.'"></iframe>
		<div class="clear">&nbsp;</div>';
	}
}

?>
