<?php

define('PS_ADMIN_DIR', getcwd());

include(PS_ADMIN_DIR.'/../config/config.inc.php');
$cookie = new Cookie('psAdmin');
Tools::setCookieLanguage();

$translations = array(
	'FR' => array(
		'Referer is missing' => 'Vous devez indiquer un "referer"',
		'Confirmation' => 'Confirmation',
		'Yes' => 'Oui',
		'No' => 'Non',
		'close'	=> 'fermer')
);

if (!Tools::getValue('referer')):
	echo '<p>'.Tools::historyc_l('Referer is missing', $translations).'</p>';
	echo '<p><a href="#" onclick="tb_remove()">'.Tools::historyc_l('close', $translations).'</a></p>';
else:
	$referer = rawurldecode(Tools::getValue('referer'));

?>

<h2><?php echo Tools::historyc_l('Confirmation', $translations) ?></h2>
<p>
	<a href="#" class="thickbox confirm_yes" title="" onclick="tb_remove(); window.open('<?php echo $referer ?>', '_self')">
		<?php echo Tools::historyc_l('Yes', $translations) ?>
	</a>
	<a href="#" class="confirm_no" onclick="tb_remove()"><?php echo Tools::historyc_l('No', $translations) ?></a>
</p>

<?php endif; //check if referer exists  ?>