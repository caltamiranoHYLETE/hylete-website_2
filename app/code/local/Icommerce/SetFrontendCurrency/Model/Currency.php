<?php

class Icommerce_SetFrontendCurrency_Model_Currency extends Mage_Directory_Model_Currency
{

    protected static $_replace_currency;
    protected $precision_level = false;

    protected function _construct()
    {
        parent::_construct();

        $precision_is_active = Icommerce_Default::getStoreConfig('setfrontendcurrency/settings/precision_active');
        if ($precision_is_active) {
            $this->precision_level = (int )Icommerce_Default::getStoreConfig('setfrontendcurrency/settings/precision');
        }
    }

    public function formatTxt($price, $options = array())
    {
        $s = parent::formatTxt($price, $options);

        if (self::$_replace_currency === null) {
            $replace = Icommerce_Default::getStoreConfig("replace_currency");
            if ($replace) {
                $parts = explode(",", $replace);
                if (count($parts) != 2) {
                    Mage::throwException("Icommerce: Wrong currency conversion syntax in Icommerce_Default");
                }
                self::$_replace_currency = $parts;
            } else {
                self::$_replace_currency = false;
            }
        }

        // Do replacement:
        if (self::$_replace_currency !== false) {
            $search = self::$_replace_currency[0];
            $replace = self::$_replace_currency[1];
            $s = str_replace($search, $replace, $s);
        }

        return $s;
    }

    public function format($price, $options = array(), $includeContainer = true, $addBrackets = false)
    {
        if ($this->precision_level === false) {
            return parent::format($price, $options, $includeContainer, $addBrackets);
        }
        return $this->formatPrecision($price, $this->precision_level, $options, $includeContainer, $addBrackets);
    }
}
