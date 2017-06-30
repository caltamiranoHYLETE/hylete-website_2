<?php

class Icommerce_Dibs_Helper_Data extends Mage_Payment_Helper_Data
{
    const XML_PATH_PAYMENT_METHODS = 'dibs';

    const PAYMENT_METHOD_SE_SWEDBANK = 'swedbank';
    const PAYMENT_METHOD_SE_NORDEA = 'nordea_se';
    const PAYMENT_METHOD_SE_HANDELSBANKEN = 'handelsbanken';
    const PAYMENT_METHOD_SE_SEB = 'seb';

    const PAYMENT_METHOD_FI_NORDEA = 'nordea_verkkomaksu';
    const PAYMENT_METHOD_FI_OSUUSPANKKI = 'osuuspankki_verkkomaksu';
    const PAYMENT_METHOD_FI_SAMPO = 'sampo_pankki_verkkomaksu';
    const PAYMENT_METHOD_FI_AKTIA = 'aktia_verkkomaksu';

    const PAYMENT_METHOD_AMEX = 'american_express';
    const PAYMENT_METHOD_VISA = 'visa';
    const PAYMENT_METHOD_MASTERCARD = 'mastercard';

    protected $_path;

    /**
     * Get html for logos you have selected in admin
     * @since 2013-05-24
     * @param int $type | 1 = only html for trusted, 2 = only html for cards, 3 = both
     * @return string | The html, without styling
     */
    public function getLogosHtml($type=3,$space="") {
        $model=Mage::getModel("dibs/dibs");
        $show_card_list=$model->showCardsList();
        $show_trusted_list=$model->showTrustedList();
        if ($show_card_list!=true and $show_trusted_list!=true) { return ""; }
        $logos_all = Mage::getModel("dibs/config_showlogos")->toOptionArray();
        $logos_selected = explode(',', $model->getConfigData('showlogos'));
        if (count($logos_selected)==0) { return ""; }
        $logos=array();
        $cards=array();
        $trusts=array();
        foreach ($logos_all as $logo) {
            $logos[$logo['value']]=$logo;

            $trusted=false;
            if (isset($logo['type'])==true) {
                if ($logo['type']==1) { $trusted=true; }
            }
            if ($trusted==true) { $trusts[$logo['value']]=$logo; }
            if ($trusted==false) { $cards[$logo['value']]=$logo; }

        }
        unset($logos_all);
        $html_trust=array();
        $html_card=array();
        $model = Mage::getModel('core/design_package');
        foreach ($logos_selected as $logo) {
            $file=$model->getSkinUrl('images/dibs/'.$logos[$logo]['image'].'.gif');
            $alt=$logos[$logo]['label'];
            $row="<img src=\"".$file."\" alt=\"".$alt."\" />";
            if (isset($trusts[$logo])==true) {
                $html_trust[]=$row;
            } else {
                $html_card[]=$row;
            }
        }
        if ((count($html_card)==0) and (count($html_trust)==0)) { return ""; }
        $answer=array();

        if ($show_trusted_list==true and ($type & 1)) {
            $answer[]=implode($space,$html_trust);
        }

        if ($show_card_list==true and ($type & 2)) {
            $answer[]=implode($space,$html_card);
        }

        $html=implode($space,$answer);
        return $html;
    }

    /**
     * @desc Path to system xml config
     * @return string
     */
    protected function _getPath()
    {
        if ($this->_path === null) {
            $this->_path = 'payment/dibs/';
        }
        return $this->_path;
    }

    /**
     * @desc Show logos in checkout or not?
     * @return bool
     */
    public function displayLogos()
    {
        return (bool) Mage::getStoreConfig($this->_getPath() . 'display_logos');
    }
}
