<?php

class Bestworlds_KlaviyoExtend_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function encryptMe($data)
    {
        return base64_encode($data);
    }
    public function decryptMe($data)
    {
        return base64_decode($data);
    }
}