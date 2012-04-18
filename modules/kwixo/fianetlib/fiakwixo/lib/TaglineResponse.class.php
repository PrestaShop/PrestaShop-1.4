<?php

/**
 * Description of TaglineResponse
 *
 * @version 3.1
 * @author ESPIAU Nicolas
 */
class TaglineResponse extends XMLResult {
    /**
     * retourne vrai si le script détecte une erreur lors de l'appel, faux sinon
     *
     * @return bool
     */
    public function hasError(){
        return array_key_exists('liberr', $this->getAttributes());
    }
}