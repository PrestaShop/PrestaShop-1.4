<?php

/**
 * Description of OptionsPaiement
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class OptionsPaiement extends XMLElement {

    public function __construct($type = null, $comptantrnp = null, $comptantrnpoffert = null) {
        $this->setName('options-paiement');

        $this->addAttribute('type', $type);
        if (!is_null($comptantrnp))
            $this->addAttribute('comptant-rnp', $comptantrnp);
        if (!is_null($comptantrnpoffert))
            $this->addAttribute('comptant-rnp-offert', $comptantrnpoffert);
    }

}