<?php
/**
 * Classe décrivant les retours d'appel au Remote Control <answer>
 *
 * @version 3.1
 * @author ESPIAU Nicolas
 */
class RemoteResponse extends XMLResult {
    public function getAcks() {
        $acks = array();

        foreach ($this->getChildrenByName('ack') as $ack){
            $acks[] = new AckResponse($ack->getXML());
        }

        return $acks;
    }
}