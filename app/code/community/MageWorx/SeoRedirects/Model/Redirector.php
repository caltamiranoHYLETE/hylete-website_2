<?php
/**
 * MageWorx
 * MageWorx_SeoRedirects Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoRedirects
 * @copyright  Copyright (c) 2017 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SeoRedirects_Model_Redirector extends Mage_Core_Model_Url_Rewrite
{
    /**
     * @param string $url
     * @param bool $isPermanent
     */
    public function redirect($url, $isPermanent = false)
    {
        parent::_sendRedirectHeaders($url, $isPermanent);
    }
}