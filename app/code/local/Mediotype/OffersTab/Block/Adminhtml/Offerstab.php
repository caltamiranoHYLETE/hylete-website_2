<?php
/**
 * Class Mediotype_OffersTab_Block_Adminhtml_Offerstab
 *
 * @author Myles Forrest <myles@mediotype.com>
 */

class Mediotype_OffersTab_Block_Adminhtml_Offerstab extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Mediotype_OffersTab_Block_Adminhtml_Offerstab constructor.
     */
    public function __construct()
    {
        $this->_blockGroup = 'mediotype_offerstab';
        $this->_controller = 'adminhtml_offerstab';
        $this->_headerText = $this->__('Offers Tab Offers');
        $this->_addButtonLabel = $this->__('Add Offer');

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
