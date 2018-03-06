<?php

/**
 * Offers tab panel cache container model.
 * @category  Class
 * @package   Mediotype_OffersTab
 * @author    Rick Buczynski <rick@mediotype.com>
 * @copyright 2018 Mediotype
 */

/**
 * Class declaration
 * @category Class_Type_Model
 * @package  Mediotype_OffersTab
 * @author   Rick Buczynski <rick@mediotype.com>
 */

class Mediotype_OffersTab_Model_PageCache_Container_Offers
    extends Enterprise_PageCache_Model_Container_Customer
{
    /**
     * Get cache ID part from cookies.
     * @return string
     */
    protected function _getIdentifier()
    {
        return $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER_GROUP, '') . 
            '_' . 
            $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER_LOGGED_IN, '');
    }

    /**
     * Get cache ID.
     * @return string
     */
    protected function _getCacheId()
    {
        return sprintf(
            'OFFERSTAB_CACHE_ID_%d_%s',
            (int) $this->_placeholder->getAttribute('customer_group'),
            md5($this->_getIdentifier())
        );
    }

    /**
     * Render block content.
     * @return string
     */
    protected function _renderBlock()
    {
        $block = $this->_getPlaceHolderBlock();

        Mage::dispatchEvent('render_block', array('block' => $block, 'placeholder' => $this->_placeholder));

        return $block->toHtml();
    }
}
