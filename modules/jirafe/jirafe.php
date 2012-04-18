<?php

if (!defined('_PS_VERSION_') || !defined('_CAN_LOAD_FILES_'))
    exit;

// Set to true if you want to debug the Jirafe API in the test sandbox
define ('JIRAFE_DEBUG', false);

// plugin version
define ('JIRAFE_MODULE_VERSION', '0.1');

if (version_compare(_PS_VERSION_, '1.5') >= 0) {
    require_once 'jirafe15.php';
} elseif (version_compare(_PS_VERSION_, '1.4') >= 0) {
    require_once 'jirafe14.php';
}
