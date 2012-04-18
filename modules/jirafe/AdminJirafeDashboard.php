<?php

include_once('jirafe.php');

if (version_compare(_PS_VERSION_, '1.5') >= 0) {
    require_once 'AdminJirafeDashboard15.php';
} elseif (version_compare(_PS_VERSION_, '1.4') >= 0) {
    require_once 'AdminJirafeDashboard14.php';
}
