<?php

if (!class_exists('Mother'))
  require_once KW_ROOT_DIR . '/kernel/Mother.class.php';

if (!class_exists('XMLElement'))
  require_once KW_ROOT_DIR . '/kernel/XMLElement.class.php';

if (!class_exists('spyc'))
  require_once KW_ROOT_DIR . '/kernel/spyc.php';

if (!class_exists('KwixoSocket'))
  require_once KW_ROOT_DIR . '/kernel/KwixoSocket.class.php';

if (!class_exists('Service'))
  require_once KW_ROOT_DIR . '/kernel/Service.class.php';

if (!class_exists('HashMD5')) {
  //serveur 32 bits
  if (PHP_INT_SIZE == 4) {
    require_once KW_ROOT_DIR . '/kernel/fianet_key_32bits.php';
  }
  //seveur 64 bits
  if (PHP_INT_SIZE == 8) {
    require_once KW_ROOT_DIR . '/kernel/fianet_key_64bits.php';
  }
}

if (!class_exists('Form'))
  require_once KW_ROOT_DIR . '/kernel/Form.class.php';

if (!class_exists('FormField'))
  require_once KW_ROOT_DIR . '/kernel/FormField.class.php';

if (!class_exists('FormFieldInputImage'))
  require_once KW_ROOT_DIR . '/kernel/FormFieldInputImage.class.php';