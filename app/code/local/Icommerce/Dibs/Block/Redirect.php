<?php
class Icommerce_Dibs_Block_Redirect extends Mage_Core_Block_Template
{
    function getDibs()
    {
        if ($this->getData('dibsmodel') == null) {
            $this->setDibsmodel(Mage::getModel('dibs/dibs'));
        }
        return $this->getData('dibsmodel');
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('icommerce/dibs/redirect.phtml');
    }

    public function getHttpCookie()
    {
        $arr = explode(";",getenv('HTTP_COOKIE'));
        foreach ($arr as $aline_id => $aline) {
            $cookie_val = explode("=", $aline);
            if (isset($cookie_val[0])) {
                $cookie_name = ltrim($cookie_val[0]);
                // __zlcid - Zopim's cookie
                if (in_array($cookie_name, array('XDEBUG_SESSION', 'mof_filters', 'VIEWED_PRODUCT_IDS', '__zlcid', '__zlcmid', '__kla_id', 'vaimo_pn_category', 'vaimo_pn_products'))) {
                    unset($arr[$aline_id]);
                } else {
                    // BAUHAUS-1368 Sociallogin and Facebook related cookie is too long.
                    if (strpos($cookie_name, 'fbsr_') !== false || strpos($cookie_name, '__utm') !== false
                            || strpos($cookie_name, 'fbm_') !== false
                            || strpos($cookie_name, 'mof_') !== false
                            || strpos($cookie_name, 'optimizely') !== false) {
                        unset($arr[$aline_id]);
                    }
                }
            }
        }
        return implode(";",$arr);
    }
}
