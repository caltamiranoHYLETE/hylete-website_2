<?php
/**
 * Class Mediotype_OffersTab_Block_Adminhtml_Offerstab
 *
 * @author Myles Forrest <myles@mediotype.com>
 */

class Mediotype_OffersTab_Block_Adminhtml_Offerstab extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /** @var Mediotype_OffersTab_Helper_Data $_offersTabHelper */
//    protected $_offersTabHelper;
//
//    protected $_offers; // Will hold array of offers to show user
//
//    protected $_offersTabExpand = false;

    /**
     * Mediotype_OffersTab_Block_Adminhtml_Offerstab constructor.
     */
    public function __construct()
    {
        $this->_blockGroup = 'mediotype_offerstab';
        $this->_controller = 'adminhtml_offerstab';
        $this->_headerText = $this->__('Offers Tab Offers');
        $this->_addButtonLabel = $this->__('Add Offer');

//        $offersTabExpand = $this->getRequest()->getParam('offers-tab-expand');
//
//        if ($offersTabExpand) {
//            $this->_offersTabExpand = true;
//        }
//
//        $this->_offersTabHelper = Mage::helper("mediotype_offerstab");
//
//        // Populate $this->_offers appropriately
//        $this->_offers = $this->_offersTabHelper->getFilteredOffers();

        parent::__construct();
    }

    /**
     *
     */
    public function shouldShowOnPage()
    {
        $url = $this->getRequest()->getRequestUri();
        return $this->_offersTabHelper->shouldShowForUrl($url);
    }
}
