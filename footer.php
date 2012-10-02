<?php $controller = new FrontController();
/* PrestaShop Mobile */ if (_THEME_NAME_ == 'prestashop_mobile') { global $smarty; $smarty->display(_PS_THEME_DIR_.'footer-page.tpl'); $smarty->assign('no_footer', 1); }
$controller->displayFooter();