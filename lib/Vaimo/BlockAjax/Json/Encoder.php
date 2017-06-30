<?php

class Vaimo_BlockAjax_Json_Encoder extends Zend_Json_Encoder
{
    public static function encode($value, $cycleCheck = false, $options = array())
    {
        $encoder = new self(($cycleCheck) ? true : false, $options);
        return $encoder->_encodeValue($value);
    }

    protected function _encodeArray(&$array)
    {
        if (!empty($array) && (implode(',', array_keys($array)) === implode(',', range(0, count($array) - 1)))) {
            $array = array_values($array);
        }

        return parent::_encodeArray($array);
    }
}