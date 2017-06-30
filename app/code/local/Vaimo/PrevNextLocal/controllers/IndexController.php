<?php

class Vaimo_PrevNextLocal_IndexController extends Mage_Core_Controller_Front_Action
{
    public function testAction()
    {
        echo 'Test area';
        $settings = Mage::helper('prevnextlocal')->getSettings();
        var_dump($settings);
    }

    public function norouteAction($coreRoute = null)
    {
        echo "noRoute Action. Please add your store code to the url.<br>";
        echo "Example: /se/prevnextlocal/index/test<br>";
    }

}