<?php

if (!class_exists('KControl'))
  require_once KW_ROOT_DIR . '/common/Control.class.php';

if (!class_exists('Utilisateur'))
  require_once KW_ROOT_DIR . '/common/Utilisateur.class.php';

if (!class_exists('Siteconso'))
  require_once KW_ROOT_DIR . '/common/Siteconso.class.php';

if (!class_exists('Adresse'))
  require_once KW_ROOT_DIR . '/common/Adresse.class.php';

if (!class_exists('Appartement'))
  require_once KW_ROOT_DIR . '/common/Appartement.class.php';

if (!class_exists('Infocommande'))
  require_once KW_ROOT_DIR . '/common/Infocommande.class.php';

if (!class_exists('Transport'))
  require_once KW_ROOT_DIR . '/common/Transport.class.php';

if (!class_exists('Pointrelais'))
  require_once KW_ROOT_DIR . '/common/Pointrelais.class.php';

if (!class_exists('ProductList'))
  require_once KW_ROOT_DIR . '/common/ProductList.class.php';

if (!class_exists('Paiement'))
  require_once KW_ROOT_DIR . '/common/Paiement.class.php';

if (!class_exists('XMLResult'))
    require_once KW_ROOT_DIR . '/common/XMLResult.class.php';