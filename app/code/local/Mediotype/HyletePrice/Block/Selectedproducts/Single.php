<?php

/**
 * Selected products single display mode block extensions.
 * @category  Class
 * @package   Mediotype_HyletePrice
 * @author    Rick Buczynski <rick@mediotype.com>
 * @copyright 2018 Mediotype
 */

/**
 * Class declaration
 * @category Class
 * @package  Mediotype_HyletePrice
 * @author   Rick Buczynski <rick@mediotype.com>
 */

class Mediotype_HyletePrice_Block_Selectedproducts_Single extends Icommerce_SelectedProducts_Block_Single
{
    /**
     * Prepare global layout.
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!$this->getChild('msrp_price')) {
            $this->append(
                $this->getLayout()
                    ->createBlock('mediotype_hyleteprice/catalog_product_list_msrp', 'msrp_price')
            );
        }

        if (!$this->getChild('multipack_offer')) {
            $this->append(
                $this->getLayout()
                    ->createBlock('mediotype_hyleteprice/catalog_product_list_multipack', 'multipack_offer')
            );
        }
    }
}
