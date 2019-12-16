<?php

class Cybersource_Cybersource_Model_SOPWebMobile_Observer
{
    static $registeredFlag = false;

    public function observe(Varien_Event_Observer $observer)
    {
        if (! self::$registeredFlag) {
            spl_autoload_register(array($this, 'autoload'), false);
            self::$registeredFlag = true;
        }
    }

    public function autoload($class)
    {
        if (! stristr($class, 'lcobucci')) {
            return;
        }

        $classFile = '/lib/' . str_replace('\\', '/', $class) . '.php';
        require_once dirname(dirname(dirname(__FILE__))) . $classFile;
    }
}
