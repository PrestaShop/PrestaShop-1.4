<?php

/**
  * Admin panel header, header.inc.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

// P3P Policies (http://www.w3.org/TR/2002/REC-P3P-20020416/#compact_policies)
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0'); // HTTP/1.1
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

require_once(dirname(__FILE__).'/init.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link type="text/css" rel="stylesheet" href="../js/jquery/datepicker/datepicker.css" />
		<link type="text/css" rel="stylesheet" href="../modules/gridextjs/extjs/resources/css/ext-all.css" />
		<link type="text/css" rel="stylesheet" href="../css/admin.css" />
		<title>PrestaShop&trade; - <?php echo translate('Administration panel') ?></title>
		<script type="text/javascript">
			var search_texts = Array('<?php echo translate('product, category...'); ?>', '<?php echo translate('customer id, name, e-mail...'); ?>', '<?php echo translate('order id'); ?>', '<?php echo translate('invoice id'); ?>', '<?php echo translate('cart id'); ?>');
			var helpboxes = <?php echo Configuration::get('PS_HELPBOX'); ?>;
		</script>
		<script type="text/javascript" src="<?php echo _PS_JS_DIR_ ?>jquery/jquery-1.2.6.pack.js"></script>
		<script type="text/javascript" src="../js/admin.js"></script>
		<script type="text/javascript" src="../js/toggle.js"></script>
		<script type="text/javascript" src="../js/tools.js"></script>
		<script type="text/javascript" src="../js/ajax.js"></script>
		<link rel="shortcut icon" href="../img/favicon.ico" />
	</head>
	<body>
		<div id="container">
			<div style="float: left; margin-top: 11px;">
				<form action="index.php?tab=AdminSearch&token=<?php echo Tools::getAdminToken('AdminSearch'.intval(Tab::getIdFromClassName('AdminSearch')).intval($cookie->id_employee)) ?>" method="post">
					<input type="text" name="bo_query" id="bo_query" style="width: 120px;" value="<?php echo (isset($_POST['bo_query']) ? Tools::safeOutput(Tools::stripslashes($_POST['bo_query'])) : ''); ?>" /> <?php translate('in') ?>
					<select name="bo_search_type" id="bo_search_type" onchange="queryType();" style="font-size: 1em;">
						<option value="1"<?php echo (isset($_POST['bo_search_type']) AND
						($_POST['bo_search_type'] == 1)) ? ' selected="selected"' : '' ?>><?php echo translate('catalog') ?></option>
						<option value="2"<?php echo (isset($_POST['bo_search_type']) AND
						($_POST['bo_search_type'] == 2)) ? ' selected="selected"' : '' ?>><?php echo translate('customers') ?></option>
						<option value="3"<?php echo (isset($_POST['bo_search_type']) AND (
						$_POST['bo_search_type'] == 3)) ? ' selected="selected"' : '' ?>><?php echo translate('orders') ?></option>
						<option value="4"<?php echo (isset($_POST['bo_search_type']) AND (
						$_POST['bo_search_type'] == 4)) ? ' selected="selected"' : '' ?>><?php echo translate('invoices') ?></option>
						<option value="5"<?php echo (isset($_POST['bo_search_type']) AND (
						$_POST['bo_search_type'] == 5)) ? ' selected="selected"' : '' ?>><?php echo translate('carts') ?></option>
					</select>&nbsp;
					<input type="submit" name="bo_search" value="<?php echo translate('Search') ?>" class="button" />
					<script type="text/javascript">queryType();</script>
				</form>
			</div>
			<div style="float: left;margin: 11px 0px 0px 50px;" id="flagsLanguage">
				<div>
				<?php
				$link = new Link();
				$languages = Language::getLanguages();
				$i = 0;
				if (sizeof($languages) != 1)
					foreach ($languages AS $language)
					{
						echo '<a href="'.$link->getLanguageLinkAdmin($language['id_lang'], $language['name']).'&adminlang=1"><img src="'._PS_IMG_.'l/'.$language['id_lang'].'.jpg" alt="'.strtoupper($language['iso_code']).'" title="'.$language['name'].'" '.($language['id_lang'] == $cookie->id_lang ? 'class="selected_language"' : '').' /></a> ';
						if ($i == 4)
							echo '</div><div style="margin-top:5px;">';
						$i++;
					}
				?>
				</div>
			</div>
			<script type="text/javascript">$('#flagsLanguage img[class!=selected_language]').css('opacity', '0.3')</script>
			<div style="float: right; margin: 11px 0px 0px 20px; text-align:right;">
				<img src="../img/admin/quick.gif" style="margin-top:5px;" />&nbsp;
				<select onchange="if (this.value == '0') return ; else if (this.value.substr(-1) == 0) document.location = this.value.substr(0, this.value.length - 1); else window.open(this.value.substr(0, this.value.length - 1), 'PrestaShop', '');" style="font-size: 1em; margin:5px 20px 0px 0px;">
					<?php
						global $cookie;
						$quicks = QuickAccess::getQuickAccesses(intval($cookie->id_lang));
						echo '<option value="0">'.translate('Quick access').'</option>';
						echo '<option value="0">---</option>';
						foreach ($quicks AS $quick)
						{
							preg_match('/tab=(.+)(&.+)?$/', $quick['link'], $adminTab);
							if (isset($adminTab[1]))
							{
								if (strpos($adminTab[1], '&'))
									$adminTab[1] = substr($adminTab[1], 0, strpos($adminTab[1], '&'));
								$quick['link'] .= '&token='.Tools::getAdminToken($adminTab[1].intval(Tab::getIdFromClassName($adminTab[1])).intval($cookie->id_employee));
							}
							echo '<option value="'.$quick['link'].intval($quick['new_window']).'">'.Category::hideCategoryPosition($quick['name']).'</option>';
						}
					?>
				</select>
				<img src="../img/admin/nav-user.gif" alt="<?php echo translate('user') ?>" />&nbsp;
				<a href="index.php?logout" title="<?php echo translate('logout') ?>">
					<b><?php echo Tools::substr($cookie->firstname, 0, 1).'.&nbsp;'.htmlentities(Tools::strtoupper($cookie->lastname), ENT_COMPAT, 'UTF-8'); ?></b>
					<img src="../img/admin/nav-logout.gif" alt="<?php echo translate('logout') ?>" />
				</a>
			</div>
			<br style="clear:both;" />
			<ul id="menu" style="margin-top:20px">
				<?php
					global $cookie;

					/* Get current tab informations */
					$id_parent_tab_current = intval(Tab::getCurrentParentId());
					$tabs = Tab::getTabs(intval($cookie->id_lang), 0);
					foreach ($tabs AS $t)
					{
						if ($t['class_name'] == $tab)
							$id_parent = $t['id_tab'];
						if (checkTabRights($t['id_tab']) === true)
						{
							$img = '../img/t/'.$t['class_name'].'.gif';
							if (trim($t['module']) != '')
								$img = _MODULE_DIR_.$t['module'].'/'.$t['class_name'].'.gif';
							echo '
							<li'.((($t['class_name'] == $tab) OR ($id_parent_tab_current == $t['id_tab'])) ? ' class="active"' : '').'>
								<a href="index.php?tab='.$t['class_name'].'&token='.Tools::getAdminToken($t['class_name'].intval($t['id_tab']).intval($cookie->id_employee)).'"><img src="'.$img.'" alt="" style="width:16px;height:16px" /> '.$t['name'].'</a>
							</li>';
						}
					}
				?>
			</ul>
			<div id="main">
				<ul id="submenu">
				<?php
					global $cookie;

					/* Display tabs belonging to opened tab */
					$id_parent = isset($id_parent) ? $id_parent : $id_parent_tab_current;
					if (isset($id_parent) AND $id_parent != -1)
					{
					 	$subTabs = Tab::getTabs(intval($cookie->id_lang), intval($id_parent));
						foreach ($subTabs AS $t)
							if (checkTabRights($t['id_tab']) === true)
								echo '
								<li>
									<a href="index.php?tab='.$t['class_name'].'&token='.Tools::getAdminToken($t['class_name'].intval($t['id_tab']).intval($cookie->id_employee)).'"><img src="../img/t/'.$t['class_name'].'.gif" alt="" style="width:16px;height:16px" /></a> <a href="index.php?tab='.$t['class_name'].'&token='.Tools::getAdminToken($t['class_name'].intval($t['id_tab']).intval($cookie->id_employee)).'">'.$t['name'].'</a>
								</li>';
					}
				?>
				</ul>
				<div id="content">
