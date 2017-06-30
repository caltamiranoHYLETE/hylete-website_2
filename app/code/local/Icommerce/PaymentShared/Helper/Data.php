<?php
class Icommerce_PaymentShared_Helper_Data extends Mage_Payment_Helper_Data
{
    public function CanSearchForAddress()
    {
        $currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();
        if ($currency_code=='SEK') {
            return true;
        } else {
            return false;
        }
    }

    public function generatePassword()
    {
        $i = 0;
        $password = '';
        $possibleChars = '0123456789bcdfghjkmnpqrstvwxyz';

        while ($i < 6) {
            $char = substr($possibleChars, mt_rand(0, strlen($possibleChars)-1), 1);

            if (!strstr($password, $char)) {
                $password .= $char;
                $i++;
            }
        }

        return $password;
    }
}