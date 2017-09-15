<?php

/**
 * Class Gene_ApplePay_Block_Method
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */
class Gene_ApplePay_Block_Method extends Mage_Payment_Block_Form
{
    /**
     * Internal constructor. Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('gene/applepay/method.phtml');
    }

    /**
     * Generate and return a token
     *
     * @return mixed
     */
    public function getClientToken()
    {
        return Mage::getModel('gene_braintree/wrapper_braintree')->init()->generateToken();
    }
}
