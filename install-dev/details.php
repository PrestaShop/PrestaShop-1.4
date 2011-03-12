<?php

include(dirname(__FILE__).'/../config/config.inc.php');

echo '<a title="'.Configuration::get('PS_UPGRADE_CURRENT_SQL').'" style="cursor: pointer;">'.substr(Configuration::get('PS_UPGRADE_CURRENT_SQL'), 0, 65).'...</a>';

?>