<?php

/**
 * Classe décrivant les éléments <ack>, enfant des réponses au script Remote Control <answer>
 *
 * @version 3.1
 * @author ESPIAU Nicolas
 */
class AckResponse extends XMLResult {
    /**
     * retourne vrai si le script a rencontré une erreur, faux sinon
     *
     * @return bool
     */
    public function hasError() {
        return ($this->returnCoderr() < 0);
    }

    /**
     * retourne le code erreur
     *
     * @return string
     */
    public function getErrorCode() {
        return $this->returnCoderr();
    }

    /**
     * retourne le libellé de l'erreur
     *
     * @return string
     */
    public function getError() {
        return $this->returnLiberr();
    }

    public function generateChecksum() {
        $md5 = new HashMD5();

        $kwixo = new FiaKwixo();

        $checksum = $md5->hash($kwixo->getAuthkey() . (string)$this->returnTransactionid() . $this->getValue());

        return $checksum;
    }

    /**
     * retourne vrai si le checksum récupéré est valide, faux sinon
     *
     * @return bool
     */
    public function checksumIsValid() {
        $checksum = $this->returnChecksum();

        $waitedchecksum = $this->generateChecksum();

        return $checksum == $waitedchecksum;
    }
}