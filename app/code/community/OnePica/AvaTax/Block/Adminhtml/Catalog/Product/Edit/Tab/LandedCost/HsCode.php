<?php
/**
 * OnePica_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 * @copyright  Copyright (c) 2009 One Pica, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Product Avatax tab HsCode
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 */
class OnePica_AvaTax_Block_Adminhtml_Catalog_Product_Edit_Tab_LandedCost_HsCode
    extends OnePica_AvaTax_Block_Adminhtml_Catalog_Product_Edit_Tab_LandedCost_Abstract
{
    /**
     * Initialize block
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);

        $this->setTemplate('onepica/avatax/product/edit/tab/renderer/hscode.phtml');
    }

    /**
     * @return \OnePica_AvaTax_Model_Records_HsCode[]
     */
    public function getHsCodes()
    {
        return $this->_getHsCodeModel()->getCollection()->getItems();
    }

    /**
     * @return false|\Mage_Core_Model_Abstract|\OnePica_AvaTax_Model_Records_HsCode
     */
    protected function _getHsCodeModel()
    {
        return Mage::getModel('avatax_records/hsCode');
    }
}
