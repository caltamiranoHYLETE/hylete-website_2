<?php
/**
 * Created by PhpStorm.
 * User: shasan
 * Date: 07/04/16
 * Time: 9:14 AM
 *
 * This class is simply a pointer to core Helper data, in order to work well wish all other functions that instantiate helpers
 */

class Nextopia_Search_Helper_Data extends Mage_CatalogSearch_Helper_Data
{
    /**
     * @return bool
     */
    public function isEnabled() {
        return (bool)Mage::getStoreConfig('nextopia_ajax_options/settings/searchstatus', Mage::app()->getStore()->getStoreId());
    }

    /**
     * @return bool
     */
    public function isDemo() {
        return (bool)Mage::getStoreConfig('nextopia_ajax_options/settings/searchdemo', Mage::app()->getStore()->getStoreId());
    }

    /**
     * @return bool
     */
    public function isOneColumnLayout() {
        $nxt_template = Mage::getStoreConfig('nextopia_ajax_options/settings/selected_template', Mage::app()->getStore()->getStoreId());
        
        return (bool)($nxt_template === "page/1column.phtml");
    }

    /**
     * Allows for demo url to display properly for search box while in demo mode.
     * 
     * @param null $query
     * @return string
     */
    public function getResultUrlWhileInDemo($query = null)
    {
        $nextopia_front_name = Mage::getConfig()->getNode('frontend/routers/nsearch/args/frontName');
        $url = $this->_getUrl($nextopia_front_name, array(
            '_query' => array(self::QUERY_VAR_NAME => $query),
            '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure()
        ));

        return $url;
    }

    /**
     * @param null $query
     * @return mixed
     *
     *  If the nextopia search magento extension is enabled the following method will change the search form
     * action accordingly.
     *
     *  Inexplicably some servers do not like the use of parent with the scope resolution operator. In those cases
     * please uses the commented out code below instead.
     */
    public function getResultUrl($query = null) {
        if (Mage::helper("nsearch")->isEnabled()) {
            $nextopia_front_name = Mage::getConfig()->getNode('frontend/routers/nsearch/args/frontName');
            $url = $this->_getUrl($nextopia_front_name, array(
                '_query' => array(self::QUERY_VAR_NAME => $query),
                '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure()
            ));
        } else {
            $url = parent::getResultUrl($query);
        }
        return $url;
    }

    /**
     * @return bool
     */
    public function getAjaxVersion() {
        $ajaxVersion =  Mage::getStoreConfig('nextopia_ajax_options/settings/ajaxversion', Mage::app()->getStore()->getStoreId());
        
        $ajaxVersion =  ($ajaxVersion === '1.5.1')? "v$ajaxVersion": $ajaxVersion;
        
        return $ajaxVersion;
    }

    /**
     * 
     */
    public function getLabelSearchResultPage () {
        $title = Mage::getStoreConfig('nextopia_ajax_options/settings/label_search_result_page', Mage::app()->getStore()->getStoreId());

        if (strlen(trim($title))) {
            return $title;
        } else {
            return "Search Results for ";
        }
    }

}