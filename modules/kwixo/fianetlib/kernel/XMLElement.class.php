<?php

/**
 * Classe XMLElement complÃ©tant la classe native SimpleXMLElement
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class XMLElement extends Mother {

  static $forbidden_chars = array('&', '<', '>');
  protected $encoding = "UTF-8";
  protected $version = "1.0";
  protected $name = "";
  protected $value = "";
  protected $attributes = array();
  protected $children = array();

  public function __construct($data=null, $debug=false) {
    //encodage
    $charset = ini_get('default_charset');

    if (is_null($data)) {
      $name = FiaKwixo::normalizeName(get_class($this));
      $this->setName($name);
    }

    //si $data est une chaine de caractÃ¨res valide
    if (is_string($data)) {
      //encodage de la chaîne
      if ($charset == '')
        $data = mb_convert_encoding($data, $this->getEncoding());
      else
        $data = mb_convert_encoding($data, $this->getEncoding(), $charset);

      //on supprime les espaces en début de chaine
      $data = preg_replace('#^[ \r\n' . chr(13) . chr(10) . ']*#', '', $data);
      //on encode les &
      $data = preg_replace('#&#', '&amp;', $data);

      //on vérifie si la chaine est une chaine valide, si non, on jette un erreur
      if (!FiaKwixo::isXMLstring($data)) {
        $msg = "La chaine \"$data\" n'est pas valide";
        insertLog(__METHOD__ . ' : ' . __LINE__, $msg);
        throw new Exception($msg);
      }

      //on la convertit en SimpleXMLElement
      $data = new SimpleXMLElement($data);
    }

    //si $data est un SimpleXMLElement object
    if (is_object($data) && get_class($data) == 'SimpleXMLElement') {
      $string = (string) $data;
      //on rÃ©cupÃ¨re le nom
      $this->name = $data->getName();
      //on rÃ©cupÃ¨re la valeur
      $this->value = trim($string);
      //on rÃ©cupÃ¨re les attributs
      foreach ($data->attributes() as $attname => $attvalue) {
        $this->attributes[$attname] = $attvalue;
      }
      //on rattache les enfants
      foreach ($data->children() as $simplexmlelementchild) {
        $child = new XMLElement($simplexmlelementchild);
        $this->addChild($child);
      }
    }
  }

  /**
   * ajoute un attribut Ã  l'objet courant
   * @param string $name nom de l'attribut
   * @param string $value valeur de l'attribut
   */
  public function addAttribute($name, $value) {
    $this->attributes[$name] = $value;
  }

  /**
   * retourne la valeur de l'attribut $name
   *
   * @param string $name nom de l'attribut
   * @return string
   */
  public function getAttribute($name) {
    return (isset($this->attributes[$name]) ? $this->attributes[$name] : null);
  }

  /**
   * retourne un tableau contenant tous les enfants et sous enfants dont le nom est $name
   *
   * @param string $name
   * @return array
   */
  public function getChildrenByName($name) {
    //ouverture du tableau
    $children = array();

    //pour tous les enfants
    foreach ($this->getChildren() as $child) {
      //si le nom correspond on l'ajoute au tableau
      if ($child->getName() == $name)
        array_push($children, $child);

      //on cherche dans les sous enfants
      $children = array_merge($children, $child->getChildrenByName($name));
    }

    return $children;
  }

  /**
   * retourne un tableau contenant tous les enfants et sous enfants dont le nom est $name, oÃ¹ l'attribut $attrbutename existe et vaut $attributevalue si non null
   *
   * @param <type> $name
   * @param <type> $attributename
   * @param <type> $attributevalue
   * @return <type> 
   */
  public function getChildrenByNameAndAttribute($name, $attributename, $attributevalue=null) {
    //on commence par filtrer les enfants par nom
    $children = $this->getChildrenByName($name);

    //pour chaque enfant prÃ©-sÃ©lectionnÃ©
    foreach ($children as $key => $child) {
      //l'attribut recherchÃ© est absent ou si sa valeur ne correspond pas
      if (is_null($child->getAttribute($attributename)) || (!is_null($attributevalue) && $child->getAttribute($attributename) != $attributevalue))
      //on retire l'enfant du tableau
        unset($children[$key]);
    }

    return $children;
  }

  /**
   * ajoute un enfant Ã  la fin de l'objet courant et retourne l'objet XML de l'enfant
   * @param mixed $input l'enfant (XMLElement, chaine ou SimpleXMLElement
   * @param string $value value of the child
   * @param array $attributes attributes of the child
   * @return XMLElement 
   */
  public function addChild($input, $value=null, $attributes=array()) {
    //normalisation de l'enfant, permettra d'ajouter tous les sous-enfants
    $input = $this->createChild($input, $value, $attributes);

    //ajout de l'enfant au tableau
    $this->children[] = $input;

    return $input;
  }

  /**
   * ajoute un enfant Ã  la fin de l'objet courant et retourne l'objet XML de l'enfant
   * @param mixed $input l'enfant (XMLElement, chaine ou SimpleXMLElement
   * @param string $value value of the child
   * @param array $attributes attributes of the child
   * @return XMLElement
   */
  public function stackChild($input, $value=null, $attributes=array()) {
    //normalisation de l'enfant, permettra d'ajouter tous les sous-enfants
    $input = $this->createChild($input, $value, $attributes);

    //ajout de l'enfant en haut du tableau
    array_unshift($this->children, $input);

    return $input;
  }

  /**
   * normalise $input en XMLElement avec sous enfants
   * cas d'appels :
   * createChild(XMLElement) --> ne fera rien
   * createChild(simpleXMLElement)
   * createChild("<element a='1' b='2'>valeur</element>")
   * createChild("element","valeur", array('a'=>1, 'b'=>2))
   * 
   * @param mixed $input objet Ã  normaliser
   * @param string $value valeur de l'objet (si $input est une chaine)
   * @param string $attributes
   * @return XMLElement objet normalisÃ©
   */
  private function createChild($input, $value=null, $attributes=array()) {
    //si l'entrÃ©e est une chaine non xml, on la construit Ã  partir des autres paramÃ¨tres
    if (is_string($input) && !FiaKwixo::isXMLstring($input)) {
      $str = "<$input";
      foreach ($attributes as $name => $val) {
        $str .= " $name='$val'";
      }
      $str .= '>';

      if (!is_null($value))
        $str .= $value;

      $str .= "</$input>";
      $input = new SimpleXMLElement($str);
    }

    //si l'entrÃ©e est une chaine XML ou un objet simpleXMLElement
    if (is_string($input) || FiaKwixo::isSimpleXMLElement($input)) {
      //conversion en XMLElement
      $input = new XMLElement($input);
    }

    //si Ã  ce stade $input n'est pas un XMLElement, il n'est pas pris en compte
    if (!FiaKwixo::isXMLElement($input)) {
      $msg = "Le paramÃ¨tre entrÃ© n'est pas pris en compte par la classe XMLElement";
      insertLog(get_class($this) . " - createChild()", $msg);
      throw new Exception($msg);
    }

    return $input;
  }

  /**
   * retourne vrai si la valeur est vide et s'il n'y a aucun enfant, faux sinon
   *
   * @return bool 
   */
  public function isEmpty() {
    return ($this->getValue() == "" || is_null($this->getValue())) && ($this->countChildren() == 0);
  }

  /**
   * retourne le nombre d'enfants au premier degrÃ© de l'objet courant
   *
   * @return int
   */
  public function countChildren() {
    return count($this->children);
  }

  /**
   * retourne l'objet SimpleXMLElement correspondant Ã  l'objet courant
   * @param boolean $recursive autorise la descente dans les enfants et sous-enfants
   * @return SimpleXMLElement 
   */
  public function toSimpleXMLElement($recursive = false) {
    //on crÃ©Ã© simplement l'objet SimpleXMLElement
    $simplexlmelementobject = new SimpleXMLElement('<' . $this->getName() . '>' . preg_replace('#&#', '&amp;', $this->getValue()) . '</' . $this->getName() . '>');

    //on ajoute les attributs
    foreach ($this->getAttributes() as $name => $value) {
      $simplexlmelementobject->addAttribute($name, $value);
    }

    //si la rÃ©curisivitÃ© est autorisÃ©e on attache les enfants
    if ($recursive)
      $this->attachChildren($simplexlmelementobject);

    return $simplexlmelementobject;
  }

  /**
   * rattache toute la descendance Ã  l'objet SimpleXMLElement en paramÃ¨tre
   *
   * @param SimpleXMLElement $simplexmlelement objet auquel rattacher toute la descendance
   */
  public function attachChildren($simplexmlelement) {
    //pour chaque enfant de l'objet courant
    foreach ($this->getChildren() as $child) {
      //on crÃ©Ã© un objet SimpleXMLElement et on l'ajoute Ã  l'objet en paramÃ¨tre
      $simplexmlelement_child = $simplexmlelement->addChild($child->getName(), $child->getValue());

      //on ajoute les attributs
      foreach ($child->getAttributes() as $name => $value) {
        $simplexmlelement_child->addAttribute($name, $value);
      }

      //on rattache les enfants de l'enfant lu Ã  l'objet SimpleXMLElement qui lui correspond
      $child->attachChildren($simplexmlelement_child);
    }
  }

  /*
   * nettoie le flux de tous les caractères interdits
   */

  public function cleanValues() {
    //nettoyage de la valeur courante
    $value = $this->getValue();
    foreach (self::$forbidden_chars as $char) {
      $value = preg_replace('#' . $char . '#', '&#' . ord($char) . ';', $value);
    }

    $this->setValue($value);

    //nettoyage du reste du flux
    foreach ($this->getChildren() as $child) {
      $child->cleanValues();
    }
  }

  /**
   * retourne l'objet sous forme de chaine XML
   * @return type string la chaine XML
   */
  public function getXML($cdata = false) {
    //nettoyage du flux
    $this->cleanValues();
    //ajout de la déclaration d'encodage dans le flux
    $ret = preg_replace('#<\?xml(.+)?>#', '<?xml version="' . $this->getVersion() . '" encoding="' . $this->getEncoding() . '" ?>', $this->toSimpleXMLElement(true)->asXML());

    //suppression des retours chariot
    $ret = preg_replace('#[\r\n' . chr(10) . chr(13) . ']#', '', $ret);
    //suppression des espaces entre les balises
    $ret = preg_replace('#>( )+<#', '><', $ret);

    //ajout des sections CDATA
    if ($cdata)
      $ret = preg_replace('#>([^<]+)<#', '><![CDATA[$1]]><', $ret);

    $ret = html_entity_decode($ret, ENT_QUOTES, $this->getEncoding());

    return ($ret);
  }

  /**
   * retourne l'objet sous forme de chaine XML
   * @return string la chaine XML
   */
  public function __toString() {
    return $this->getXML();
  }

  /**
   * enregistre la chaine XML dans un fichier
   *
   * @param string $filename chemin du fichie
   * @return string 
   */
  public function saveInFile($filename) {
    return $this->toSimpleXMLElement(true)->asXML($filename);
  }

  /**
   *
   * @param string $name
   * @param array $params
   * @return mixed
   */
  public function __call($name, array $params) {
    //si le prÃ©fixe est "get", c'est une mÃ©thode de lecture
    if (preg_match('#^get(.+)$#', $name, $out)) {
      return $this->__get(strtolower($out[1]));
    }
    //si le prÃ©fixe est "set", c'est une mÃ©thode d'Ã©criture
    if (preg_match('#^set(.+)$#', $name, $out)) {
      return $this->__set(strtolower($out[1]), $params[0]);
    }

    //si le prÃ©fixe est "child", c'est un ajout d'enfant dynamique
    if (preg_match('#^child(.+)$#', $name, $out)) {
      //on stocke le nom de l'Ã©lÃ©ment Ã  ajouter
      $elementname = strtolower($out[1]);

      //on stock le booleen indiquant la possibilitÃ© d'ajouter une balise vide
      $empty_allowed = (isset($params[2]) ? $params[2] : false);

      //si un paramÃ¨tre est passÃ© et que c'est un XMLElement on l'ajoute directement comme fils si le nom correspond Ã  celui de la fonction
      if (isset($params[0]) && FiaKwixo::isXMLElement($params[0])) {
        //si le nom ne correspond pas on jette un erreur
        if ($params[0]->getName() != $elementname)
          throw new Exception("Le nom de la balise ne correspond pas : $elementname attendu, " . $params[0]->getName() . " trouvÃ©.");

        //si l'Ã©lÃ©ment n'est pas vide ou si on autorise les Ã©lÃ©ments vides
        if (!$params[0]->isEmpty() || $empty_allowed)
          return $this->addChild($params[0]);

        //si vide non autorisÃ©, on sort
        return false;
      }

      //crÃ©ation de l'Ã©lÃ©ment fils
      $child = new XMLElement("<$elementname></$elementname>");
      //si des attributs sont passÃ©s en paramÃ¨tres on les ajouts
      if (isset($params[1])) {
        foreach ($params[1] as $att => $value) {
          $child->addAttribute($att, $value);
        }
      }

      //si il n'y a aucun paramÃ¨tre entrÃ© et qu'on autorise les balises vides
      if ((!isset($params[0]) || is_null($params[0]))) {
        if ($empty_allowed)
          return $this->addChild($child);

        //si vide non autorisÃ© on sort sans rien faire
        return false;
      }

      //si le paramÃ¨tre est une chaine
      if (is_string($params[0]) or is_int($params[0])) {
        //si c'est une chaine XML on crÃ©Ã© un sous-enfant et on l'affecte
        if (FiaKwixo::isXMLstring($params[0])) {
          $granchild = $this->createChild($params[0]);
          $child->addChild($granchild);
        } else {
          //si c'est une chaine normale, on l'affecte comme valeur
          $child->setValue($params[0]);
        }
      }

      //on ajoute l'enfant
      if (!$child->isEmpty() || $empty_allowed)
        return $this->addChild($child);

      return false;
    }
  }

}