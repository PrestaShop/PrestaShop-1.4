<?php
/**
 * retourne vrai si $string est uns chaine XML valide, faux sinon
 *
 * @param string $string chaine à tester
 * @return bool 
 */
function isXMLstring($string, $debug=false) {
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
function isType($type, $object) {
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
function isXMLElement($input) {
    return isType('XMLElement', $input);
}

/**
 * retour vrai si l'objet en paramètre est un objet FormField, faux sinon
 * 
 * @param mixed $input
 * @return boolean 
 */
function isFormField($input) {
    return isType('FormField', $input);
}

/**
 * retour vrai si l'objet en paramètre est un objet Form, faux sinon
 * 
 * @param mixed $input
 * @return boolean 
 */
function isForm($input) {
    return isType('Form', $input);
}

/**
 * retourne vrai si $input est un objet de classe SimpleXMLElement, faux sinon
 *
 * @param mixed $input objet à tester
 * @return bool 
 */
function isSimpleXMLElement($input) {
    return isType('SimpleXMLElement', $input);
}

/**
 * converti une chaine en chaine valide pour une balise XML, exemple : OptionsPaiement devient options-paiment
 * @param string $name 
 */
function normalizeName($name) {
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
function insertLog($func, $msg) {
    //si le fichier log n'existe pas on le créé vide
    if (!file_exists(KW_ROOT_DIR . '/logs/errorlog.xml')) {
        //création du fichier en écriture
        $handle = fopen(KW_ROOT_DIR . '/logs/errorlog.xml', 'w');
        //fermeture immédiate du fichier
        fclose($handle);

        //création d'un XMLElement qui contiendra toutes les erreurs
        $log = new XMLElement('<errorlog></errorlog>');
        //création d'un XMLElement qui représente la première entrée
        $error = new XMLElement("<error></error>");
        $error->childTime(date('d-m-Y h:i:s'));
        $error->childFunc('functions.inc.php - insertLog()');
        $error->childMessage('Création du fichier de log');
        //ajout de l'entrée dans le log principal
        $log->addChild($error);
        //sauvegarde du log
        $log->saveInFile(KW_ROOT_DIR . '/logs/errorlog.xml');
    }

    //création d'une nouvelle entrée
    $error = new XMLElement("<error></error>");
    $error->childTime(date('d-m-Y h:i:s'));
    $error->childFunc($func);
    $error->childMessage($msg);

    //ouverture du log principal
    $log = simplexml_load_file(KW_ROOT_DIR . '/logs/errorlog.xml');
    $xmllog = new XMLElement($log);
    //ajout de la nouvelle entrée en haut du fichier
    $xmllog->stackChild($error);
    $xmllog->saveInFile(KW_ROOT_DIR . '/logs/errorlog.xml');
}