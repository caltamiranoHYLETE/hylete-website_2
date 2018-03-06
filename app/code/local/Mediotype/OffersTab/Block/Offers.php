<?php
/**
 * Class Mediotype_OffersTab_Block_Offers
 *
 * @author Myles Forrest <myles@mediotype.com>
 */

class Mediotype_OffersTab_Block_Offers extends Mage_Core_Block_Template
{
    /** @var Mediotype_OffersTab_Helper_Data $_offersTabHelper */
    protected $_offersTabHelper;

    /** @var array */
    protected $_offers;

    /**
     * Mediotype_OffersTab_Block_Adminhtml_Offerstab constructor.
     */
    public function __construct()
    {
        $this->_offersTabHelper = Mage::helper("mediotype_offerstab");
        $this->_offers = $this->_offersTabHelper->getFilteredOffers();

        parent::__construct();
    }

    /**
     * Determine what pages to display the offerstab block on based on a comma delimited system configuration
     *
     * @return bool
     */
    public function shouldShowOnPage()
    {
        $url = $this->getRequest()->getRequestUri();
        return $this->_offersTabHelper->shouldShowForUrl($url);
    }

    /**
     * Get cache key data.
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array_merge(
            parent::getCacheKeyInfo(),
            array(
                'customer_group' => $this->getCustomerGroupId(), // also supplied by FPC cookie model
            )
        );
    }

    /**
     * Generate a container element ID.
     * @return integer
     */
    public function getContainerHtmlId()
    {
        return sprintf('offers_container_group_%d', $this->getCustomerGroupId());
    }

    /**
     * Get the current customer group ID.
     * @return int
     */
    public function getCustomerGroupId()
    {
        return Mage::getSingleton('customer/session')->getCustomerGroupId() ?: Mage_Customer_Model_Group::CUST_GROUP_ALL;
    }

    /**
     * Has URL param to expand offers tab on page load
     *
     * @return bool
     */
    public function hasExpandOffer()
    {
        $offersAutoOpenKey = Mage::getStoreConfig('mediotype_offerstab/general/auto_open_offers_key');
        $offersAutoOpenValue = Mage::getStoreConfig('mediotype_offerstab/general/auto_open_offers_value');
        $offersTabExpand = $this->getRequest()->getParam($offersAutoOpenKey);

        return ($offersTabExpand && $offersTabExpand == $offersAutoOpenValue ? true : false);
    }
}
