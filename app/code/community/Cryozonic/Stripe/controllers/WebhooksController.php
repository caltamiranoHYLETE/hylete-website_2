<?php

class Cryozonic_Stripe_WebhooksController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        Mage::helper('cryozonic_stripe/webhooks')->dispatchEvent();
    }
}
