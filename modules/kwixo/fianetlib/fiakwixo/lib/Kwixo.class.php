<?php

/**
 * Classe spécifique du service Kwixo. Contient toutes fonctionnalités, appels de scripts d'envoi, et scripts de retours nécessaires
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class FiaKwixo extends Service {
  const INPUT_TYPE = 'hidden'; //type par défaut des champs du form. text pour le debug, hidden sinon
  const REMOTE_CTRL_ANSWER_TYPE_XML = 'xml';
  const REMOTE_CTRL_ANSWER_TYPE_TXT = 'txt';
  const REMOTE_CTRL_ANSWER_TYPE_URL = 'url';
  const REMOTE_CTRL_MODE_ATOMIC = 'atomic';
  const REMOTE_CTRL_ACTION_TOTAL_CANCEL = 1;
  const REMOTE_CTRL_ACTION_PARTIAL_CANCEL = 3;
  const REMOTE_CTRL_ACTION_VALIDATION = 2;
  const WALLET_VERSION = '1.0';
  const CRYPT_VERSION = '2.0';

  /**
   * génère la date de livraison calculée à partir de la date de commande et du délai de livraison passé en param (délai par défaut si null)
   *
   * @param XMLElement $order
   * @param int $deliverytime délais de livraison en jours
   * @return date date de livraison
   */
  public function generateDatelivr(XMLElement $order, $deliverytime=null) {
    if (is_null($deliverytime))
      $deliverytime = $this->getDefaultdeliverytime();

    $datecom = strtotime(array_pop($order->getChildrenByName('datecom'))->getValue());
    $datelivr = date('Y-m-d', mktime(0, 0, 0, date('m', $datecom), date('d', $datecom) + $deliverytime, date('Y', $datecom)));

    return $datelivr;
  }

  /**
   * génère la valeur du crypt
   *
   * @param XMLElement $order
   * @return string
   */
  public function generateCrypt(XMLElement $order) {
    $MD5 = new HashMD5();

    $montant = array_pop($order->getChildrenByName('montant'))->getValue();
    $email = array_pop(array_pop($order->getChildrenByNameAndAttribute('utilisateur', 'type', 'facturation'))->getChildrenByName('email'))->getValue();
    $refid = array_pop($order->getChildrenByName('refid'))->getValue();
    $nom = array_pop(array_pop($order->getChildrenByNameAndAttribute('utilisateur', 'type', 'facturation'))->getChildrenByName('nom'))->getValue();
    $secondes = preg_replace('#^.+:(\d{2})$#', '$1', array_pop($order->getChildrenByName('datecom'))->getValue());

    $modulo = $secondes % 4;
    switch ($modulo) {
      case 0:
        $select = $montant;
        break;
      case 1:
        $select = $email;
        break;
      case 2:
        $select = $refid;
        break;
      case 3:
        $select = $nom;
        break;
      default:
        break;
    }

    return $MD5->hash($this->getAuthkey() . $refid . $montant . $email . $select);
  }

  /**
   * retourne le statut du serveur Kwixo : OK pour validité de tous les services, KC si crédit RnP non dispo, KO si serveur non dispo
   *
   * @return string valeur du statut
   */
  protected function getServiceStatus() {
    //connexion à l'url du script de validité
    $con = new KwixoSocket($this->getUrlavailable());
    //récupération de la réponse
    $res = $con->send();

    //si le serveur n'a pas répondu on retourne KO
    if ($res === false)
      return 'KO';

    //conscruction d'un XMLElement pour analyse de la réponse
    $xml = new XMLElement($res);
    //retour de l'attribut status indiquant l'état du serveur
    return $xml->getAttribute('status');
  }

  /**
   * retourne vrai si paiement comptant dispo, faux sinon
   *
   * @return bool validité du paiement en comptant
   */
  public function cashAvailable() {
    return $this->getServiceStatus() == 'OK' || $this->getServiceStatus() == 'KC';
  }

  /**
   * retourne vrai si paiement crédit RnP dispo, faux sinon
   *
   * @return bool validité du paiement en comptant
   */
  public function creditAvailable() {
    return $this->getServiceStatus() == 'OK';
  }

  /**
   * retourne le formulaire de soumission du flux vers le script $script Kwixo sous la forme d'une chaine HTML
   *
   * @param string $script nom du script pour la soumission
   * @param XMLElement $order
   * @param mixed $xmlparams paramètres additionnels pour le retour sur urlcall, sous forme de chaine XML ou d'objet XMLElement
   * @param string $urlsys url vers laquelle les retours sont envoyés en PUSH
   * @param string $urlcall url vers laquelle l'internaute est redirigé
   * @param string $submittype type de soumission du formulaire : automatique, manuel avec un bouton (traditionnel), manuel avec un bouton image
   * @param string $imagepath chemin de l'image pour la soumission manuelle avec bouton image
   * @return Form formulaire de soumission
   */
  private function getSubmissionForm($scripturl, XMLElement $order, $xmlparams=null, $urlsys=null, $urlcall=null, $submittype=Form::SUBMIT_STANDARD, $imagepath=null) {
    //si le $xmlparams est un objet XMLElement on en déduit la chaine correspondante
    if (self::isXMLElement($xmlparams))
      $xmlparams = $xmlparams->getXML();

    //définition des différents champs du form
    $fields = array(
        'MerchId' => array('type' => self::INPUT_TYPE, 'name' => 'MerchId', 'value' => $this->getSiteId()),
        'XMLInfo' => array('type' => self::INPUT_TYPE, 'name' => 'XMLInfo', 'value' => preg_replace('#"#', "'", $order->getXML(true))),
        'URLCall' => array('type' => self::INPUT_TYPE, 'name' => 'URLCall', 'value' => $urlcall),
        'URLSys' => array('type' => self::INPUT_TYPE, 'name' => 'URLSys', 'value' => $urlsys),
        'XMLParam' => array('type' => self::INPUT_TYPE, 'name' => 'XMLParam', 'value' => $xmlparams),
    );

    //instanciation du form
    $form = new Form($scripturl, 'submit_kwixo_xml', 'POST', $fields);

    //ajout du submit
    switch ($submittype) {
      case Form::SUBMIT_IMAGE:
        $form->addImageSubmit($imagepath, 'Payer avec Kwixo', 'Payer avec Kwixo', 'Payer avec Kwixo', 'image_sumbit');
        break;

      case Form::SUBMIT_STANDARD:
        $form->addSubmit();
        break;

      case Form::SUBMIT_AUTO:
        $form->setAutosubmit(true);
        break;

      default:
        $msg = "Type submit non reconnu.";
        self::insertLog(__METHOD__ . ' : ' . __LINE__, $msg);
        break;
    }

    return $form;
  }

  /**
   * retourne le formulaire de soumission frontline
   *
   * @param XMLElement $order
   * @param mixed $xmlparams paramètres additionnels pour le retour sur urlcall, sous forme de chaine XML ou d'objet XMLElement
   * @param string $urlsys url vers laquelle les retours sont envoyés en PUSH
   * @param string $urlcall url vers laquelle l'internaute est redirigé
   * @param string $submittype type de soumission du formulaire : automatique, manuel avec un bouton (traditionnel), manuel avec un bouton image
   * @param string $imagepath chemin de l'image pour la soumission manuelle avec bouton image
   * @return Form formulaire de soumission
   */
  public function getTransactionForm(XMLElement $order, $xmlparams=null, $urlsys=null, $urlcall=null, $submittype=Form::SUBMIT_STANDARD, $imagepath=null) {
    return $this->getSubmissionForm($this->getUrlfrontline(), $order, $xmlparams, $urlsys, $urlcall, $submittype, $imagepath);
  }

  /**
   * retourne le formulaire de soumission checkline
   *
   * @param XMLElement $order
   * @param mixed $xmlparams paramètres additionnels pour le retour sur urlcall, sous forme de chaine XML ou d'objet XMLElement
   * @param string $urlsys url vers laquelle les retours sont envoyés en PUSH
   * @param string $urlcall url vers laquelle l'internaute est redirigé
   * @param string $submittype type de soumission du formulaire : automatique, manuel avec un bouton (traditionnel), manuel avec un bouton image
   * @param string $imagepath chemin de l'image pour la soumission manuelle avec bouton image
   * @return Form formulaire de soumission
   */
  public function getChecklineForm(XMLElement $order, $xmlparams=null, $urlsys=null, $urlcall=null, $submittype=Form::SUBMIT_STANDARD, $imagepath=null) {
    return $this->getSubmissionForm($this->getUrlcheckline(), $order, $xmlparams, $urlsys, $urlcall, $submittype, $imagepath);
  }

  /**
   * retourne le dernier tag attributé à la transaction $tid
   *
   * @param string $rid référence marchand
   * @param string $tid référence Kwixo
   * @return string XMLElement construit depuis la réponse du script
   */
  public function getTagline($rid, $tid) {
    $MD5 = new HashMD5();

    $data = array(
        'RefID' => $rid,
        'TransactionID' => $tid,
        'MerchantID' => $this->getSiteid(),
        'CheckSum' => $MD5->hash($this->getAuthkey() . $rid . $tid),
    );

    $con = new KwixoSocket($this->getUrltagline(), 'POST', $data);
    return new XMLElement($con->send());
  }

  /**
   * envoi une requête d'annulation totale ou partiel ou de validation remote control pour la commande correspondant aux refID et transacID
   *
   * @param int $actioncode
   * @param string $refid
   * @param string $transacid
   * @param float $cmplt
   * @param string $answertype
   * @param string $urlcall
   * @param string $mode
   * @return XMLElement réponse du serveur
   */
  public function sendRemoteControl($actioncode, $refid, $transacid, $cmplt='null', $answertype = Kwixo::REMOTE_CTRL_ANSWER_TYPE_XML, $urlcall=null, $mode=Kwixo::REMOTE_CTRL_MODE_ATOMIC) {
    $MD5 = new HashMD5();

    $checksum = $MD5->hash($this->getAuthkey() . $actioncode . $transacid . $cmplt);

    $data = array(
        'Mode' => $mode,
        'ActionCode' => $actioncode,
        'MerchantID' => $this->getSiteId(),
        'RefID' => $refid,
        'TransactionID' => $transacid,
        'Cmplt' => $cmplt,
        'CheckSum' => $checksum,
        'AnswerType' => $answertype,
        'URLCall' => $urlcall,
    );

    $con = new KwixoSocket($this->getUrlremotecontrol(), 'POST', $data);
    return new XMLElement($con->send());
  }

  /*   * ********** Méthodes de traitement générales **************** */

  /**
   * retourne vrai si $string est uns chaine XML valide, faux sinon
   *
   * @param string $string chaine à tester
   * @return bool
   */
  static function isXMLstring($string, $debug=false) {
    if ($debug)
      var_dump("string = $string");
    //on vérifie si des balises sont présentes avec ou sans déclaration xml
    preg_match('#^(<\?xml.+\?>[\r\n ]*)?<([^( |>)]+).*>.*</(.+)>$#s', $string, $output);
    preg_match('#^(<\?xml.+\?>[\r\n ]*)?<([^( |>)]+).*/>$#s', $string, $output2);
    if ($debug) {
      var_dump($output);
      var_dump($output2);
    }

    //retourne vrai si des balises sont présentes et si les balises ont le meme nom
    return (count($output) != 0 && ($output[2] == $output[3])) || count($output2) != 0;
  }

  /**
   * retourne vrai si l'objet $object et de type $type, faux sinon
   *
   * @param string $type nom de la classe attendue
   * @param mixed $object objet à tester
   * @return bool
   */
  static function isType($type, $object) {
    //retourne faux directement si le paramètre n'est pas un objet
    if (!is_object($object))
      return false;

    return (get_class($object) == $type || in_array($type, class_parents($object)));
  }

  /**
   * retour vrai si l'objet en paramètre est un objet XMLElement, faux sinon
   *
   * @param mixed $input
   * @return bool
   */
  static function isXMLElement($input) {
    return self::isType('XMLElement', $input);
  }

  /**
   * retour vrai si l'objet en paramètre est un objet FormField, faux sinon
   *
   * @param mixed $input
   * @return boolean
   */
  static function isFormField($input) {
    return self::isType('FormField', $input);
  }

  /**
   * retour vrai si l'objet en paramètre est un objet Form, faux sinon
   *
   * @param mixed $input
   * @return boolean
   */
  static function isForm($input) {
    return self::isType('Form', $input);
  }

  /**
   * retourne vrai si $input est un objet de classe SimpleXMLElement, faux sinon
   *
   * @param mixed $input objet à tester
   * @return bool
   */
  static function isSimpleXMLElement($input) {
    return self::isType('SimpleXMLElement', $input);
  }

  /**
   * converti une chaine en chaine valide pour une balise XML, exemple : OptionsPaiement devient options-paiment
   * @param string $name
   */
  static function normalizeName($name) {
    $string = strtolower($name[0]);
    $i = 1;
    for ($i; $i < strlen($name); $i++) {
      if (ord($name[$i]) >= ord('A') && ord($name[$i]) <= ord('Z')) {
        $string .= '-';
      }

      $string .= strtolower($name[$i]);
    }

    return $string;
  }

  /**
   * insère une erreur en haut du fichier de log, en le créant s'il n'existe pas déjà
   *
   * @param string $func nom de la fonction reportant le bug
   * @param string $msg description de l'erreur
   */
  static function insertLog($func, $msg) {
    if (file_exists(KW_ROOT_DIR . '/logs/fianetlog.xml') && filesize(KW_ROOT_DIR . '/logs/fianetlog.xml') > 1000000) {
      $prefix = KW_ROOT_DIR . '/logs/fianetlog-';
      $base = date('YmdHis');
      $sufix = '.xml';
      $filename = $prefix . $base . $sufix;

      for ($i = 0; file_exists($filename); $i++)
        $filename = $prefix . $base . "-$i" . $sufix;

      rename(KW_ROOT_DIR . '/logs/fianetlog.xml', $filename);
    }

    //si le fichier log n'existe pas on le créé vide
    if (!file_exists(KW_ROOT_DIR . '/logs/fianetlog.xml')) {
      //création du fichier en écriture
      $handle = fopen(KW_ROOT_DIR . '/logs/fianetlog.xml', 'w');
      //fermeture immédiate du fichier
      fclose($handle);

      //création d'un XMLElement qui contiendra toutes les erreurs
      $log = new XMLElement('<fianetlog></fianetlog>');
      //création d'un XMLElement qui représente la première entrée
      $entry = new XMLElement("<entry></entry>");
      $entry->childTime(date('d-m-Y h:i:s'));
      $entry->childFunc('functions.inc.php - insertLog()');
      $entry->childMessage('Création du fichier de log');
      //ajout de l'entrée dans le log principal
      $log->addChild($entry);
      //sauvegarde du log
      $log->saveInFile(KW_ROOT_DIR . '/logs/fianetlog.xml');
    }

    //création d'une nouvelle entrée
    $entry = new XMLElement("<entry></entry>");
    $entry->childTime(date('d-m-Y h:i:s'));
    $entry->childFunc($func);
    $entry->childMessage($msg);

    //ouverture du log principal
    $log = simplexml_load_file(KW_ROOT_DIR . '/logs/fianetlog.xml');
    $xmllog = new XMLElement($log);
    //ajout de la nouvelle entrée en haut du fichier
    $xmllog->stackChild($entry);
    $xmllog->saveInFile(KW_ROOT_DIR . '/logs/fianetlog.xml');
  }

}