<?php

/**
 * Description of Wallet
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class Wallet extends XMLElement {
    
    public function __construct($datecom=null, $walletversion=FiaKwixo::WALLET_VERSION) {
        parent::__construct();

        $this->addAttribute('version', $walletversion);
        $this->childDatecom($datecom);
    }
}